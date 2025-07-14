<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'unique_key',
        'product_title',
        'product_description',
        'style_number',
        'sanmar_mainframe_color',
        'size',
        'color_name',
        'piece_price',
        'file_upload_id',
    ];

    protected $casts = [
        'piece_price' => 'decimal:2',
    ];

    /**
     * Get the file upload that owns the product.
     */
    public function fileUpload(): BelongsTo
    {
        return $this->belongsTo(FileUpload::class);
    }
}
