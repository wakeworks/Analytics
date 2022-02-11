<?php

namespace Zazama\Analytics\Models;

use SilverStripe\ORM\DataObject;

class AnalyticsURL extends DataObject {

    private static $table_name = 'AnalyticsURL';

    private static $db = [
        'URL' => 'Varchar(4096)'
    ];

    public static function find_or_create($url) {
        $existing = AnalyticsURL::get()->filter(['URL' => $url])->first();

        if(!$existing) {
            $newUrl = new AnalyticsURL();
            $newUrl->URL = $url;
            $newUrl->write();
            return $newUrl;
        } else {
            return $existing;
        }
    }
}