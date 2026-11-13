<?php

namespace App\Console\Commands;

use App\Models\Place;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use ZipArchive;

/**
 * Loads the free GeoNames lists into the places table:
 * every populated place in India (cities, towns, villages) and world cities over 15,000 people.
 * Safe to run again: rows are updated in place. Data: https://www.geonames.org (CC BY 4.0).
 */
class ImportPlaces extends Command
{
    protected $signature = 'places:import {--country=IN : Country lists to load in full (comma separated)} {--no-world : Skip the world cities list} {--dir= : Folder that already holds the downloaded files}';

    protected $description = 'Load birth places (cities, towns, villages) from GeoNames';

    private const BASE = 'https://download.geonames.org/export/dump/';

    public function handle(): int
    {
        $dir = $this->option('dir') ?: storage_path('app/geonames');
        @mkdir($dir, 0775, true);

        $admin1 = $this->codes($this->fetch($dir, 'admin1CodesASCII.txt'));
        $admin2 = $this->codes($this->fetch($dir, 'admin2Codes.txt'));

        $lists = array_filter(array_map('trim', explode(',', strtoupper((string) $this->option('country')))));
        $files = array_map(fn ($c) => "$c.zip", $lists);
        if (! $this->option('no-world')) {
            $files[] = 'cities15000.zip';
        }

        $total = 0;
        foreach ($files as $zip) {
            $txt = $this->unzip($this->fetch($dir, $zip), $dir);
            $count = $this->load($txt, $admin1, $admin2);
            $this->info("$zip: $count places");
            $total += $count;
        }

        $this->info("Done. $total rows loaded; table now has ".Place::count().' places.');

        return self::SUCCESS;
    }

    private function fetch(string $dir, string $name): string
    {
        $path = $dir.DIRECTORY_SEPARATOR.$name;
        if (! is_file($path)) {
            $this->line("Downloading $name ...");
            Http::timeout(600)->sink($path)->get(self::BASE.$name)->throw();
        }

        return $path;
    }

    private function unzip(string $zipPath, string $dir): string
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException("Cannot open $zipPath");
        }
        $name = basename($zipPath, '.zip').'.txt';
        $zip->extractTo($dir, $name);
        $zip->close();

        return $dir.DIRECTORY_SEPARATOR.$name;
    }

    /** code => name from admin1/admin2 lists ("IN.09\tGujarat\t..."). */
    private function codes(string $path): array
    {
        $out = [];
        foreach (file($path, FILE_IGNORE_NEW_LINES) as $line) {
            $p = explode("\t", $line);
            if (count($p) >= 2) {
                $out[$p[0]] = ($p[2] ?? '') !== '' ? $p[2] : $p[1]; // plain ASCII spelling
            }
        }

        return $out;
    }

    private function load(string $txt, array $admin1, array $admin2): int
    {
        $handle = fopen($txt, 'r');
        $batch = [];
        $count = 0;

        while (($line = fgets($handle)) !== false) {
            $p = explode("\t", rtrim($line, "\r\n"));
            // Only populated places (feature class P), skipping abandoned/destroyed ones.
            if (count($p) < 18 || $p[6] !== 'P' || in_array($p[7], ['PPLH', 'PPLQ', 'PPLW'], true)) {
                continue;
            }
            $search = Place::searchKey($p[2] !== '' ? $p[2] : $p[1]);
            if ($search === '' || $p[17] === '') {
                continue;
            }
            $batch[] = [
                'id' => (int) $p[0],
                'name' => mb_substr($p[2] !== '' ? $p[2] : $p[1], 0, 120), // "Junagadh", not "Jūnāgadh"
                'search_name' => mb_substr($search, 0, 120),
                'district' => $admin2["$p[8].$p[10].$p[11]"] ?? null,
                'state' => $admin1["$p[8].$p[10]"] ?? null,
                'country_code' => $p[8],
                'lat' => round((float) $p[4], 6),
                'lng' => round((float) $p[5], 6),
                'timezone' => $p[17],
                'population' => min((int) $p[14], 4294967295),
            ];
            if (count($batch) === 500) {
                $count += $this->save($batch);
                $batch = [];
            }
        }
        fclose($handle);

        return $count + $this->save($batch);
    }

    private function save(array $rows): int
    {
        if ($rows) {
            DB::table('places')->upsert($rows, ['id'], ['name', 'search_name', 'district', 'state', 'country_code', 'lat', 'lng', 'timezone', 'population']);
        }

        return count($rows);
    }
}
