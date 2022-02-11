<?php

namespace Zazama\Analytics\Forms;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Client\Browser;
use SilverStripe\ORM\DataObject;
use Zazama\Analytics\Models\AnalyticsLog;

class AnalyticsBrowserVersionField extends AnalyticsField
{
    protected $schemaComponent = 'AnalyticsBrowserVersionField';

    public function __construct(string $title = null) {
        if(!$title) {
            $title = _t(__CLASS__ . '.TITLE', 'Browser versions');
        }
        $this->addExtraClass('analytics-browser-version-field');
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
        $query->setFrom("\"{$table}\"");
        $query->setSelect("\"{$table}\".\"BrowserName\"");
        $query->addSelect("\"{$table}\".\"BrowserVersion\"");
        $query->addSelect("COUNT(*) as Count");
        $query->setGroupBy(["\"{$table}\".\"BrowserName\"", "\"{$table}\".\"BrowserVersion\""]);
        $query->setOrderBy("Count", "DESC");
        $query->setLimit(20);
        $result = $query->execute();

        $map = [];
        foreach($result as $record) {
            $shortName = reset($record);
            $version = next($record);
            $count = next($record);
            $browserName = array_key_exists($shortName, Browser::getAvailableBrowsers()) ? Browser::getAvailableBrowsers()[$shortName] : DeviceDetector::UNKNOWN;
            $map[$browserName . ' ' . $version] = $count;
        }

        return $map;
    }
}
