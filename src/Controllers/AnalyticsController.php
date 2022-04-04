<?php

namespace WakeWorks\Analytics\Controllers;

use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\View\Requirements;
use WakeWorks\Analytics\Forms\AnalyticsHitsField;
use WakeWorks\Analytics\Forms\AnalyticsBrowserField;
use WakeWorks\Analytics\Forms\AnalyticsBrowserVersionField;
use WakeWorks\Analytics\Forms\AnalyticsDeviceField;
use WakeWorks\Analytics\Forms\AnalyticsOSField;
use WakeWorks\Analytics\Forms\AnalyticsUrlsField;

class AnalyticsController extends LeftAndMain {
    private static $url_segment = 'analytics';
    private static $menu_title  = 'Analytics';
    private static $menu_priority = 0;
    private static $menu_icon_class = 'font-icon-chart-pie';

    public function init() {
        parent::init();
    }

    public function AnalyticsForm() {
        $fields = new FieldList(FieldGroup::create(
            AnalyticsHitsField::create(),
            AnalyticsBrowserField::create(),
            AnalyticsBrowserVersionField::create(),
            AnalyticsDeviceField::create(),
            AnalyticsOSField::create(),
            AnalyticsUrlsField::create()
        )->addExtraClass('analytics-form-field-group'));

        return new Form($this, 'AnalyticsForm', $fields, null, null);
    }
}