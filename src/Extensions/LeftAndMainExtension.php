<?php

namespace WakeWorks\Analytics\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\View\Requirements;

class LeftAndMainExtension extends Extension {

    public function init()
    {
        Requirements::add_i18n_javascript('wakeworks/analytics:client/lang', false);
        Requirements::javascript('wakeworks/analytics:client/dist/js/bundle.js');
        Requirements::css('wakeworks/analytics:client/dist/styles/bundle.css');
    }

}