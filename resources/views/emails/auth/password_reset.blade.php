<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; text-align: center; }
        .container { max-width: 500px; margin: 20px auto; background: #ffffff; padding: 40px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h1 { color: #0f172a; font-size: 24px; margin-bottom: 20px; }
        p { color: #64748b; font-size: 16px; line-height: 1.6; }
        .otp-box { background: #0ea5e9; color: #ffffff; font-size: 36px; font-weight: bold; letter-spacing: 8px; padding: 15px 0; margin: 30px 0; border-radius: 8px; display: inline-block; width: 100%; }
        .footer { margin-top: 30px; font-size: 12px; color: #94a3b8; }
        .brand { font-weight: bold; color: #0ea5e9; }
    </style>
</head>
<body>
    <div class="container">
        <h1>إعادة تعيين كلمة السر</h1>
        <p>مرحباً، تلقينا طلباً لإعادة تعيين كلمة السر الخاصة بحسابك في <span class="brand">CareLink</span>.</p>
        
        <div class="otp-box">{{ $data['otp'] }}</div>
        
        <p>هذا الرمز صالح لمدة <strong>10 دقائق</strong> فقط. إذا لم تكن أنت من طلب ذلك، يرجى تجاهل هذه الرسالة.</p>
        
        <div class="footer">
            &copy; {{ date('Y') }} CareLink. جميع الحقوق محفوظة.
        </div>
    </div>
</body>
</html>