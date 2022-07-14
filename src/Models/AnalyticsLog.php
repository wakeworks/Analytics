<?php

namespace WakeWorks\Analytics\Models;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
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
        'IsFirstVisit' => 'Boolean'
    ];

    private static $has_one = [
        'Page' => SiteTree::class,
        'URL' => AnalyticsURL::class
    ];

    private static $indexes = [
        'Date' => true
    ];

    public function processBeforeDelegate(HTTPRequest $request) {
        $this->extend('updateProcessBeforeDelegate', $request);
    }

    public function processAfterDelegate(HTTPRequest $request, HTTPResponse $response) {
        $this->extend('updateProcessAfterDelegate', $request, $response);
    }
}