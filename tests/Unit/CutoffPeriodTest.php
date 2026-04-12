<?php

use App\Support\CutoffPeriod;
use Illuminate\Support\Carbon;

it('returns first half period for dates 1-15', function () {
    $period = CutoffPeriod::forDate('2026-04-10');

    expect($period->start->toDateString())->toBe('2026-04-01')
        ->and($period->end->toDateString())->toBe('2026-04-15');
});

it('returns second half period for dates 16+', function () {
    $period = CutoffPeriod::forDate('2026-04-20');

    expect($period->start->toDateString())->toBe('2026-04-16')
        ->and($period->end->toDateString())->toBe('2026-04-30');
});

it('handles cutoff boundary date (15th) as first period', function () {
    $period = CutoffPeriod::forDate('2026-04-15');

    expect($period->start->toDateString())->toBe('2026-04-01')
        ->and($period->end->toDateString())->toBe('2026-04-15');
});

it('handles cutoff boundary date (16th) as second period', function () {
    $period = CutoffPeriod::forDate('2026-04-16');

    expect($period->start->toDateString())->toBe('2026-04-16')
        ->and($period->end->toDateString())->toBe('2026-04-30');
});

it('navigates to previous period', function () {
    // From Apr 1-15, previous should be Mar 16-31
    $period = CutoffPeriod::forDate('2026-04-10');
    $previous = $period->previous();

    expect($previous->start->toDateString())->toBe('2026-03-16')
        ->and($previous->end->toDateString())->toBe('2026-03-31');
});

it('navigates to next period', function () {
    // From Apr 1-15, next should be Apr 16-30
    $period = CutoffPeriod::forDate('2026-04-10');
    $next = $period->next();

    expect($next->start->toDateString())->toBe('2026-04-16')
        ->and($next->end->toDateString())->toBe('2026-04-30');
});

it('handles February end of month correctly', function () {
    $period = CutoffPeriod::forDate('2026-02-20');

    expect($period->start->toDateString())->toBe('2026-02-16')
        ->and($period->end->toDateString())->toBe('2026-02-28');
});

it('handles leap year February correctly', function () {
    // 2028 is a leap year
    $period = CutoffPeriod::forDate('2028-02-20');

    expect($period->start->toDateString())->toBe('2028-02-16')
        ->and($period->end->toDateString())->toBe('2028-02-29');
});

it('generates correct label', function () {
    $period = CutoffPeriod::forDate('2026-04-10');

    expect($period->label)->toBe('Apr 1 – Apr 15, 2026');
});

it('converts to array correctly', function () {
    $period = CutoffPeriod::forDate('2026-04-10');
    $array = $period->toArray();

    expect($array)->toHaveKeys(['start', 'end', 'label'])
        ->and($array['start'])->toBe('2026-04-01')
        ->and($array['end'])->toBe('2026-04-15');
});

it('respects custom cutoff_day config', function () {
    config(['attendance.cutoff_day' => 10]);

    $period = CutoffPeriod::forDate('2026-04-08');

    expect($period->start->toDateString())->toBe('2026-04-01')
        ->and($period->end->toDateString())->toBe('2026-04-10');

    $period2 = CutoffPeriod::forDate('2026-04-12');

    expect($period2->start->toDateString())->toBe('2026-04-11')
        ->and($period2->end->toDateString())->toBe('2026-04-30');

    // Reset
    config(['attendance.cutoff_day' => 15]);
});
