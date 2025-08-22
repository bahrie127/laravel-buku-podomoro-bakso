<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    /** @use HasFactory<\Database\Factories\AttachmentFactory> */
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'path',
        'original_name',
        'size',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function getUrl(): string
    {
        return Storage::url($this->path);
    }

    public function getSizeInKb(): float
    {
        return round($this->size / 1024, 2);
    }

    public function getSizeInMb(): float
    {
        return round($this->size / (1024 * 1024), 2);
    }

    public function getHumanReadableSize(): string
    {
        if ($this->size < 1024) {
            return $this->size . ' B';
        } elseif ($this->size < 1024 * 1024) {
            return $this->getSizeInKb() . ' KB';
        } else {
            return $this->getSizeInMb() . ' MB';
        }
    }
}
