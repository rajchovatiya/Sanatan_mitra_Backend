<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Poster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosterCategoryTidyTest extends TestCase
{
    use RefreshDatabase;

    private function cat(array|string $name): Category
    {
        return Category::create(['type' => 'poster', 'name' => is_array($name) ? $name : ['en' => $name]]);
    }

    private function poster(Category $c, array $extra = []): Poster
    {
        return Poster::create(['category_id' => $c->id, 'title' => ['en' => 'P'], 'image_path' => 'posters/p.jpg'] + $extra);
    }

    private function runTidy(): void
    {
        (require database_path('migrations/2026_11_19_100100_regroup_poster_categories.php'))->up();
    }

    public function test_weekday_categories_become_one_days_category(): void
    {
        $mon = $this->cat('Monday');
        $thu = $this->cat('Thrusday');
        $a = $this->poster($mon);
        $b = $this->poster($thu, ['weekdays' => [7]]);

        $this->runTidy();

        $days = Category::ofType('poster')->get()->first(fn ($c) => $c->name['en'] === 'Days');
        $this->assertNotNull($days);
        $this->assertSame([1], $a->fresh()->weekdays);
        $this->assertSame([4, 7], $b->fresh()->weekdays);
        $this->assertSame($days->id, $a->fresh()->category_id);
        $this->assertModelMissing($mon);
        $this->assertModelMissing($thu);
    }

    public function test_ram_ji_moves_into_bhagwan_and_order_is_set(): void
    {
        $love = $this->cat('Love');
        $god = $this->cat(['en' => 'ભગવાન']);
        $ram = $this->cat('Ram ji');
        $morning = $this->cat(['en' => 'સુપ્રભાત']);
        $p = $this->poster($ram);

        $this->runTidy();

        $this->assertSame($god->id, $p->fresh()->category_id);
        $this->assertModelMissing($ram);
        $order = Category::ofType('poster')->orderBy('sort_order')->pluck('id')->all();
        $this->assertSame([$morning->id, $god->id, $love->id], $order);
    }

    public function test_api_lists_todays_picks_for_today_all(): void
    {
        $c = $this->cat('Suvichar');
        $picked = $this->poster($c, ['today_on' => '2026-11-19']);
        $this->poster($c, ['today_on' => '2026-11-20']);
        $this->poster($c);

        $this->getJson('/api/v1/posters?date=2026-11-19')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $picked->id)
            ->assertJsonPath('data.0.today_on', '2026-11-19');
        $this->getJson('/api/v1/posters?date=19-11-2026')->assertUnprocessable();
    }
}
