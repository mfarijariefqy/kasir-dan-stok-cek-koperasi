<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'barcode', 'name', 'category_id', 'unit',
        'buy_price', 'sell_price', 'stock_qty', 'branch_id', 'is_active',
    ];

    protected $casts = [
        'buy_price'  => 'decimal:2',
        'sell_price' => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function productLogs(): HasMany
    {
        return $this->hasMany(ProductLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
