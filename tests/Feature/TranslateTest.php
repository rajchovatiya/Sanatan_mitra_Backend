<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AdminAuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TranslateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(Admin::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'secret-pass-1']), 'admin');
    }

    public function test_without_a_service_the_admin_gets_a_clear_message(): void
    {
        $this->postJson('/admin/translate', ['texts' => ['title' => 'Diwali']])
            ->assertStatus(503)
            ->assertJsonPath('message', 'Translation is not set up yet. Add a translation service key in .env.');
    }

    public function test_google_fills_hindi_and_gujarati_and_is_not_audited(): void
    {
        config(['sanatan.translate.driver' => 'google', 'sanatan.translate.google_key' => 'k']);
        Http::fake(fn (Request $r) => Http::response(['data' => ['translations' => [
            ['translatedText' => $r['target'] === 'hi' ? 'दीवाली' : 'દિવાળી'],
            ['translatedText' => $r['target'] === 'hi' ? 'रोशनी का त्योहार' : 'પ્રકાશનો તહેવાર'],
        ]]]));

        $this->postJson('/admin/translate', ['texts' => ['title' => 'Diwali', 'about' => 'Festival of lights']])
            ->assertOk()
            ->assertJsonPath('translations.hi.title', 'दीवाली')
            ->assertJsonPath('translations.gu.about', 'પ્રકાશનો તહેવાર');

        Http::assertSent(fn (Request $r) => $r['source'] === 'en' && $r['q'] === ['Diwali', 'Festival of lights']);
        $this->assertSame(0, AdminAuditLog::count());
    }

    public function test_google_failure_gives_a_friendly_message(): void
    {
        config(['sanatan.translate.driver' => 'google', 'sanatan.translate.google_key' => 'bad']);
        Http::fake(['*' => Http::response(['error' => 'invalid key'], 400)]);

        $this->postJson('/admin/translate', ['texts' => ['title' => 'Diwali']])
            ->assertStatus(503)->assertJsonPath('message', 'The translation service did not answer. Please try again.');
    }

    public function test_forms_load_the_button_script_and_skip_sanskrit_lyrics(): void
    {
        $this->postJson('/admin/translate', ['texts' => []])->assertUnprocessable();

        $this->get('/admin/mantras/create')
            ->assertOk()
            ->assertSee('assets/js/admin-translate.js')
            ->assertSee('data-field="lyrics" data-translate="off"', false)
            ->assertSee('data-field="title" data-translate="on"', false);
    }
}
