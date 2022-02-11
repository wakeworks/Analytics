<?php

use SilverStripe\Dev\FunctionalTest;
use Zazama\Analytics\Models\AnalyticsLog;

class AnalyticsProcessorMiddlewareTest extends FunctionalTest
{
    protected $usesDatabase = true;

    protected static $fixture_file = 'AnalyticsProcessorMiddlewareTest.yml';

    public function setUp(): void {
        parent::setUp();

        $homePage = $this->objFromFixture('Page', 'home');
        $homePage->doPublish();

        // 200
        $this->get($homePage->RelativeLink());

        // 302
        $this->get('admin/');

        // 404
        $this->get('definitelynotfoundihope/');

        // Ignore admin
        $this->logInWithPermission('ADMIN');
        $this->get('admin/pages');

        // ignore Security ping
        $this->get('Security/ping');
    }

    public function testResponseCode() {
        $this->assertEquals(1, AnalyticsLog::get()->filter('ResponseCode', 200)->count());
        $this->assertEquals(1, AnalyticsLog::get()->filter('ResponseCode', 302)->count());
        $this->assertEquals(1, AnalyticsLog::get()->filter('ResponseCode', 404)->count());
    }

    public function testIgnorePages() {
        $this->assertEquals(3, AnalyticsLog::get()->count());
    }
}