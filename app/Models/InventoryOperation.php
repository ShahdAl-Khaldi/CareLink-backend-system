<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryOperation extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_id',
        'item_name',
        'type',
        'delta',
        'actor_name',
        'notes',
    ];

    protected $casts = [
        'delta' => 'integer',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }
}
