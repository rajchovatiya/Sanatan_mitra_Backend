<?php

use App\Models\Category;
use App\Models\Poster;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * One-time tidy-up of the poster categories (client request, 2026-11-19):
 *  1. Monday … Sunday categories become one "Days" category; each poster keeps
 *     its day as a ticked weekday, so the app still shows it first on that day.
 *  2. "Ram ji" posters move into "ભગવાન" (God) and the Ram ji category goes.
 *  3. A sensible order for the category chips.
 * Matches categories by name, so it does nothing on a database without them.
 */
return new class extends Migration
{
    private const DAYS = [
        'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'thrusday' => 4,
        'friday' => 5, 'saturday' => 6, 'sunday' => 7,
    ];

    /** Chip order: names (any language, lower case) → position. Others follow, as they were. */
    private const ORDER = [
        ['સુપ્રભાત', 'suprabhat', 'good morning'],
        ['days', 'વાર', 'वार'],
        ['ભગવાન', 'bhagwan', 'bhagvan', 'god'],
        ['mahadev'],
        ['khatushyam'],
        ['તહેવાર', 'festival', 'tehvar'],
        ['સુવિચાર', 'suvichar'],
        ['motivation'],
        ['krishna uvach'],
        ['shrimad bhagwadgita', 'bhagavad gita'],
        ['chanakya niti'],
        ['jyotish'],
        ['daily'],
        ['love'],
        ['anniversary'],
    ];

    public function up(): void
    {
        $cats = Category::ofType('poster')->get();
        $named = fn (Category $c) => array_map(
            fn ($v) => mb_strtolower(trim((string) $v)),
            array_values((array) $c->name)
        );

        DB::transaction(function () use ($cats, $named) {
            // 1. Days.
            $dayCats = $cats->filter(fn ($c) => array_intersect($named($c), array_keys(self::DAYS)));
            if ($dayCats->isNotEmpty()) {
                $days = Category::create([
                    'type' => 'poster',
                    'name' => ['en' => 'Days', 'hi' => 'वार', 'gu' => 'વાર'],
                    'is_active' => true,
                ]);
                foreach ($dayCats as $c) {
                    $day = self::DAYS[array_values(array_intersect($named($c), array_keys(self::DAYS)))[0]];
                    foreach (Poster::where('category_id', $c->id)->get() as $p) {
                        $weekdays = collect($p->weekdays ?? [])->push($day)->map(fn ($d) => (int) $d)
                            ->unique()->sort()->values()->all();
                        $p->forceFill(['category_id' => $days->id, 'weekdays' => $weekdays])->save();
                    }
                    $c->delete();
                }
            }

            // 2. Ram ji → ભગવાન.
            $ram = $cats->first(fn ($c) => array_intersect($named($c), ['ram ji', 'ramji', 'ram']));
            $god = $cats->first(fn ($c) => array_intersect($named($c), ['ભગવાન', 'bhagwan', 'bhagvan', 'god']));
            if ($ram && $god) {
                Poster::where('category_id', $ram->id)->update(['category_id' => $god->id]);
                $ram->delete();
            }

            // 3. Order.
            foreach (Category::ofType('poster')->get() as $c) {
                foreach (self::ORDER as $pos => $names) {
                    if (array_intersect($named($c), $names)) {
                        $c->forceFill(['sort_order' => ($pos + 1) * 10])->save();
                        continue 2;
                    }
                }
                $c->forceFill(['sort_order' => 1000 + $c->id])->save();
            }
        });
    }

    public function down(): void
    {
        // Not reversible: the old categories are gone (their posters are kept).
    }
};
