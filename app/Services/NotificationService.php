<?php

namespace App\Services;

use App\Mail\GeneralNotificationMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    // NFR-03.5: النظام لازم يضل شغال حتى لو جزء منه (خدمة الإيميل الخارجية)
    // وقع. كانت Mail::send() بكل الدوال هون بدون أي try/catch، فأي عطل
    // بسيرفر SMTP كان بيطيح الـ request كله - حتى لما يكون الإجراء الأساسي
    // (موافقة على طبيب، مثلاً) خلص ونفّذ فعلياً بقاعدة البيانات قبل الإيميل
    public static function sendToAll($title, $body)
    {
        $failed = 0;

        User::chunk(100, function ($users) use ($title, $body, &$failed) {
            foreach ($users as $user) {
                try {
                    Mail::to($user->email)->send(new GeneralNotificationMail(
                        $title,
                        'emails.general',
                        ['body' => $body]
                    ));
                } catch (\Throwable $e) {
                    $failed++;
                    Log::warning('Broadcast email failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
                }
            }
        });

        return $failed === 0;
    }

    public static function sendToUser($userId, $modelClass, $title, $body)
    {
        $user = User::find($userId);
        if (! $user) {
            return false;
        }

        try {
            Mail::to($user->email)->send(new GeneralNotificationMail(
                $title,
                'emails.general',
                ['body' => $body]
            ));

            return true;
        } catch (\Throwable $e) {
            Log::warning('Email to user failed', ['user_id' => $userId, 'error' => $e->getMessage()]);

            return false;
        }
    }

    // الدالة الأصلية لإشعارات النظام - بترجع true/false بدل ما ترمي استثناء،
    // عشان المستدعي (زي approveDoctor) يقدر يكمل عمله الأساسي بغض النظر عن
    // نجاح الإيميل، أو (زي forgotPassword) يتحقق من القيمة الراجعة ويعطي
    // المستخدم رسالة صحيحة إذا فعلاً ما انبعت
    public static function send($type, User $user, $data = [])
    {
        $subject = self::getSubject($type);
        $template = self::getTemplate($type);

        try {
            Mail::to($user->email)->send(new GeneralNotificationMail($subject, $template, $data));

            return true;
        } catch (\Throwable $e) {
            Log::warning('Notification email failed', ['type' => $type, 'user_id' => $user->id, 'error' => $e->getMessage()]);

            return false;
        }
    }

    private static function getSubject($type)
    {
        return match ($type) {
            'doctor_approved' => 'تم تفعيل حسابك في CareLink',
            'doctor_rejected' => 'تحديث بخصوص طلب انضمامك',
            'doctor_suspended' => 'تم إيقاف حسابك في CareLink',
            'doctor_activated' => 'تم إعادة تفعيل حسابك في CareLink',
            'password_reset' => 'إعادة تعيين كلمة السر',
            default => 'إشعار من CareLink',
        };
    }

    private static function getTemplate($type)
    {
        return match ($type) {
            'doctor_approved' => 'emails.doctors.approved',
            'doctor_rejected' => 'emails.doctors.rejected',
            'doctor_suspended' => 'emails.doctors.suspended',
            'doctor_activated' => 'emails.doctors.activated',
            'password_reset' => 'emails.auth.password_reset',
            default => 'emails.general',
        };
    }
}
