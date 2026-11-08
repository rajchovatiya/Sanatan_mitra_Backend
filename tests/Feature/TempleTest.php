<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Darshan;
use App\Models\Temple;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TempleTest extends TestCase
{
    use RefreshDatabase;

    public function test_temple_list_filters_and_detail(): void
    {
        $somnath = Temple::create([
            'type' => 'jyotirlinga', 'name' => ['en' => 'Somnath', 'hi' => 'सोमनाथ'], 'state' => 'Gujarat',
            'about' => ['en' => 'First Jyotirlinga'], 'lat' => 20.888, 'lng' => 70.401,
        ]);
        Temple::create(['type' => 'shakti_peeth', 'name' => ['en' => 'Kamakhya'], 'state' => 'Assam']);
        Temple::create(['type' => 'jyotirlinga', 'name' => ['en' => 'Hidden'], 'is_active' => false]);

        $this->getJson('/api/v1/temples')->assertOk()->assertJsonCount(2, 'data')->assertJsonMissingPath('data.0.about');
        $this->getJson('/api/v1/temples?type=jyotirlinga&state=Gujarat')->assertJsonCount(1, 'data');
        $this->getJson("/api/v1/temples/{$somnath->id}", ['Accept-Language' => 'hi'])
            ->assertJsonPath('data.name', 'सोमनाथ')
            ->assertJsonPath('data.about', 'First Jyotirlinga')
            ->assertJsonPath('data.lat', 20.888);
    }

    public function test_darshan_today_is_latest_up_to_today_or_null(): void
    {
        $this->getJson('/api/v1/darshan/today')->assertOk()->assertExactJson(['data' => null]);

        Darshan::create(['date' => today()->subDay(), 'title' => ['en' => 'Yesterday'], 'image_path' => 'darshans/a.jpg']);
        Darshan::create(['date' => today(), 'title' => ['en' => 'Today'], 'image_path' => 'darshans/b.jpg']);
        Darshan::create(['date' => today()->addDay(), 'title' => ['en' => 'Tomorrow'], 'image_path' => 'darshans/c.jpg']);

        $this->getJson('/api/v1/darshan/today')->assertJsonPath('data.title', 'Today');
    }

    public function test_admin_manages_temples_and_darshan(): void
    {
        Storage::fake('public');
        $this->actingAs(Admin::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'secret-pass-1']), 'admin');

        $this->post('/admin/temples', [
            'type' => 'jyotirlinga', 'name' => ['en' => 'Somnath'], 'state' => 'Gujarat', 'is_active' => '1',
            'image' => UploadedFile::fake()->image('s.jpg'),
        ])->assertSessionHasNoErrors();
        $temple = Temple::first();

        $this->post('/admin/darshans', [
            'date' => today()->toDateString(), 'temple_id' => $temple->id, 'title' => ['en' => 'Shringar'],
            'image' => UploadedFile::fake()->image('d.jpg'), 'video_url' => 'http://insecure.example', 'is_active' => '1',
        ])->assertSessionHasErrors('video_url');

        $this->post('/admin/darshans', [
            'date' => today()->toDateString(), 'temple_id' => $temple->id, 'title' => ['en' => 'Shringar'],
            'image' => UploadedFile::fake()->image('d.jpg'), 'video_url' => 'https://youtube.com/live/x', 'is_active' => '1',
        ])->assertSessionHasNoErrors();

        foreach (['/admin/temples', '/admin/temples/create', "/admin/temples/{$temple->id}/edit",
            '/admin/darshans', '/admin/darshans/create', '/admin/darshans/'.Darshan::first()->id.'/edit'] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
