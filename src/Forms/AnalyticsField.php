<?php

namespace WakeWorks\Analytics\Forms;

use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FormField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\Queries\SQLSelect;
use WakeWorks\Analytics\Extensions\SubsitesExtension;
use WakeWorks\Analytics\Middlewares\AnalyticsProcessorMiddleware;
use WakeWorks\Analytics\Models\AnalyticsLog;

class AnalyticsField extends FieldGroup
{
    protected $schemaDataType = FormField::SCHEMA_DATA_TYPE_CUSTOM;

    public function __construct(string $title = null) {
        $this->addExtraClass('analytics-field');
        $this->setFieldHolderTemplate('WakeWorks\\Analytics\\Forms\\AnalyticsField');
        $this->setSmallFieldHolderTemplate('WakeWorks\\Analytics\\Forms\\AnalyticsField');
        parent::__construct($title, []);
    }

    public function getSchemaStateDefaults()
    {
        $state = parent::getSchemaStateDefaults();
        $state['className'] = self::class;
        $state['title'] = $this->Title();
        return $state;
    }

    public function createSQLSelect($unique = false) {
        $table = DataObject::getSchema()->tableName(AnalyticsLog::class);
        $query = new SQLSelect();
        $query->setFrom("\"{$table}\"");
        if($unique) {
            $query->addWhere("\"{$table}\".\"IsFirstVisit\" = 1");
        }

        // Check for extensions
        if(AnalyticsLog::has_extension(SubsitesExtension::class)) {
            $currentId = \SilverStripe\Subsites\State\SubsiteState::singleton()->getSubsiteId();
            if($currentId) {
                $query->addWhere("\"{$table}\".\"SubsiteID\" = {$currentId}");
            } else {
                $query->addWhere("\"{$table}\".\"SubsiteID\" = 0 OR \"{$table}\".\"SubsiteID\" IS NULL");
            }
        }

        return $query;
    }
}
