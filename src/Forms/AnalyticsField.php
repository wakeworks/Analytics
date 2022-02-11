<?php

namespace Zazama\Analytics\Forms;

use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FormField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\Queries\SQLSelect;
use Zazama\Analytics\Middlewares\AnalyticsProcessorMiddleware;
use Zazama\Analytics\Models\AnalyticsLog;

class AnalyticsField extends FieldGroup
{
    protected $schemaDataType = FormField::SCHEMA_DATA_TYPE_CUSTOM;

    public function __construct(string $title = null) {
        $this->addExtraClass('analytics-field');
        $this->setFieldHolderTemplate('Zazama\\Analytics\\Forms\\AnalyticsField');
        $this->setSmallFieldHolderTemplate('Zazama\\Analytics\\Forms\\AnalyticsField');
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
        if(Config::inst()->get(AnalyticsProcessorMiddleware::class, 'image_verification')) {
            $query->addWhere("\"{$table}\".\"IsImageVerified\" = 1");
        }

        return $query;
    }
}
