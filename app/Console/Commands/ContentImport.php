<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Mantra;
use App\Models\Poster;
use App\Support\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Loads what content:export packed. Safe to run on every start: a category
 * with the same type and English name, a poster with the same image and a
 * mantra with the same audio (or title in the same category) are reused, and
 * missing media files are copied back in.
 */
class ContentImport extends Command
{
    protected $signature = 'content:import {--dir= : Folder made by content:export (default database/seed-content)}';

    protected $description = 'Load categories, posters and mantras packed by content:export';

    public function handle(): int
    {
        $dir = $this->option('dir') ?: database_path('seed-content');
        if (! File::exists($dir.'/content.json')) {
            $this->line('No content to import.');

            return self::SUCCESS;
        }

        $data = json_decode(File::get($dir.'/content.json'), true);
        $restore = function (?string $path) use ($dir): ?string {
            if ($path && ! Media::disk()->exists($path) && File::exists($dir.'/media/'.$path)) {
                Media::disk()->put($path, File::get($dir.'/media/'.$path));
            }

            return $path;
        };

        $categoryIds = [];
        $added = ['categories' => 0, 'posters' => 0, 'mantras' => 0];

        foreach ($data['categories'] ?? [] as $c) {
            $name = $c['name']['en'] ?? null;
            $existing = Category::query()->where('type', $c['type'])->get()
                ->first(fn (Category $x) => ($x->name['en'] ?? null) === $name);
            if (! $existing) {
                $existing = Category::create([
                    'type' => $c['type'],
                    'name' => $c['name'],
                    'description' => $c['description'],
                    'icon_path' => $restore($c['icon_path']),
                    'sort_order' => $c['sort_order'],
                    'is_active' => $c['is_active'],
                ]);
                $added['categories']++;
            } else {
                $restore($c['icon_path']);
            }
            $categoryIds[$c['key']] = $existing->id;
        }

        foreach ($data['posters'] ?? [] as $p) {
            $restore($p['image_path']);
            if (! isset($categoryIds[$p['category']]) || Poster::where('image_path', $p['image_path'])->exists()) {
                continue;
            }
            Poster::create([
                'category_id' => $categoryIds[$p['category']],
                'title' => $p['title'],
                'image_path' => $p['image_path'],
                'frame' => $p['frame'],
                'show_from' => $p['show_from'],
                'sort_order' => $p['sort_order'],
                'is_active' => $p['is_active'],
            ]);
            $added['posters']++;
        }

        foreach ($data['mantras'] ?? [] as $m) {
            $restore($m['image_path']);
            $restore($m['audio_path']);
            $categoryId = $categoryIds[$m['category']] ?? null;
            if (! $categoryId) {
                continue;
            }
            $exists = $m['audio_path']
                ? Mantra::where('audio_path', $m['audio_path'])->exists()
                : Mantra::where('category_id', $categoryId)->get()
                    ->contains(fn (Mantra $x) => ($x->title['en'] ?? null) === ($m['title']['en'] ?? null));
            if ($exists) {
                continue;
            }
            Mantra::create([
                'category_id' => $categoryId,
                'title' => $m['title'],
                'deity' => $m['deity'],
                'lyrics' => $m['lyrics'],
                'meaning' => $m['meaning'],
                'image_path' => $m['image_path'],
                'audio_path' => $m['audio_path'],
                'duration_seconds' => $m['duration_seconds'],
                'sort_order' => $m['sort_order'],
                'is_active' => $m['is_active'],
            ]);
            $added['mantras']++;
        }

        $this->info(sprintf(
            'Imported %d categories, %d posters, %d mantras (existing ones kept).',
            $added['categories'], $added['posters'], $added['mantras'],
        ));

        return self::SUCCESS;
    }
}
