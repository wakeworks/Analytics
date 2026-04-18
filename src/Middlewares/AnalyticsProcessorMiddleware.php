<?php

namespace WakeWorks\Analytics\Middlewares;

use AnalyticsGarbageCollectionTask;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Middleware\HTTPMiddleware;
use DeviceDetector\DeviceDetector;
use SilverStripe\Admin\AdminRootController;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Input\ArrayInput;
use WakeWorks\Analytics\Models\AnalyticsLog;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injector;
use WakeWorks\Analytics\Analytics;
use WakeWorks\Analytics\Models\AnalyticsURL;
use Psr\SimpleCache\CacheInterface;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use WakeWorks\Analytics\Cache\DeviceDetectorCache;
use WakeWorks\Analytics\Models\AnalyticsVerification;

class AnalyticsProcessorMiddleware implements HTTPMiddleware {
    use Configurable;

    private static $enabled = true;
    private static $image_verification = false;
    private static $preserve_for_days = 365;
    private static $gc_divisor = 0;

    public function process(HTTPRequest $request, callable $delegate)
    {
        if(!$this->config()->get('enabled')) {
            return $delegate($request);
        }

        $analytics = Injector::inst()->get(Analytics::class);
        $analytics->enable();
        $analytics->setAnalyticsLog(new AnalyticsLog());

        if(is_int($this->config()->get('gc_divisor')) && $this->config()->get('gc_divisor') > 0) {
            $randomNumber = mt_rand(1, $this->config()->get('gc_divisor'));
            if($randomNumber === 1) {
                (new AnalyticsGarbageCollectionTask())->run(
                    new ArrayInput([]),
                    new PolyOutput(PolyOutput::FORMAT_HTML, PolyOutput::VERBOSITY_SILENT)
                );
            }
        }

        $insertImageTracking = !!$this->config()->get('image_verification');

        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $deviceDetector = new DeviceDetector($userAgent);
        $deviceDetector->setCache(new DeviceDetectorCache(Injector::inst()->get(CacheInterface::class . '.analytics')));
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

        $currentModel->OSName = $deviceDetector->getOs('short_name') ?? null;
        $currentModel->OSVersion = $deviceDetector->getOs('version') ?? null;
        $currentModel->BrowserName = $deviceDetector->getClient('short_name') ?? null;
        $currentModel->BrowserVersion = $deviceDetector->getClient('version') ?? null;
        $currentModel->DeviceType = $deviceDetector->getDevice() ?? -1;

        $currentModel->processBeforeDelegate($request);

        $response = $delegate($request);

        $url = $request->getURL();

        if($this->isAdminUrl($url) ||
           $this->isBlockedUrl($url) ||
           !$this->isAllowedStatusCode($response->getStatusCode()) ||
           $this->userHasCMSAccess() ||
           $analytics->isDisabled()) {
            return $response;
        }

        // URLs can change after delegation (e.g. null to home), set after
        if(!$currentModel->URLID) $currentModel->URLID = AnalyticsURL::find_or_create($url)->ID;

        $currentModel->processAfterDelegate($request, $response);

        if(!$request->getSession()->get(self::class . 'Visited')) {
            $currentModel->IsFirstVisit = true;
            $request->getSession()->set(self::class . 'Visited', true);
        }

        if($insertImageTracking) {
            $contentTypeHeader = strtolower((string) $response->getHeader('Content-Type'));
            if(str_contains($contentTypeHeader, 'text/html')) {
                $uuid = AnalyticsVerification::generate_and_write($currentModel)->UUID;
                $img = '<img src="/_analytics/imageverification/' . urlencode((string) $uuid) . '" style="position: absolute; visibility: hidden;" alt="" />' . "\n";

                // This is taken from Requirements_Backend
                $newBody = preg_replace(
                    '/(<\/body[^>]*>)/i',
                    addcslashes($img, '\\$') . '\\1',
                    (string) $response->getBody()
                );
                $response->setBody($newBody);
            }
        } else {
            $currentModel->write();
        }

        return $response;
    }

    public function isAdminUrl($url)
    {
        if (class_exists(AdminRootController::class)) {
            $adminUrl = AdminRootController::config()->get('url_base');
        } else {
            $adminUrl = 'admin';
        }

        return str_starts_with((string) $url, (string) $adminUrl);
    }

    public function isBlockedUrl($url) {
        return str_starts_with((string) $url, 'Security') && !str_starts_with((string) $url, 'Security/login') && strpos((string) $url, 'UserDefinedFormController/ping');
    }

    public function isAllowedStatusCode($statusCode) {
        return $statusCode >= 200 && $statusCode <= 204;
    }

    public function userHasCMSAccess() {
        $currentUser = Security::getCurrentUser();
        if(!$currentUser) return false;

        return Permission::checkMember($currentUser, 'CMS_ACCESS');
    }
}