<?php

use Lunar\Blog\Panel\Requests\BlogArticleRequest;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Blog\TestCase;

uses(TestCase::class);

it('keeps the published_at instant correct when app.timezone is not UTC', function () {
    $staff = Staff::factory()->create();

    // Laravel sets PHP's default timezone from config('app.timezone') once,
    // at framework bootstrap (LoadConfiguration::bootstrap()); overriding the
    // config value mid-test does not retroactively change it. Flip the real
    // PHP default timezone to reproduce a non-UTC app.timezone, and restore
    // it afterwards so other tests are not affected.
    $originalTimezone = date_default_timezone_get();
    date_default_timezone_set('America/New_York');
    config([
        'app.timezone' => 'America/New_York',
        'lunar-blog.publish_timezone' => 'Europe/London',
    ]);

    try {
        // Build and fully resolve (prepareForValidation + validation) the
        // request, the same lifecycle Laravel runs when the panel controller
        // type-hints it, without needing an HTTP round trip or a routed
        // article.
        $request = BlogArticleRequest::create('/panel/blog/articles', 'POST', [
            'title' => 'Timezone safe publishing',
            'author_id' => $staff->id,
            'published_at' => '2026-06-01T12:00',
        ]);
        $request->setContainer(app())->setRedirector(app('redirect'));
        $request->validateResolved();

        // 1 June is BST (+1), so 12:00 UK wall-clock is 11:00 UTC. Before the
        // fix, articleData() re-parsed the already-UTC value as a tz-less
        // string, so it fell back to app.timezone (America/New_York, -4 in
        // June) and produced a completely different instant.
        expect($request->articleData()['published_at']->utc()->format('Y-m-d H:i'))
            ->toBe('2026-06-01 11:00');
    } finally {
        date_default_timezone_set($originalTimezone);
    }
});
