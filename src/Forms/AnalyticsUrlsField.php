<?php

namespace WakeWorks\Analytics\Forms;

use SilverStripe\ORM\DataObject;
use WakeWorks\Analytics\Models\AnalyticsLog;
use WakeWorks\Analytics\Models\AnalyticsURL;

class AnalyticsUrlsField extends AnalyticsField
{
    protected $schemaComponent = 'AnalyticsUrlsField';

    public function __construct(string $title = null) {
        if(!$title) {
            $title = _t(__CLASS__ . '.TITLE', 'Most viewed URLs');
        }
        $this->addExtraClass('analytics-urls-field');
        parent::__construct($title, []);
    }

    public function getSchemaStateDefaults()
    {
        $state = parent::getSchemaStateDefaults();
        $state['chartData'] = $this->getUrls();
        return $state;
    }

    private function getUrls() {
        $table = DataObject::getSchema()->tableName(AnalyticsLog::class);
        $urlTable = DataObject::getSchema()->tableName(AnalyticsURL::class);
        $query = parent::createSQLSelect();
        $query->setSelect("\"{$table}\".\"URLID\"");
        $query->addSelect("\"{$urlTable}\".\"URL\"");
        $query->addSelect("MAX(\"{$table}\".\"PageID\")");
        $query->addSelect("COUNT(*) as Count");
        $query->addLeftJoin($urlTable, "\"{$table}\".\"URLID\" = \"{$urlTable}\".\"ID\"");
        $query->addWhere("\"{$table}\".\"URLID\" != 0");
        $query->setGroupBy("\"{$table}\".\"URLID\"");
        $query->setOrderBy("Count", "DESC");
        $query->setLimit(20);
        $result = $query->execute();

        $obj = [];
        foreach($result as $record) {
            reset($record);
            $url = next($record);
            $pageId = next($record);
            $count = next($record);
            $obj[$url] = [
                'Count' => $count,
                'PageID' => $pageId
            ];
        }

        return $obj;
    }
}
