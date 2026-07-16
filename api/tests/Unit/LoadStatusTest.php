<?php

use App\Domain\Loads\LoadStatus;

it('allows only legal forward transitions', function () {
    expect(LoadStatus::Quoted->canTransitionTo(LoadStatus::Booked))->toBeTrue();
    expect(LoadStatus::Booked->canTransitionTo(LoadStatus::Dispatched))->toBeTrue();
    expect(LoadStatus::InTransit->canTransitionTo(LoadStatus::Delivered))->toBeTrue();
    expect(LoadStatus::Delivered->canTransitionTo(LoadStatus::Invoiced))->toBeTrue();
});

it('rejects skipping stages or moving backwards', function () {
    expect(LoadStatus::Quoted->canTransitionTo(LoadStatus::Delivered))->toBeFalse();
    expect(LoadStatus::Delivered->canTransitionTo(LoadStatus::Booked))->toBeFalse();
    expect(LoadStatus::Invoiced->canTransitionTo(LoadStatus::Quoted))->toBeFalse();
});

it('treats invoiced and cancelled as terminal', function () {
    expect(LoadStatus::Invoiced->allowedNext())->toBe([]);
    expect(LoadStatus::Cancelled->allowedNext())->toBe([]);
});

it('can be cancelled from early active stages', function () {
    expect(LoadStatus::Quoted->canTransitionTo(LoadStatus::Cancelled))->toBeTrue();
    expect(LoadStatus::Booked->canTransitionTo(LoadStatus::Cancelled))->toBeTrue();
    expect(LoadStatus::Dispatched->canTransitionTo(LoadStatus::Cancelled))->toBeTrue();
});

it('exposes all string values', function () {
    expect(LoadStatus::values())->toContain('quoted', 'booked', 'delivered', 'invoiced', 'cancelled');
});
