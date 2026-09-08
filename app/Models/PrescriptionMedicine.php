<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// هان هاد الموديل عبارة عن موديل يحتوي على رقم الوصفة لكن فيها الادوية والجرعة والمدة هيك افضل من انو نحطهم كلهم ف جدول واحد
// عشان الاستدعاءات وهيلزمونا يعني
class PrescriptionMedicine extends Model
{
    protected $fillable = ['prescription_id', 'medicine_name', 'dosage', 'duration'];

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }
}
