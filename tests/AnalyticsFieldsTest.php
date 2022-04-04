<?php

use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\FunctionalTest;
use WakeWorks\Analytics\Forms\AnalyticsBrowserField;
use WakeWorks\Analytics\Forms\AnalyticsBrowserVersionField;
use WakeWorks\Analytics\Forms\AnalyticsDeviceField;
use WakeWorks\Analytics\Forms\AnalyticsHitsField;
use WakeWorks\Analytics\Forms\AnalyticsOSField;
use WakeWorks\Analytics\Middlewares\AnalyticsProcessorMiddleware;

class AnalyticsFieldsTest extends FunctionalTest
{
    protected $usesDatabase = true;

    protected static $fixture_file = 'AnalyticsFieldsTest.yml';

    private $user_agents = [
        // Chrome 60
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36',
        // Firefox 7
        'Mozilla/5.0 (Windows NT 5.1; rv:7.0.1) Gecko/20100101 Firefox/7.0.1',
        // Firefox 54
        'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0',
        // IE 9
        'Mozilla/4.0 (compatible; MSIE 9.0; Windows NT 6.1)',
        // Safari 9 on iPad
        'Mozilla/5.0 (iPad; CPU OS 9_3_2 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13F69 Safari/601.1',
        // Chrome 91 on Android
        'Mozilla/5.0 (Linux; Android 10; Android SDK built for x86) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.120 Mobile Safari/537.36'
    ];

    public function setUp(): void {
        parent::setUp();

        $homePage = $this->objFromFixture('Page', 'home');
        $homePage->doPublish();
        $this->get('home');

        Config::modify()->update(AnalyticsProcessorMiddleware::class, 'image_verification', false);

        foreach($this->user_agents as $userAgent) {
            $_SERVER['HTTP_USER_AGENT'] = $userAgent;
            $this->session()->clearAll();
            $this->get('home');
        }
    }

    public function testBrowserField() {
        $result = AnalyticsBrowserField::create()->getSchemaStateDefaults()['chartData'];

        $this->assertEquals([
            'Chrome' => 1,
            'Firefox' => 2,
            'Internet Explorer' => 1,
            'Mobile Safari' => 1,
            'Chrome Mobile' => 1
        ], $result);
    }

    public function testBrowserVersionField() {
        $result = AnalyticsBrowserVersionField::create()->getSchemaStateDefaults()['chartData'];

        $this->assertEquals([
            'Chrome 60.0' => 1,
            'Firefox 7.0' => 1,
            'Firefox 54.0' => 1,
            'Internet Explorer 9.0' => 1,
            'Mobile Safari 9.0' => 1,
            'Chrome Mobile 91.0' => 1
        ], $result);
    }

    public function testDeviceField() {
        $result = AnalyticsDeviceField::create()->getSchemaStateDefaults()['chartData'];

        $this->assertEquals([
            'desktop' => 4,
            'tablet' => 1,
            'smartphone' => 1
        ], $result);
    }

    public function testOSField() {
        $result = AnalyticsOSField::create()->getSchemaStateDefaults()['chartData'];

        $this->assertEquals([
            'Windows' => 4,
            'iOS' => 1,
            'Android' => 1
        ], $result);
    }

    public function testHitsField() {
        $result = AnalyticsHitsField::create()->getSchemaStateDefaults()['chartData'];

        $this->assertEquals([
            'Hits' => [
                'Start' => date('Y-m-d'),
                'End' => date('Y-m-d'),
                'Days' => [
                    date('Y-m-d') => 6
                ]
            ],
            'Unique' => [
                'Start' => date('Y-m-d'),
                'End' => date('Y-m-d'),
                'Days' => [
                    date('Y-m-d') => 6
                ]
            ]
        ], $result);
    }
}