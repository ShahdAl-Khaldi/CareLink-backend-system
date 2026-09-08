<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_id',
        'batch_number',
        'quantity',
        'expiry_date',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'quantity' => 'integer',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }
}
