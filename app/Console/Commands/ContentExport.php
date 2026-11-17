<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Mantra;
use App\Models\Poster;
use App\Support\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Packs poster/mantra categories, posters and mantras (with their images and
 * audio) into database/seed-content, so another server can load them with
 * content:import. Used to move test content from a PC to the hosted server.
 */
class ContentExport extends Command
{
    protected $signature = 'content:export {--dir= : Target folder (default database/seed-content)}';

    protected $description = 'Pack categories, posters and mantras with their media for content:import';

    public function handle(): int
    {
        $dir = $this->option('dir') ?: database_path('seed-content');
        File::deleteDirectory($dir);
        File::ensureDirectoryExists($dir.'/media');

        $copy = function (?string $path) use ($dir): ?string {
            if (! $path || ! Media::disk()->exists($path)) {
                return null;
            }
            File::ensureDirectoryExists(dirname($dir.'/media/'.$path));
            File::put($dir.'/media/'.$path, Media::disk()->get($path));

            return $path;
        };

        $categories = Category::query()->whereIn('type', ['poster', 'mantra'])->orderBy('id')->get();
        $data = [
            'categories' => $categories->map(fn (Category $c) => [
                'key' => $c->id,
                'type' => $c->type,
                'name' => $c->name,
                'description' => $c->description,
                'icon_path' => $copy($c->icon_path),
                'sort_order' => $c->sort_order,
                'is_active' => $c->is_active,
            ])->values(),
            'posters' => Poster::query()->orderBy('id')->get()->map(fn (Poster $p) => [
                'category' => $p->category_id,
                'title' => $p->title,
                'image_path' => $copy($p->image_path),
                'frame' => $p->frame,
                'footer_below' => $p->footer_below,
                'show_date' => $p->show_date,
                'date_position' => $p->date_position,
                'date_on' => $p->date_on?->format('Y-m-d'),
                'weekdays' => $p->weekdays,
                'show_from' => $p->show_from?->format('Y-m-d'),
                'sort_order' => $p->sort_order,
                'is_active' => $p->is_active,
            ])->filter(fn ($p) => $p['image_path'] !== null)->values(),
            'mantras' => Mantra::query()->orderBy('id')->get()->map(fn (Mantra $m) => [
                'category' => $m->category_id,
                'title' => $m->title,
                'deity' => $m->deity,
                'lyrics' => $m->lyrics,
                'meaning' => $m->meaning,
                'image_path' => $copy($m->image_path),
                'audio_path' => $copy($m->audio_path),
                'duration_seconds' => $m->duration_seconds,
                'sort_order' => $m->sort_order,
                'is_active' => $m->is_active,
            ])->values(),
        ];

        File::put($dir.'/content.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->info(sprintf(
            'Exported %d categories, %d posters, %d mantras to %s',
            count($data['categories']), count($data['posters']), count($data['mantras']), $dir,
        ));

        return self::SUCCESS;
    }
}
