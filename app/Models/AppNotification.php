<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['title', 'body', 'image_path', 'link', 'admin_id'])]
class AppNotification extends Model
{
    use HasTranslations;

    protected function casts(): array
    {
        return ['title' => 'array', 'body' => 'array', 'sent_at' => 'datetime'];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /** Push payload in one language (falls back to English). */
    public function message(string $lang): array
    {
        return [
            'title' => ($this->title[$lang] ?? null) ?: $this->title['en'],
            'body' => ($this->body[$lang] ?? null) ?: $this->body['en'],
            'image' => Media::url($this->image_path),
            'data' => ['notification_id' => (string) $this->id, 'link' => (string) $this->link],
        ];
    }
}
