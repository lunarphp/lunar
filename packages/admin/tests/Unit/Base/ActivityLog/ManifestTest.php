<?php

namespace Lunar\Hub\Tests\Unit\Base\ActivityLog;

use Lunar\Hub\Facades\ActivityLog;
use Lunar\Hub\Tests\Stubs\ActivityLogRenderer;
use Lunar\Hub\Tests\TestCase;

/**
 * @group hub.activity-log
 */
class ManifestTest extends TestCase
{
    /** @test */
    public function can_add_renderer()
    {
        $this->assertCount(0, ActivityLog::getItems('foo'));

        ActivityLog::addRender('foo', ActivityLogRenderer::class);

        $this->assertCount(1, ActivityLog::getItems('foo'));

        $this->assertCount(0, ActivityLog::getItems('bar'));
    }
}
