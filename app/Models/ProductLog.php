<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductLog extends Model
{
    protected $fillable = [
        'product_id', 'user_id', 'type', 'qty', 'note', 'logged_at',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
