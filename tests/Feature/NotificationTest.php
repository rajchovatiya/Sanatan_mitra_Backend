<?php

namespace Tests\Feature;

use App\Jobs\SendAppNotification;
use App\Models\Admin;
use App\Models\AppNotification;
use App\Models\User;
use App\Services\Push\FcmPushSender;
use App\Services\Push\PushSender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sends_notification_in_the_background(): void
    {
        Queue::fake();
        $this->actingAs(Admin::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'secret-pass-1']), 'admin');

        $this->post('/admin/notifications', ['title' => ['en' => 'Diwali'], 'body' => ['en' => 'Happy Diwali'], 'link' => 'javascript:alert(1)'])
            ->assertSessionHasErrors('link');
        $this->post('/admin/notifications', ['title' => ['en' => 'Diwali'], 'body' => ['en' => 'Happy Diwali'], 'link' => '/festivals/5'])
            ->assertSessionHasNoErrors();

        Queue::assertPushed(SendAppNotification::class);
        $this->get('/admin/notifications')->assertOk()->assertSee('Sending');
        $this->get('/admin/notifications/create')->assertOk();
    }

    public function test_job_sends_one_message_per_language_and_marks_sent(): void
    {
        $sent = [];
        $this->app->instance(PushSender::class, new class($sent) implements PushSender
        {
            public function __construct(private array &$sent) {}

            public function sendToTopic(string $topic, array $message): void
            {
                $this->sent[$topic] = $message['title'];
            }
        });

        $n = AppNotification::create(['title' => ['en' => 'Hello', 'hi' => 'नमस्ते'], 'body' => ['en' => 'Body']]);
        SendAppNotification::dispatchSync($n);

        $this->assertSame(['all_en' => 'Hello', 'all_hi' => 'नमस्ते', 'all_gu' => 'Hello'], $sent);
        $this->assertNotNull($n->fresh()->sent_at);
    }

    public function test_user_sees_sent_notifications_and_unread_count(): void
    {
        $user = Sanctum::actingAs(User::factory()->create());
        AppNotification::create(['title' => ['en' => 'Draft'], 'body' => ['en' => 'x']]);
        $old = AppNotification::create(['title' => ['en' => 'Old'], 'body' => ['en' => 'x']]);
        $old->forceFill(['sent_at' => now()->subDay()])->save();

        $this->getJson('/api/v1/notifications')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('unread_count', 1)->assertJsonPath('data.0.unread', true);

        $this->postJson('/api/v1/notifications/read')->assertNoContent();
        $this->travel(1)->minutes();
        $new = AppNotification::create(['title' => ['en' => 'New'], 'body' => ['en' => 'x']]);
        $new->forceFill(['sent_at' => now()])->save();

        $this->getJson('/api/v1/notifications')->assertJsonPath('unread_count', 1)
            ->assertJsonPath('data.0.title', 'New')->assertJsonPath('data.1.unread', false);
    }

    public function test_fcm_sender_signs_token_and_posts_to_topic(): void
    {
        // On Windows, OpenSSL needs its config file to create keys (e.g. Herd ships one).
        $options = ['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
        $config = collect([getenv('OPENSSL_CONF'), getenv('USERPROFILE').'/.config/herd/openssl.cnf'])->first(fn ($p) => $p && is_file($p));
        if ($config) {
            $options['config'] = $config;
        }
        $key = openssl_pkey_new($options);
        if (! $key) {
            $this->markTestSkipped('OpenSSL cannot create keys on this machine.');
        }
        openssl_pkey_export($key, $pem, null, $options);
        $path = tempnam(sys_get_temp_dir(), 'fcm');
        file_put_contents($path, json_encode(['project_id' => 'demo', 'client_email' => 'bot@demo.iam', 'private_key' => $pem]));

        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'tok']),
            'fcm.googleapis.com/*' => Http::response(['name' => 'ok']),
        ]);

        (new FcmPushSender($path))->sendToTopic('all_hi', ['title' => 'T', 'body' => 'B', 'image' => null, 'data' => ['link' => '/x']]);
        unlink($path);

        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'projects/demo/messages:send')
            && $r->hasHeader('Authorization', 'Bearer tok')
            && $r['message']['topic'] === 'all_hi'
            && $r['message']['notification'] === ['title' => 'T', 'body' => 'B']);
    }
}
