<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Customer;

class Transaction extends Model
{
    protected $fillable = [
        'trx_no', 'trx_date', 'user_id', 'branch_id', 'customer_id', 'customer_name',
        'payment_method', 'payment_status', 'paid_at', 'total',
    ];

    protected $casts = [
        'trx_date' => 'date',
        'paid_at'  => 'datetime',
        'total'    => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function isBelumLunas(): bool
    {
        return $this->payment_status === 'Belum Lunas';
    }
}
