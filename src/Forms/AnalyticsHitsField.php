<?php

namespace WakeWorks\Analytics\Forms;

use SilverStripe\ORM\DataObject;
use WakeWorks\Analytics\Models\AnalyticsLog;

class AnalyticsHitsField extends AnalyticsField
{
    protected $schemaComponent = 'AnalyticsHitsField';

    public function __construct(string $title = null) {
        if(!$title) {
            $title = _t(__CLASS__ . '.TITLE', 'Daily visitors');
        }
        $this->addExtraClass('analytics-hits-field');
        parent::__construct($title, []);
    }

    public function getSchemaStateDefaults()
    {
        $state = parent::getSchemaStateDefaults();
        $state['chartData'] = [
            'Hits' => $this->getDailyState(),
            'Unique' => $this->getDailyState(true)
        ];
        return $state;
    }

    private function getDailyState($unique = false) {
        $table = DataObject::getSchema()->tableName(AnalyticsLog::class);
        $query = parent::createSQLSelect($unique);
        $query->setSelect("\"{$table}\".\"Date\" as Date");
        $query->addSelect("COUNT(*) as Count");
        $query->setGroupBy("\"{$table}\".\"Date\"");
        $query->setOrderBy("Date");
        $result = $query->execute();

        $map = $result->map();
        $dates = array_keys($map);
        return [
            'Start' => count($dates) > 0 ? $dates[0] : null,
            'End' => count($dates) > 0 ? $dates[count($dates) - 1] : null,
            'Days' => $map
        ];
    }
}
