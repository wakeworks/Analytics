<?php

namespace Zazama\Analytics\Middlewares;

use AnalyticsGarbageCollectionTask;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Middleware\HTTPMiddleware;
use DeviceDetector\DeviceDetector;
use SilverStripe\Admin\AdminRootController;
use Zazama\Analytics\Models\AnalyticsLog;
use Defuse\Crypto\Crypto;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injector;
use Zazama\Analytics\Analytics;
use Zazama\Analytics\Models\AnalyticsURL;

class AnalyticsProcessorMiddleware implements HTTPMiddleware {
    use Configurable;

    private static $enabled = true;
    private static $secret_key = null;
    private static $image_verification = false;
    private static $preserve_for_days = 365;
    private static $gc_divisor = 0;

    public function process(HTTPRequest $request, callable $delegate)
    {
        if(!$this->config()->get('enabled')) {
            return $delegate($request);
        }

        $analytics = Injector::inst()->get(Analytics::class);
        $analytics->setAnalyticsLog(new AnalyticsLog());

        if(is_int($this->config()->get('gc_divisor')) && $this->config()->get('gc_divisor') > 0) {
            $randomNumber = mt_rand(0, $this->config()->get('gc_divisor'));
            if($randomNumber === 0) {
                (new AnalyticsGarbageCollectionTask())->run($request, true);
            }
        }

        $insertImageTracking = false;
        if($this->config()->get('image_verification') && $this->config()->get('secret_key')) {
            if($request->getVar('analyticsimage')) {
                return $this->processAnalyticsImage($request);
            } else {
                $insertImageTracking = true;
            }
        }

        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $deviceDetector = new DeviceDetector($userAgent);
        $deviceDetector->parse();

        /**
         * If the UserAgent is detected as a bot, we don't write it into the DB for efficiency, because the storage amount can get quiet high with bots enabled.
         * If the Browser can't be detected, the client is with high probability also a bot.
         */
        if($deviceDetector->isBot() || $deviceDetector->getClient('name') === DeviceDetector::UNKNOWN || !$deviceDetector->getClient('name')) {
            return $delegate($request);
        }

        if ($request->getVar('CMSPreview') === '1' || $request->getVar('stage') !== null) {
            $analytics->disable();
        }

        $currentModel = $analytics->getAnalyticsLog();
        $currentModel->Date = date('Y-m-d');

        if(!$request->getSession()->get(__CLASS__ . 'Visited')) {
            $currentModel->IsFirstVisit = true;
            $request->getSession()->set(__CLASS__ . 'Visited', true);
        }

        $currentModel->OSName = $deviceDetector->getOs('short_name') ?? null;
        $currentModel->OSVersion = $deviceDetector->getOs('version') ?? null;
        $currentModel->BrowserName = $deviceDetector->getClient('short_name') ?? null;
        $currentModel->BrowserVersion = $deviceDetector->getClient('version') ?? null;
        $currentModel->DeviceType = $deviceDetector->getDevice() ?? -1;

        $response = $delegate($request);

        $url = $request->getURL();

        if($this->isAdminUrl($url) ||
           $this->isBlockedUrl($url) ||
           !$this->isAllowedStatusCode($response->getStatusCode()) ||
           $analytics->isDisabled()) {
            return $response;
        }

        // URLs can change after delegation (e.g. null to home), set after
        if(!$currentModel->URLID) $currentModel->URLID = AnalyticsURL::find_or_create($url)->ID;

        $currentModel->write();

        if($insertImageTracking) {
            $encryptedId = Crypto::encryptWithPassword(strval($currentModel->ID), $this->config()->get('secret_key'));
            $img = '<img src="/?analyticsimage=' . urlencode($encryptedId) . '" style="position: absolute; visibility: hidden;" alt="" />' . "\n";

            // This is taken from Requirements_Backend
            $newBody = preg_replace(
                '/(<\/body[^>]*>)/i',
                addcslashes($img, '\\$') . '\\1',
                $response->getBody()
            );
            $response->setBody($newBody);
        }

        return $response;
    }

    public function processAnalyticsImage(HTTPRequest $request) {
        // We set the session in the request before, so if it's not there, it's probably a bot.
        if(!$request->getSession()->get(__CLASS__ . 'Visited')) {
            return new HTTPResponse('', 204);
        }

        $encryptedId = $request->getVar('analyticsimage');

        try {
            $id = Crypto::decryptWithPassword($encryptedId, $this->config()->get('secret_key'));
        } catch(\Exception $e) {
            return new HTTPResponse('Bad request', 400);
        }

        $model = AnalyticsLog::get_by_id(AnalyticsLog::class, $id);
        if(!$model || $model->IsImageVerified) {
            return new HTTPResponse('Bad request', 400);
        }
        $model->IsImageVerified = true;
        $model->write();
        return new HTTPResponse('', 204);
    }

    public function isAdminUrl($url)
    {
        if (class_exists(AdminRootController::class)) {
            $adminUrl = AdminRootController::config()->get('url_base');
        } else {
            $adminUrl = 'admin';
        }

        return strpos($url, $adminUrl) === 0;
    }

    public function isBlockedUrl($url) {
        return strpos($url, 'Security') === 0 && strpos($url, 'Security/login') !== 0 && strpos($url, 'UserDefinedFormController/ping');
    }

    public function isAllowedStatusCode($statusCode) {
        return $statusCode >= 200 && $statusCode <= 204;
    }
}