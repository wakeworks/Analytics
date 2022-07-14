<?php

use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\Queries\SQLDelete;
use WakeWorks\Analytics\Middlewares\AnalyticsProcessorMiddleware;
use WakeWorks\Analytics\Models\AnalyticsLog;

class AnalyticsGarbageCollectionTask extends BuildTask {

    private static $segment = 'AnalyticsGarbageCollectionTask';

    protected $title = 'Remove old or unverified analytic logs';

    public function run($request, $silent = false)
    {
        $preserve_for_days = Config::inst()->get(AnalyticsProcessorMiddleware::class, 'preserve_for_days');
        $time_start = microtime(true);

        if(!is_int($preserve_for_days) || $preserve_for_days < 0) {
            if(!$silent) {
                user_error('preserve_for_days is not an integer.');
            }
            return;
        }

        $timeDiff = new DateInterval('P' . $preserve_for_days . 'D');
        $removeDate = new DateTime();
        $removeDate->sub($timeDiff);

        $countBefore = AnalyticsLog::get()->count();

        // DataList::removeAll() loops through all items and deletes them one by one, which is too inefficient in this case
        $table = DataObject::getSchema()->tableName(AnalyticsLog::class);
        $query = new SQLDelete();
        $query->setFrom("\"{$table}\"");
        $query->addWhere("\"{$table}\".\"Date\" < '{$removeDate->format('Y-m-d')}'");
        $query->execute();

        if(!$silent) {
            echo 'Deleted ' . ($countBefore - AnalyticsLog::get()->count()) . ' rows in ' . number_format((microtime(true) - $time_start), 2) . 's.';
        }
    }

    public function getDescription() {
        $description = "Removes the following analytic logs:\n\n";
        $description .= '- older than ' . Config::inst()->get(AnalyticsProcessorMiddleware::class, 'preserve_for_days') . " days\n";

        return $description;
    }
}