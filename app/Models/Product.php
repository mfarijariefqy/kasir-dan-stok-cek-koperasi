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

    /**
     * Reconstruct stock as of the end of $dateTo by undoing movements logged after it.
     * stock_qty always reflects "now", so historical ranges must back it out.
     */
    public function stockAt(string $dateTo): int
    {
        $netAfter = $this->productLogs()
            ->where('logged_at', '>', $dateTo . ' 23:59:59')
            ->selectRaw("SUM(CASE WHEN type = 'IN' THEN qty ELSE -qty END) as net")
            ->value('net');

        return $this->stock_qty - (int) ($netAfter ?? 0);
    }
}
