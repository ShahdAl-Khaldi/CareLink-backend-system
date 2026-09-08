<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'company',
        'quantity',
        'min_quantity',
        'unit',
        'price',
        'expiry_date',
        'keywords',
        'updated_by',
    ];

    protected $casts = [
        'keywords' => 'array',
        'expiry_date' => 'date',
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'min_quantity' => 'integer',
    ];

    public function batches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(InventoryOperation::class);
    }

    /**
     * إعادة حساب الكمية الإجمالية وأقرب تاريخ صلاحية بناءً على الدفعات الحالية.
     * تُستدعى بعد أي إضافة/تعديل/حذف لدفعة.
     */
    public function recalculateFromBatches(): void
    {
        $this->quantity = (int) $this->batches()->sum('quantity');
        $this->expiry_date = $this->batches()->min('expiry_date');
        $this->save();
    }
}
