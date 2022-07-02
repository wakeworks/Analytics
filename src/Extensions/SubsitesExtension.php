<?php

namespace WakeWorks\Analytics\Extensions;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Subsites\Model\Subsite;
use SilverStripe\Subsites\State\SubsiteState;

class SubsitesExtension extends DataExtension {
    private static $has_one = [
        'Subsite' => Subsite::class
    ];

    public function updateProcessAfterDelegate(HTTPRequest $request, HTTPResponse $response) {
        $currentSubsiteId = SubsiteState::singleton()->getSubsiteId();

        // This means we're on the main page, then we leave SubsiteID at null
        if($currentSubsiteId === 0 || !$currentSubsiteId) {
            return;
        }

        $this->owner->SubsiteID = $currentSubsiteId;
    }
}