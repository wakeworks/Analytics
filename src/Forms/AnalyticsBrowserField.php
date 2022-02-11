<?php

namespace Zazama\Analytics\Forms;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Client\Browser;
use SilverStripe\ORM\DataObject;
use Zazama\Analytics\Models\AnalyticsLog;

class AnalyticsBrowserField extends AnalyticsField
{
    protected $schemaComponent = 'AnalyticsBrowserField';

    public function __construct(string $title = null) {
        if(!$title) {
            $title = _t(__CLASS__ . '.TITLE', 'Browsers');
        }
        $this->addExtraClass('analytics-browser-field');
        parent::__construct($title, []);
    }

    public function getSchemaStateDefaults()
    {
        $state = parent::getSchemaStateDefaults();
        $state['chartData'] = $this->getBrowsers();
        return $state;
    }

    private function getBrowsers() {
        $table = DataObject::getSchema()->tableName(AnalyticsLog::class);
        $query = parent::createSQLSelect(true);
        $query->setSelect("\"{$table}\".\"BrowserName\"");
        $query->addSelect("COUNT(*) as Count");
        $query->setGroupBy("\"{$table}\".\"BrowserName\"");
        $query->setOrderBy("Count", "DESC");
        $query->setLimit(20);
        $result = $query->execute();

        $map = [];
        foreach($result as $record) {
            $key = reset($record);
            $val = next($record);
            $browserName = array_key_exists($key, Browser::getAvailableBrowsers()) ? Browser::getAvailableBrowsers()[$key] : DeviceDetector::UNKNOWN;
            $map[$browserName] = $val;
        }

        return $map;
    }
}
