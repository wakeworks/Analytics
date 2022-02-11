<?php

namespace Zazama\Analytics\Forms;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\AbstractDeviceParser;
use SilverStripe\ORM\DataObject;
use Zazama\Analytics\Models\AnalyticsLog;

class AnalyticsDeviceField extends AnalyticsField
{
    protected $schemaComponent = 'AnalyticsDeviceField';

    public function __construct(string $title = null) {
        if(!$title) {
            $title = _t(__CLASS__ . '.TITLE', 'Devices');
        }
        $this->addExtraClass('analytics-device-field');
        parent::__construct($title, []);
    }

    public function getSchemaStateDefaults()
    {
        $state = parent::getSchemaStateDefaults();
        $state['chartData'] = $this->getDevices();
        return $state;
    }

    private function getDevices() {
        $table = DataObject::getSchema()->tableName(AnalyticsLog::class);
        $query = parent::createSQLSelect(true);
        $query->setFrom("\"{$table}\"");
        $query->setSelect("\"{$table}\".\"DeviceType\"");
        $query->addSelect("COUNT(*) as Count");
        $query->setGroupBy("\"{$table}\".\"DeviceType\"");
        $query->setOrderBy("Count", "DESC");
        $query->setLimit(20);
        $result = $query->execute();

        $map = [];
        foreach($result as $record) {
            $deviceType = reset($record);
            $count = next($record);
            $deviceName = AbstractDeviceParser::getDeviceName($deviceType) !== false ? AbstractDeviceParser::getDeviceName($deviceType) : DeviceDetector::UNKNOWN;
            $map[$deviceName] = $count;
        }

        return $map;
    }
}
