<?php

namespace Modules\Operations\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Operations\Services\DocumentCatalog;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'documentable_type', 'documentable_id', 'type', 'stage', 'sensitive', 'original_name',
        'path', 'mime', 'size', 'notes', 'uploaded_by',
    ];

    protected $casts = [
        'sensitive' => 'boolean',
        'size' => 'integer',
    ];

    protected $hidden = ['path', 'documentable_type'];

    protected $appends = ['type_label', 'url', 'is_image'];

    public function documentable()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return DocumentCatalog::label($this->type);
    }

    public function getUrlAttribute(): string
    {
        return route('documents.show', $this);
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }

    /** Price-bearing papers are only for people who can see money. */
    public function visibleTo(?User $user): bool
    {
        return $user !== null && (!$this->sensitive || $user->can('money.view'));
    }
}
