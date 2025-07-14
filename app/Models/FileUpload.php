<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FileUpload extends Model
{
    protected $fillable = [
        'filename',
        'original_name',
        'file_size',
        'status',
        'total_records',
        'processed_records',
        'failed_records',
        'error_message',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    /**
     * Get the products for the file upload.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the progress percentage.
     */
    public function getProgressPercentageAttribute(): int
    {
        if (!$this->total_records || $this->total_records === 0) {
            return 0;
        }
        
        return (int) round(($this->processed_records / $this->total_records) * 100);
    }
}
