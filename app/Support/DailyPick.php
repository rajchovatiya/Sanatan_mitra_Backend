<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/** Picks the same row for everyone on a given day, rotating through the rows day by day. */
final class DailyPick
{
    public static function from(Builder $query, Carbon $day): ?Model
    {
        $count = (clone $query)->count();

        return $count ? $query->orderBy('id')->skip($day->dayOfYear % $count)->first() : null;
    }
}
