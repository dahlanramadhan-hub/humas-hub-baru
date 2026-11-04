<?php

namespace App\Models;

use App\Support\Uploads;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;

class Content extends Model
{
    public const STATUSES = ['Belum Dikerjakan', 'Menunggu Review', 'Revisi', 'Approved', 'Publish Terjadwal', 'Publish'];
    public const FINISHED = ['Approved', 'Publish Terjadwal', 'Publish'];
    public const BEFORE_APPROVAL = ['Belum Dikerjakan', 'Menunggu Review', 'Revisi'];

    public const TYPES = ['Grafis', 'Video', 'Carousel', 'Foto', 'Reels', 'Story'];
    public const SOCIALS = ['TikTok', 'Instagram', 'Twitter'];
    public const DEADLINE_NOTES = ['Same Day', 'Besok', 'Dalam Minggu Ini', 'Tanggal Tertentu'];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'channels'   => 'array',
            'deadline'   => 'date',
            'publish_at' => 'datetime',
        ];
    }

    /* ---------- relasi ---------- */
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requester_id'); }
    public function editor(): BelongsTo    { return $this->belongsTo(User::class, 'editor_id'); }
    public function assets(): HasMany      { return $this->hasMany(ContentAsset::class); }
    public function materials(): HasMany   { return $this->assets()->where('category', 'material')->orderBy('id'); }
    public function references(): HasMany  { return $this->assets()->where('category', 'reference')->orderBy('id'); }
    public function drafts(): HasMany      { return $this->hasMany(Draft::class)->orderBy('id'); }
    public function finalDraft(): BelongsTo { return $this->belongsTo(Draft::class, 'final_draft_id'); }

    /* ---------- siapa boleh melihat apa ---------- */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return match (true) {
            $user->isSuperAdmin() => $query,
            $user->isPengaju()    => $query->where('requester_id', $user->id),
            $user->isEditor()     => $query->where('editor_id', $user->id),
            $user->isPublisher()  => $query->whereIn('status', self::FINISHED),
            default               => $query->whereRaw('1 = 0'),
        };
    }

    public function isFinished(): bool       { return in_array($this->status, self::FINISHED, true); }
    public function isBeforeApproval(): bool { return in_array($this->status, self::BEFORE_APPROVAL, true); }

    /** Pihak yang sedang ditunggu. */
    public function turnLabel(): string
    {
        return match ($this->status) {
            'Belum Dikerjakan', 'Revisi'      => 'Editor',
            'Menunggu Review'                 => 'Pengaju',
            'Approved', 'Publish Terjadwal'   => 'Publisher',
            default                           => 'Selesai',
        };
    }

    /* ---------- tambah bahan / referensi ---------- */
    public function addFile(string $category, UploadedFile $file): ContentAsset
    {
        return $this->assets()->create(
            ['category' => $category, 'kind' => 'file'] + Uploads::put($file, "contents/{$this->id}/{$category}")
        );
    }

    public function addText(string $category, string $text): ContentAsset
    {
        return $this->assets()->create([
            'category'   => $category,
            'kind'       => 'text',
            'name'       => $category === 'material' ? 'Bahan / Footage (Teks)' : 'Referensi (Teks)',
            'text'       => $text,
            'size_label' => 'Teks',
        ]);
    }

    public function addLink(string $category, string $url): ContentAsset
    {
        return $this->assets()->create([
            'category' => $category, 'kind' => 'link',
            'name' => $url, 'text' => $url, 'size_label' => 'Link',
        ]);
    }

    public function lastDraft(): ?Draft
    {
        $this->loadMissing('drafts.comments');
        return $this->drafts->last();
    }

    public function badgeClass(): string
    {
        return match ($this->status) {
            'Approved', 'Publish' => 'b-green',
            'Revisi'              => 'b-yellow',
            'Menunggu Review'     => 'b-blue',
            'Publish Terjadwal'   => 'b-purple',
            default               => 'b-gray',
        };
    }

    public static function initials(?string $name): string
    {
        $parts = array_filter(preg_split('/\s+/', trim((string) $name)));
        $init  = collect($parts)->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('');
        return mb_strtoupper($init) ?: '?';
    }
}