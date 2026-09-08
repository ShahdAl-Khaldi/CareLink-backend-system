<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescription extends Model
{
    protected $fillable = ['appointment_id', 'diagnosis', 'notes', 'status',
        'dispensed_at', 'dispensed_by', 'dispense_notes', 'verify_method',
        'allergy_warning', 'allergy_overridden'];

    // الوصفة تتبع موعد واحد
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    // مين صرف الوصفة (صيدلي)
    public function dispensedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    // الوصفة الواحدة تحتوي على عدة أدوية
    public function medicines(): HasMany
    {
        return $this->hasMany(PrescriptionMedicine::class);
    }

    // طلبات تجديد هاي الوصفة (FR-06.11)
    public function refillRequests(): HasMany
    {
        return $this->hasMany(PrescriptionRefillRequest::class);
    }
}
