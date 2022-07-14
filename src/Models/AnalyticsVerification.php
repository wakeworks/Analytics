<?php

namespace WakeWorks\Analytics\Models;

use SilverStripe\ORM\DataObject;
use Ramsey\Uuid\Uuid;
use SilverStripe\ORM\Queries\SQLDelete;

class AnalyticsVerification extends DataObject {

    private static $table_name = 'AnalyticsVerification';

    private static $db = [
        'UUID' => 'Varchar(36)',
        'Data' => 'Text'
    ];

    private static $indexes = [
        'UUID' => [
            'type' => 'unique',
            'columns' => ['UUID']
        ]
    ];

    public static function generate_and_write($analyticsLog) {
        $verification = new AnalyticsVerification();
        $verification->UUID = Uuid::uuid4()->toString();
        $verification->Data = json_encode($analyticsLog->toMap());
        $verification->write();

        return $verification;
    }

    public static function get_by_uuid($uuid) {
        return AnalyticsVerification::get()->filter(['UUID' => $uuid])->first();
    }

    public static function garbage_collection() {
        $fiveMinutesAgo = new \DateTime();
        $fiveMinutesAgo->sub(new \DateInterval('PT' . '5' . 'M'));

        $table = DataObject::getSchema()->tableName(self::class);
        $query = new SQLDelete();
        $query->setFrom("\"{$table}\"");
        $query->addWhere("\"{$table}\".\"Created\" < '{$fiveMinutesAgo->format('Y-m-d H:i:s')}'");
        $query->execute();
    }

    public function dataToAnalyticsLog() {
        $decoded = json_decode($this->Data, true);
        $analyticsLog = new AnalyticsLog();
        $analyticsLog->update($decoded);

        return $analyticsLog;
    }

}