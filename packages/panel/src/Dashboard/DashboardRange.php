<?php

namespace Lunar\Panel\Dashboard;

use Carbon\CarbonImmutable;

/**
 * The dashboard reporting window. Every widget aggregates over the same
 * bounds and compares against the same previous period, so the whole page
 * always agrees on what "the last 30 days" means.
 */
enum DashboardRange: string
{
    case Today = 'today';
    case SevenDays = '7d';
    case ThirtyDays = '30d';
    case NinetyDays = '90d';

    public static function fromValue(?string $value, self $default = self::ThirtyDays): self
    {
        return self::tryFrom((string) $value) ?? $default;
    }

    public function days(): int
    {
        return match ($this) {
            self::Today => 1,
            self::SevenDays => 7,
            self::ThirtyDays => 30,
            self::NinetyDays => 90,
        };
    }

    /** Inclusive lower bound: the start of the first day in the window. */
    public function start(): CarbonImmutable
    {
        return $this->end()->subDays($this->days());
    }

    /** Exclusive upper bound: the start of tomorrow, so today is always included. */
    public function end(): CarbonImmutable
    {
        return CarbonImmutable::now()->startOfDay()->addDay();
    }

    public function previousStart(): CarbonImmutable
    {
        return $this->start()->subDays($this->days());
    }

    /** Exclusive; the previous window ends where the current one starts. */
    public function previousEnd(): CarbonImmutable
    {
        return $this->start();
    }

    /**
     * Chart buckets across the window: hourly for Today, daily otherwise.
     *
     * @return array<int, array{start: CarbonImmutable, end: CarbonImmutable, label: string}>
     */
    public function buckets(): array
    {
        $buckets = [];

        if ($this === self::Today) {
            $dayStart = $this->start();

            for ($hour = 0; $hour < 24; $hour++) {
                $start = $dayStart->addHours($hour);

                $buckets[] = ['start' => $start, 'end' => $start->addHour(), 'label' => $start->format('H:00')];
            }

            return $buckets;
        }

        for ($day = 0; $day < $this->days(); $day++) {
            $start = $this->start()->addDays($day);

            $buckets[] = ['start' => $start, 'end' => $start->addDay(), 'label' => $start->translatedFormat('d M')];
        }

        return $buckets;
    }
}
