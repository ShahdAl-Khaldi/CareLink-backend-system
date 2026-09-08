<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;  
use Illuminate\Database\Eloquent\Relations\HasMany;
class Patient extends Model
{
    protected $guarded = [];

    // $fillable غير الفاضي بيلغي أثر $guarded=[] بلارافيل (بيصير allowlist صارم)،
    // فأي عمود مش مذكور هون بينرفض بصمت من mass assignment - لهيك ناقص guardian_id
    // وحقول التأمين/التنبيهات كانت بتترفض بصمت من update() بدون أي خطأ ظاهر
    protected $fillable = [
        'full_name',
        'phone',
        'national_id',
        'birth_date',
        'address',
        'guardian_id',
        'insurance_status',
        'insurance_provider',
        'reception_flags',
        'reception_note',
        'user_id',
    ];

    protected $casts = [
        'reception_flags' => 'array',
    ];

    // العلاقة مع الوصي أو ولي الأمر
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'guardian_id');
    }

    // العلاقة مع التابعين (الأبناء أو التابعين لهذا المريض)
    public function dependents(): HasMany
    {
        return $this->hasMany(Patient::class, 'guardian_id');
    }

    // حساب الويب المرتبط بهذا المريض (لو تم إنشاؤه من الاستقبال بزر
    // "إنشاء حساب ويب") - غير موجود لمعظم مرضى الاستقبال العاديين
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
