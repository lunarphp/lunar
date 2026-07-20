<?php

namespace Lunar\Panel\Http\Controllers\Settings;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Panel\Http\Controllers\Concerns\ResolvesTableExtensions;
use Spatie\Activitylog\Models\Activity;

/**
 * A read-only, paginated view over the activity log. Every Lunar model that
 * logs activity lands here; the panel is a viewer, not a manager.
 */
class ActivityLogController
{
    use ResolvesTableExtensions;

    /** @var array<int, array{key: string, label: string, width?: string, align?: string}> */
    protected array $columns = [];

    public function index(Request $request): Response
    {
        $this->columns = [
            ['key' => 'description', 'label' => __('panel::activity_log.column_activity'), 'width' => 'minmax(0, 1.6fr)'],
            ['key' => 'subject', 'label' => __('panel::activity_log.column_subject'), 'width' => 'minmax(0, 1fr)'],
            ['key' => 'causer_name', 'label' => __('panel::activity_log.column_causer'), 'width' => 'minmax(0, 1fr)'],
            ['key' => 'created_at', 'label' => __('panel::activity_log.column_when'), 'width' => '160px'],
        ];

        $resolver = $this->resolveTable('activity-log.index');

        $subjectTypes = Activity::query()
            ->select('subject_type')
            ->whereNotNull('subject_type')
            ->distinct()
            ->orderBy('subject_type')
            ->pluck('subject_type');

        $events = Activity::query()
            ->select('event')
            ->whereNotNull('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');

        $activities = Activity::query()
            ->with('causer')
            ->when($request->filled('subject_type'), fn ($query) => $query->where('subject_type', $request->string('subject_type')->value()))
            ->when($request->filled('event'), fn ($query) => $query->where('event', $request->string('event')->value()))
            ->tap(fn ($query) => $resolver->applyColumnQueries($query))
            ->tap(fn ($query) => $resolver->applyFilters($query, $request))
            ->latest()
            ->latest('id')
            ->paginate(25)
            ->withQueryString()
            ->through(function (Activity $activity) use ($resolver): array {
                $row = [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'event' => $activity->event,
                    'subject_type' => $activity->subject_type ? class_basename($activity->subject_type) : null,
                    'subject_id' => $activity->subject_id,
                    'causer_name' => $activity->causer?->full_name ?? $activity->causer?->name ?? null,
                    'changes' => $activity->changes(),
                    'created_at' => $activity->created_at,
                    '_actions' => $resolver->resolveRowActionUrls($activity),
                ];

                foreach ($resolver->getColumnKeys() as $key) {
                    $row[$key] = $activity->getAttribute($key);
                }

                return $row;
            });

        return Inertia::render('settings/activity-log/Index', [
            'activities' => $activities,
            ...$this->tableProps($resolver, $this->columns, $request),
            'subjectTypes' => $subjectTypes->map(fn (string $type) => [
                'value' => $type,
                'label' => class_basename($type),
            ]),
            'events' => $events,
            'filters' => $request->only(['subject_type', 'event']),
            'urls' => [
                'index' => route('panel.settings.activity-log.index'),
            ],
        ]);
    }
}
