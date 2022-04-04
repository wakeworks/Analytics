<?php

namespace WakeWorks\Analytics;

class Analytics {

    private $analytics_log = null;
    private $disabled = false;

    public function setAnalyticsLog($analyticsLog) {
        $this->analytics_log = $analyticsLog;
    }

    public function getAnalyticsLog() {
        return $this->analytics_log;
    }

    public function disable() {
        $this->disabled = true;
    }

    public function enable() {
        $this->disabled = false;
    }

    public function isDisabled() {
        return $this->disabled;
    }
}