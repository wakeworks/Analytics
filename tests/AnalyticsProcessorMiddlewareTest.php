<?php

use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\FunctionalTest;
use WakeWorks\Analytics\Middlewares\AnalyticsProcessorMiddleware;
use WakeWorks\Analytics\Models\AnalyticsLog;
use Defuse\Crypto\Crypto;

class AnalyticsProcessorMiddlewareTest extends FunctionalTest
{
    protected $usesDatabase = true;

    protected static $fixture_file = 'AnalyticsProcessorMiddlewareTest.yml';

    public function setUp(): void {
        parent::setUp();

        $homePage = $this->objFromFixture('Page', 'home');
        $homePage->doPublish();

        Config::modify()->update(AnalyticsProcessorMiddleware::class, 'image_verification', false);

        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36';

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

    public function testIgnorePages() {
        $this->assertEquals(1, AnalyticsLog::get()->count());
    }

    public function testDisable() {
        Config::modify()->update(AnalyticsProcessorMiddleware::class, 'enabled', false);
        $countBefore = AnalyticsLog::get()->count();

        // Call home
        $this->get('home');

        // Check if new log has been created
        $this->assertEquals($countBefore, AnalyticsLog::get()->count());
    }

    public function testImageTracking() {
        Config::modify()->update(AnalyticsProcessorMiddleware::class, 'image_verification', true);
        Config::modify()->update(AnalyticsProcessorMiddleware::class, 'secret_key', '1234');

        // Check if tracking code is inserted into html
        $body = $this->get('home')->getBody();
        $this->assertNotFalse(preg_match("/<img src=\"\\/\\?analyticsimage=(.*?)\"/", $body, $matches));
        $this->assertArrayHasKey(1, $matches);

        $currentModel = AnalyticsLog::get()->sort('ID', 'DESC')->first();

        // Check if decryptable and ID same as last log entry
        try {
            $id = Crypto::decryptWithPassword($matches[1], '1234');

            $this->assertEquals($currentModel->ID, $id);
        } catch(\Exception $e) {
            $this->fail();
        }

        $this->assertEquals($currentModel->IsImageVerified, 0);

        // Call verification url
        $this->get('/?analyticsimage=' . $matches[1]);

        // Check if no new log is created for the image verification & check that it's verified
        $nextModel = AnalyticsLog::get()->sort('ID', 'DESC')->first();
        $this->assertEquals($currentModel->ID, $nextModel->ID);
        $this->assertEquals($nextModel->IsImageVerified, 1);
    }

    public function testGC() {
        Config::modify()->update(AnalyticsProcessorMiddleware::class, 'gc_divisor', 1);
        Config::modify()->update(AnalyticsProcessorMiddleware::class, 'preserve_for_days', 365);

        $countBefore = AnalyticsLog::get()->count();
        $this->get('home');
        // Nothing should be deleted yet
        $this->assertEquals($countBefore + 1, AnalyticsLog::get()->count());

        $model = AnalyticsLog::get()->first();
        $model->Date = date('Y-m-d', strtotime('-364 days'));
        $model->write();

        // Still nothing should be deleted
        $countBefore = AnalyticsLog::get()->count();
        $this->get('home');
        $this->assertEquals($countBefore + 1, AnalyticsLog::get()->count());

        $model->Date = date('Y-m-d', strtotime('-366 days'));
        $model->write();

        $countBefore = AnalyticsLog::get()->count();
        $this->get('home');
        // Check if one has been deleted
        $this->assertEquals($countBefore, AnalyticsLog::get()->count());
    }
}