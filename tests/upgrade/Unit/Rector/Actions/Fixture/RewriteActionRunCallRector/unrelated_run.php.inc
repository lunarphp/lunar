<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Actions\Fixture\RewriteActionRunCallRector;

final class Job
{
    public static function run(): void {}
}

function leave_unrelated_alone(): void
{
    Job::run();
}
