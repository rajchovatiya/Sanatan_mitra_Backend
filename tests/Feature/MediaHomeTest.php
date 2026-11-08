<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Festival;
use App\Models\LearningVideo;
use App\Models\Rashifal;
use App\Models\StudioMedia;
use App\Models\Suvichar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaHomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_wallpapers_videos_live_and_courses(): void
    {
        $wall = Category::create(['type' => 'wallpaper', 'name' => ['en' => 'Shiva']]);
        StudioMedia::create(['kind' => 'wallpaper', 'category_id' => $wall->id, 'title' => ['en' => 'W1'], 'image_path' => 'studio-wallpaper/a.jpg']);
        $course = Category::create(['type' => 'course', 'name' => ['en' => 'Sanskrit basics']]);
        LearningVideo::create(['kind' => 'lesson', 'category_id' => $course->id, 'title' => ['en' => 'Lesson 1'], 'video_url' => 'https://youtu.be/a']);
        LearningVideo::create(['kind' => 'live', 'title' => ['en' => 'Somnath Aarti'], 'video_url' => 'https://youtu.be/live']);

        $this->getJson('/api/v1/wallpapers/categories')->assertJsonPath('data.0.name', 'Shiva');
        $this->getJson("/api/v1/wallpapers?category={$wall->id}")->assertJsonCount(1, 'data')->assertJsonPath('data.0.video_url', null);
        $this->getJson('/api/v1/videos')->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/live')->assertJsonPath('data.0.title', 'Somnath Aarti');
        $this->getJson('/api/v1/courses')->assertJsonPath('data.0.name', 'Sanskrit basics');
        $this->getJson("/api/v1/courses/{$course->id}")->assertJsonPath('data.lessons.0.title', 'Lesson 1');
        $this->getJson("/api/v1/courses/{$wall->id}")->assertNotFound();
    }

    public function test_home_returns_every_section_in_one_call(): void
    {
        Rashifal::create(['date' => today(), 'rashi' => 2, 'overall' => ['en' => 'Good']]);
        Festival::create(['name' => ['en' => 'Diwali'], 'date' => today()->addDays(2)]);
        Suvichar::create(['text' => ['en' => 'Seva']]);

        $this->getJson('/api/v1/home')
            ->assertOk()
            ->assertJsonStructure(['data' => ['rashifal', 'festivals', 'suvichar', 'gita_verse', 'darshan', 'mantras', 'temples']])
            ->assertJsonPath('data.rashifal.0.rashi', 2)
            ->assertJsonPath('data.festivals.0.name', 'Diwali')
            ->assertJsonPath('data.suvichar.text', 'Seva')
            ->assertJsonPath('data.darshan', null);
    }

    public function test_admin_uploads_wallpaper_video_and_lessons(): void
    {
        Storage::fake('public');
        $this->actingAs(Admin::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'secret-pass-1']), 'admin');
        $wall = Category::create(['type' => 'wallpaper', 'name' => ['en' => 'Shiva']]);
        $video = Category::create(['type' => 'video', 'name' => ['en' => 'Festival']]);
        $course = Category::create(['type' => 'course', 'name' => ['en' => 'Gita']]);

        // A wallpaper cannot go into a video category.
        $this->post('/admin/studio', ['kind' => 'wallpaper', 'category_id' => $video->id, 'title' => ['en' => 'X'],
            'image' => UploadedFile::fake()->image('w.jpg')])->assertSessionHasErrors('category_id');
        $this->post('/admin/studio', ['kind' => 'wallpaper', 'category_id' => $wall->id, 'title' => ['en' => 'X'], 'is_active' => '1',
            'image' => UploadedFile::fake()->image('w.jpg')])->assertSessionHasNoErrors();
        $this->post('/admin/studio', ['kind' => 'video', 'category_id' => $video->id, 'title' => ['en' => 'V'],
            'image' => UploadedFile::fake()->image('t.jpg')])->assertSessionHasErrors('video');
        $this->post('/admin/studio', ['kind' => 'video', 'category_id' => $video->id, 'title' => ['en' => 'V'], 'is_active' => '1',
            'image' => UploadedFile::fake()->image('t.jpg'), 'video' => UploadedFile::fake()->create('v.mp4', 900, 'video/mp4')])
            ->assertSessionHasNoErrors();

        $this->post('/admin/learning', ['kind' => 'lesson', 'title' => ['en' => 'L1'], 'video_url' => 'https://youtu.be/x'])
            ->assertSessionHasErrors('category_id');
        $this->post('/admin/learning', ['kind' => 'lesson', 'category_id' => $course->id, 'title' => ['en' => 'L1'], 'video_url' => 'https://youtu.be/x', 'is_active' => '1'])
            ->assertSessionHasNoErrors();
        $this->delete("/admin/categories/{$course->id}")->assertSessionHasErrors('category');

        foreach (['/admin/studio', '/admin/studio?kind=video', '/admin/studio/create?kind=video', '/admin/studio/'.StudioMedia::first()->id.'/edit',
            '/admin/learning', '/admin/learning?kind=lesson', '/admin/learning/create?kind=lesson', '/admin/learning/'.LearningVideo::first()->id.'/edit',
            '/admin/categories'] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
