<?php

namespace Zazama\Analytics\Extensions;

use SilverStripe\Core\Config\Config;
use SilverStripe\Control\Director;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\DevelopmentAdmin;
use SilverStripe\Security\Security;
use Zazama\Analytics\Analytics;
use Zazama\Analytics\Middlewares\AnalyticsProcessorMiddleware;

class ControllerExtension extends Extension {
    public function onAfterInit() {
        if(!Config::inst()->get(AnalyticsProcessorMiddleware::class, 'enabled')) {
            return;
        }

        $analytics = Injector::inst()->get(Analytics::class);
        $currentModel = $analytics->getAnalyticsLog();

        // disable dev urls
        if(is_a($this->owner, DevelopmentAdmin::class)) {
            $analytics->disable();
        }

        //disable /Security urls
        if(is_a($this->owner, Security::class)) {
            $analytics->disable();
        }

        if(($currentPage = Director::get_current_page()) && $currentModel) {
            $currentModel->PageID = $currentPage->ID;
        }
    }
}