<?php

namespace WakeWorks\Analytics\Controllers;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Injector\Injector;
use WakeWorks\Analytics\Analytics;
use WakeWorks\Analytics\Middlewares\AnalyticsProcessorMiddleware;
use WakeWorks\Analytics\Models\AnalyticsVerification;

class AnalyticsVerificationController extends Controller {

    private static $allowed_actions = [
        'imageverification'
    ];

    private static $url_handlers = [
        'imageverification/$UUID' => 'imageverification'
    ];

    protected function init() {
        parent::init();

        $analytics = Injector::inst()->get(Analytics::class);
        $analytics->disable();
    }

    public function index() {
        return $this->httpError(404);
    }

    public function imageverification(HTTPRequest $request) {
        // We set the session in the request before, so if it's not there, it's probably a bot.
        if(!$request->getSession()->get(AnalyticsProcessorMiddleware::class . 'Visited')) {
            return new HTTPResponse('', 204);
        }

        $uuid = $request->param('UUID');
        $verification = AnalyticsVerification::get_by_uuid($uuid);
        if(!$verification) {
            return new HTTPResponse('Bad request', 400);
        }

        $model = $verification->dataToAnalyticsLog();
        $model->write();
        $verification->delete();
        AnalyticsVerification::garbage_collection();
        return new HTTPResponse('', 204);
    }

}