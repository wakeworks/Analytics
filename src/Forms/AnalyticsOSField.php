<?php

namespace Zazama\Analytics\Forms;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\OperatingSystem;
use SilverStripe\ORM\DataObject;
use Zazama\Analytics\Models\AnalyticsLog;

class AnalyticsOSField extends AnalyticsField
{
    protected $schemaComponent = 'AnalyticsOSField';

    public function __construct(string $title = null) {
        if(!$title) {
            $title = _t(__CLASS__ . '.TITLE', 'Operating Systems');
        }
        $this->addExtraClass('analytics-os-field');
        parent::__construct($title, []);
    }

    public function getSchemaStateDefaults()
    {
        $state = parent::getSchemaStateDefaults();
        $state['chartData'] = $this->getOS();
        return $state;
    }

    private function getOS() {
        $table = DataObject::getSchema()->tableName(AnalyticsLog::class);
        $query = parent::createSQLSelect(true);
        $query->setSelect("\"{$table}\".\"OSName\"");
        $query->addSelect("COUNT(*) as Count");
        $query->setGroupBy("\"{$table}\".\"OSName\"");
        $query->setOrderBy("Count", "DESC");
        $query->setLimit(20);
        $result = $query->execute();

        $map = [];
        foreach($result as $record) {
            $shortName = reset($record);
            $count = next($record);
            $osName = OperatingSystem::getNameFromId($shortName) ?? DeviceDetector::UNKNOWN;
            $map[$osName] = $count;
        }

        return $map;
    }
}
