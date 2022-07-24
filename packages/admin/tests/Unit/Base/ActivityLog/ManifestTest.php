<?php

namespace GetCandy\Hub\Tests\Unit\Base\ActivityLog;

use GetCandy\Hub\Facades\ActivityLog;
use GetCandy\Hub\Tests\Stubs\ActivityLogRenderer;
use GetCandy\Hub\Tests\TestCase;

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
