<?php

namespace WakeWorks\Analytics\Models;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\DataObject;

class AnalyticsLog extends DataObject {

    private static $table_name = 'AnalyticsLog';

    private static $db = [
        'Date' => 'Date',
        'OSName' => 'Varchar(255)',
        'OSVersion' => 'Varchar(255)',
        'BrowserName' => 'Varchar(255)',
        'BrowserVersion' => 'Varchar(255)',
        'DeviceType' => 'Int',
        'IsFirstVisit' => 'Boolean',
        'IsImageVerified' => 'Boolean'
    ];

    private static $has_one = [
        'Page' => SiteTree::class,
        'URL' => AnalyticsURL::class
    ];

    private static $indexes = [
        'Date' => true
    ];
}