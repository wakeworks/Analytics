<?php

namespace WakeWorks\Analytics\Controllers;

use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Security\PermissionProvider;
use WakeWorks\Analytics\Extensions\SubsitesExtension;
use WakeWorks\Analytics\Forms\AnalyticsHitsField;
use WakeWorks\Analytics\Forms\AnalyticsBrowserField;
use WakeWorks\Analytics\Forms\AnalyticsBrowserVersionField;
use WakeWorks\Analytics\Forms\AnalyticsDeviceField;
use WakeWorks\Analytics\Forms\AnalyticsOSField;
use WakeWorks\Analytics\Forms\AnalyticsPagesField;
use WakeWorks\Analytics\Models\AnalyticsLog;

class AnalyticsController extends LeftAndMain implements PermissionProvider {
    private static $url_segment = 'analytics';
    private static $menu_title  = 'Analytics';
    private static $menu_priority = 0;
    private static $menu_icon_class = 'font-icon-chart-pie';
    private static $required_permission_codes = 'CMS_ACCESS_Analytics';

    public function init() {
        parent::init();
    }

    public function AnalyticsForm() {
        $fields = new FieldList(FieldGroup::create(
            AnalyticsHitsField::create(),
            AnalyticsPagesField::create(),
            AnalyticsDeviceField::create(),
            AnalyticsBrowserField::create(),
            AnalyticsBrowserVersionField::create(),
            AnalyticsOSField::create()
        )->addExtraClass('analytics-form-field-group'));

        return new Form($this, 'AnalyticsForm', $fields, null, null);
    }

    public function providePermissions() {
        return [
            self::$required_permission_codes => [
                'name' => _t(
                    'SilverStripe\\CMS\\Controllers\\CMSMain.ACCESS',
                    "Access to '{title}' section",
                    ['title' => static::menu_title()]
                ),
                'category' => _t('SilverStripe\\Security\\Permission.CMS_ACCESS_CATEGORY', 'CMS Access'),
                'help' => _t(
                    __CLASS__.'.ACCESS_HELP',
                    'Allow viewing of the analytics section.'
                )
            ]
        ];
    }

    public function subsiteCMSShowInMenu() {
        return AnalyticsLog::has_extension(SubsitesExtension::class);
    }
}