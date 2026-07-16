<?php

namespace App\Domain\Loads;

/**
 * Lifecycle of a load. Transitions are guarded — see allowedNext().
 */
enum LoadStatus: string
{
    case Quoted = 'quoted';
    case Booked = 'booked';
    case Dispatched = 'dispatched';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';
    case Invoiced = 'invoiced';
    case Cancelled = 'cancelled';

    /**
     * @return list<LoadStatus>
     */
    public function allowedNext(): array
    {
        return match ($this) {
            self::Quoted => [self::Booked, self::Cancelled],
            self::Booked => [self::Dispatched, self::Cancelled],
            self::Dispatched => [self::InTransit, self::Cancelled],
            self::InTransit => [self::Delivered],
            self::Delivered => [self::Invoiced],
            self::Invoiced, self::Cancelled => [],
        };
    }

    public function canTransitionTo(LoadStatus $target): bool
    {
        return in_array($target, $this->allowedNext(), true);
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $s) => $s->value, self::cases());
    }
}
