<?php

namespace WakeWorks\Analytics\Forms;

use SilverStripe\ORM\DataObject;
use WakeWorks\Analytics\Models\AnalyticsLog;
use WakeWorks\Analytics\Models\AnalyticsURL;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\DataQuery;

class AnalyticsPagesField extends AnalyticsField
{
    protected $schemaComponent = 'AnalyticsPagesField';

    public function __construct(string $title = null) {
        if(!$title) {
            $title = _t(__CLASS__ . '.TITLE', 'Most viewed pages');
        }
        $this->addExtraClass('analytics-pages-field');
        parent::__construct($title, []);
    }

    public function getSchemaStateDefaults()
    {
        $state = parent::getSchemaStateDefaults();
        $state['chartData'] = [
            'Pages' => $this->getPages(),
            'URLs' => $this->getUrls()
        ];
        return $state;
    }

    private function getPages() {
        $params = [];
        $table = DataObject::getSchema()->tableName(AnalyticsLog::class);
        $siteTreeTable = DataObject::getSchema()->tableName(SiteTree::class);
        $query = parent::createSQLSelect();
        $query->setSelect("\"{$table}\".\"PageID\"");
        $query->addSelect("\"{$siteTreeTable}\".\"Title\"");
        $query->addSelect("COUNT(*) as Count");
        $query->addLeftJoin(
            '(' . (new DataQuery(SiteTree::class))->sql($params) . ')',
            "\"{$table}\".\"PageID\" = \"{$siteTreeTable}\".\"ID\"",
            $siteTreeTable,
            20,
            $params
        );
        $query->addWhere("\"{$table}\".\"PageID\" != 0");
        $query->setGroupBy("\"{$table}\".\"PageID\"");
        $query->setOrderBy("Count", "DESC");
        $query->setLimit(20);
        $result = $query->execute();

        $obj = [];
        foreach($result as $record) {
            $pageId = reset($record);
            $pageTitle = next($record) ?? '';
            $count = next($record);
            array_push($obj, [
                'ID' => $pageId,
                'Count' => $count,
                'Title' => $pageTitle
            ]);
        }

        return $obj;
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
            array_push($obj, [
                'URL' => $url,
                'Count' => $count,
                'PageID' => $pageId
            ]);
        }

        return $obj;
    }
}
