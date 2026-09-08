<div align="center">

# 🏥 CareLink Backend System

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

**نظام إدارة العيادات الطبية المتكامل — Backend API**

<p align="center">
  <img src="https://img.shields.io/badge/API-RESTful-0096FF" />
  <img src="https://img.shields.io/badge/Auth-Sanctum%20%2B%20OAuth2-FF6B6B" />
  <img src="https://img.shields.io/badge/Real--Time-Reverb-4ECDC4" />
  <img src="https://img.shields.io/badge/Queue-Database-45B7D1" />
</p>

</div>

---

## 📋 فهرس المحتويات

- [نظرة عامة](#-نظرة-عامة)
- [المميزات الرئيسية](#-المميزات-الرئيسية)
- [هيكل النظام والأدوار](#-هيكل-النظام-والأدوار)
- [الموديلات والكيانات](#-الموديلات-والكيانات)
- [المتطلبات](#-المتطلبات)
- [التثبيت والإعداد](#-التثبيت-والإعداد)
- [البيئة والإعدادات](#-البيئة-والإعدادات)
- [الـ API Endpoints](#-api-endpoints)
- [الأمان والصلاحيات](#-الأمان-والصلاحيات)
- [الاختبارات](#-الاختبارات)
- [هيكل المشروع](#-هيكل-المشروع)
- [الترخيص](#-الترخيص)

---

## 🔭 نظرة عامة

**CareLink** هو نظام backend متكامل لإدارة العيادات الطبية، مبني باستخدام **Laravel 13** و **PHP 8.3**. يوفر واجهة برمجة تطبيقات RESTful API كاملة لإدارة جميع جوانب العيادة من المواعيد والسجلات الطبية إلى المخزون والمختبر والأشعة.

### 🎯 ما يميز النظام
- **تعدد الأدوار**: 8 أدوار مختلفة (Admin, Doctor, Patient, Reception, Lab, Pharmacy, Radiology, Inventory Manager)
- **التواصل الفوري**: نظام محادثات مدمج بين الطبيب والمريض
- **الإشعارات الذكية**: إشعارات فورية للمواعيد والنتائج والوصفات الطبية
- **إدارة المخزون**: تتبع كامل للأدوية والمستلزمات الطبية
- **تسجيل الدخول الاجتماعي**: دعم Google OAuth 2.0
- **حماية متقدمة**: مصادقة Sanctum + تحكم دقيق بالصلاحيات (RBAC)

---

## ✨ المميزات الرئيسية

| الموديول | الوصف |
|----------|-------|
| 👤 **إدارة المستخدمين** | تسجيل/دخول/استعادة كلمة المرور لجميع الأدوار |
| 📅 **المواعيد** | حجز، إلغاء، تأكيد، وإدارة المواعيد الطبية |
| 📋 **السجلات الطبية** | إنشاء وعرض السجلات الطبية والتشخيصات |
| 💬 **المحادثات** | دردشة فورية بين الطبيب والمريض مرتبطة بالموعد |
| 🔔 **الإشعارات** | نظام إشعارات داخلي + بث عام (Broadcasts) |
| 🧪 **المختبر** | طلبات تحاليل مع تتبع الحالة (Pending → In Progress → Completed) |
| 🩻 **الأشعة** | طلبات أشعة مع إرفاق النتائج والصور |
| 💊 **الصيدلية** | إدارة الوصفات الطبية والأدوية |
| 📦 **المخزون** | إدارة الأصناف، الدفعات، والعمليات (Stock In/Out/Adjustment) |
| 🏠 **الاستقبال** | تسجيل المرضى، تسجيل الوصول (Check-in)، تسليم الشift |
| 📝 **المحتوى** | مقالات طبية، FAQs، شهادات المرضى (Testimonials) |
| 📊 **لوحة التحكم** | إحصائيات وإدارة كاملة للأدمن |

---

## 👥 هيكل النظام والأدوار

```
┌─────────────────────────────────────────────────────────────┐
│                      CareLink System                        │
├─────────────────────────────────────────────────────────────┤
│  🔴 Admin          │  إدارة النظام، الموظفين، المحتوى     │
│  👨‍⚕️ Doctor         │  المواعيد، السجلات الطبية، الوصفات   │
│  🧑 Patient        │  الحجز، السجلات، المحادثات           │
│  🏥 Reception      │  تسجيل المرضى، الاستقبال، الشيفتات   │
│  🧪 Lab            │  طلبات التحاليل ونتائجها             │
│  💊 Pharmacy       │  الوصفات والأدوية                    │
│  🩻 Radiology      │  طلبات الأشعة ونتائجها               │
│  📦 Inventory      │  إدارة المخزون والكميات              │
└─────────────────────────────────────────────────────────────┘
```

---

## 🗄️ الموديلات والكيانات

### الكيانات الرئيسية (28 Model)

| الكيان | الوظيفة |
|--------|---------|
| `User` | المستخدم الأساسي مع Role-based access |
| `Patient` / `PatientProfile` | بيانات المريض والملف الطبي |
| `DoctorProfile` | بيانات الطبيب والتخصص والتقييم |
| `ReceptionistProfile` | بيانات موظف الاستقبال |
| `LabProfile` | بيانات فني المختبر |
| `Appointment` | المواعيد الطبية مع حالات متعددة |
| `MedicalRecord` | السجلات الطبية والتشخيص |
| `Prescription` / `PrescriptionMedicine` | الوصفات الطبية والأدوية |
| `LabOrder` | طلبات التحاليل المخبرية |
| `ImagingOrder` | طلبات الأشعة |
| `Conversation` / `Message` | المحادثات والرسائل |
| `Notification` / `Broadcast` | الإشعارات والبث العام |
| `Inventory` / `InventoryBatch` / `InventoryOperation` | المخزون والدفعات والعمليات |
| `ShiftHandover` | تسليم الشيفتات بين الاستقبال |
| `Article` / `Post` / `Ad` / `Faq` / `Testimonial` | المحتوى والتسويق |
| `DoctorRating` | تقييم الأطباء |
| `ContactMessage` | رسائل التواصل |
| `Setting` | إعدادات النظام |

---

## 🛠️ المتطلبات

- **PHP** >= 8.3
- **Composer** >= 2.0
- **Node.js** >= 20 (لـ Vite/Frontend assets)
- **Database**: MySQL 
- **Redis** (اختياري — للـ Cache و Queue)

### الحزم الرئيسية

```json
{
  "laravel/framework": "^13.8",
  "laravel/sanctum": "^4.0",
  "laravel/socialite": "^5.29",
  "laravel/reverb": "^1.0",
  "doctrine/dbal": "^4.4"
}
```

---

## 🚀 التثبيت والإعداد

### 1. استنساخ المستودع

```bash
git clone https://github.com/nabil-assi/CareLink-backend-system.git
cd CareLink-backend-system
```

### 2. تثبيت الحزم

```bash
# PHP dependencies
composer install

# Node dependencies (للـ Vite)
npm install
npm run build
```

### 3. إعداد البيئة

```bash
cp .env.example .env
php artisan key:generate
```

### 4. قاعدة البيانات

```bash
# SQLite (افتراضي)
touch database/database.sqlite

# تشغيل المايجريشنز
php artisan migrate --force

# تعبئة البيانات الأولية (حسابات تجريبية)
php artisan db:seed --class=RoleAccountsSeeder
```

### 5. تشغيل الخادم

```bash
# تشغيل الخادم فقط
php artisan serve

# أو تشغيل كل الخدمات (Server + Queue + Logs + Vite)
composer run dev
```

---

## ⚙️ البيئة والإعدادات

### ملف `.env` الرئيسي

```env
APP_NAME=CareLink
APP_URL=http://localhost

# Database (Mysql افتراضي)
DB_CONNECTION=mysql

# Queue & Cache
QUEUE_CONNECTION=database
CACHE_STORE=database

# Google OAuth (لتسجيل الدخول بالجوجل)
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/api/auth/google/callback

# Frontend URL (لـ CORS)
FRONTEND_URL=http://localhost:5173

# Mail (لاستعادة كلمة المرور)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
```

### الحسابات التجريبية (من Seeder)

| الدور | البريد | كلمة المرور |
|-------|--------|-------------|
| Admin | `admin@gmail.com` | `12345678` |
| Doctor | `doctor@gmail.com` | `12345678` |
| Patient | `patient@gmail.com` | `12345678` |
| Reception | `reception@gmail.com` | `12345678` |
| Lab | `lab@gmail.com` | `12345678` |

---

## 🔌 API Endpoints

### المصادقة (Auth)

| Method | Endpoint | الوصف |
|--------|----------|-------|
| `POST` | `/api/patient/register` | تسجيل مريض جديد |
| `POST` | `/api/patient/login` | دخول المريض |
| `POST` | `/api/doctor/register` | تسجيل طبيب جديد |
| `POST` | `/api/doctor/login` | دخول الطبيب |
| `POST` | `/api/admin/login` | دخول الأدمن |
| `POST` | `/api/staff/login` | دخول الموظفين (استقبال/مختبر/أشعة/صيدلية/مخزون) |
| `GET`  | `/api/auth/google` | تسجيل دخول بـ Google |

### المريض (Patient)

| Method | Endpoint | الوصف |
|--------|----------|-------|
| `GET`  | `/api/patient/profile` | الملف الشخصي |
| `PATCH`| `/api/patient/profile` | تحديث الملف |
| `GET`  | `/api/patient/medical-profile` | الملف الطبي |
| `POST` | `/api/patient/appointments` | حجز موعد |
| `GET`  | `/api/patient/appointments` | مواعيدي |
| `PATCH`| `/api/patient/appointments/{id}/cancel` | إلغاء موعد |
| `GET`  | `/api/patient/medical-records` | السجلات الطبية |
| `POST` | `/api/patient/ratings` | تقييم طبيب |

### الطبيب (Doctor)

| Method | Endpoint | الوصف |
|--------|----------|-------|
| `GET`  | `/api/doctor/profile` | الملف الشخصي |
| `GET`  | `/api/doctor/appointments` | مواعيدي |
| `PATCH`| `/api/doctor/appointments/{id}/cancel` | إلغاء موعد |
| `POST` | `/api/doctor/appointments/{id}/medical-records` | إضافة سجل طبي |
| `GET`  | `/api/doctor/appointments/{id}/medical-records` | عرض السجل الطبي |
| `POST` | `/api/doctor/appointments/{id}/prescriptions` | إضافة وصفة |
| `POST` | `/api/doctor/appointments/{id}/lab-orders` | طلب تحليل |
| `POST` | `/api/doctor/appointments/{id}/imaging-orders` | طلب أشعة |

### الاستقبال (Reception)

| Method | Endpoint | الوصف |
|--------|----------|-------|
| `POST` | `/api/reception/patients` | تسجيل مريض جديد |
| `GET`  | `/api/reception/patients` | قائمة المرضى |
| `POST` | `/api/reception/appointments` | حجز موعد |
| `PATCH`| `/api/reception/appointments/{id}/check-in` | تسجيل وصول |
| `POST` | `/api/reception/shift-handover` | تسليم الشيفت |

### الأدمن (Admin)

| Method | Endpoint | الوصف |
|--------|----------|-------|
| `GET`  | `/api/admin/dashboard` | لوحة التحكم |
| `GET`  | `/api/admin/patients` | جميع المرضى |
| `GET`  | `/api/admin/doctors` | جميع الأطباء |
| `PATCH`| `/api/admin/doctors/{id}/approve` | قبول طبيب |
| `DELETE`| `/api/admin/doctors/{id}/reject` | رفض طبيب |
| `GET`  | `/api/admin/staff` | إدارة الموظفين |
| `POST` | `/api/admin/staff` | إضافة موظف |
| `GET`  | `/api/admin/posts` | إدارة المنشورات |
| `POST` | `/api/admin/posts` | إضافة منشور |
| `GET`  | `/api/admin/ads` | إدارة الإعلانات |
| `POST` | `/api/admin/broadcast` | بث إشعار عام |
| `GET`  | `/api/admin/appointments` | جميع المواعيد |

### المختبر (Lab)

| Method | Endpoint | الوصف |
|--------|----------|-------|
| `GET`  | `/api/laboratory/orders` | طلبات التحاليل |
| `POST` | `/api/laboratory/orders/{id}/start` | بدء التحليل |
| `POST` | `/api/laboratory/orders/{id}/complete` | إكمال التحليل |
| `POST` | `/api/laboratory/orders/{id}/redo` | إعادة التحليل |

### الأشعة (Radiology)

| Method | Endpoint | الوصف |
|--------|----------|-------|
| `GET`  | `/api/radiology/orders` | طلبات الأشعة |
| `POST` | `/api/radiology/orders/{id}/complete` | إكمال الأشعة |

### الصيدلية (Pharmacy)

| Method | Endpoint | الوصف |
|--------|----------|-------|
| `GET`  | `/api/pharmacy/prescriptions` | الوصفات الطبية |
| `POST` | `/api/pharmacy/prescriptions/{id}/ready` | تجهيز الوصفة |
| `POST` | `/api/pharmacy/prescriptions/{id}/dispense` | صرف الأدوية |

### المخزون (Inventory)

| Method | Endpoint | الوصف |
|--------|----------|-------|
| `GET`  | `/api/inventory/items` | عرض الأصناف |
| `POST` | `/api/inventory/items` | إضافة صنف |
| `PUT`  | `/api/inventory/items/{id}` | تعديل صنف |
| `DELETE`| `/api/inventory/items/{id}` | حذف صنف |
| `POST` | `/api/inventory/items/{id}/adjust` | تعديل الكمية |
| `GET`  | `/api/inventory/operations` | سجل العمليات |

### المحادثات (Chat)

| Method | Endpoint | الوصف |
|--------|----------|-------|
| `GET`  | `/api/appointments/{id}/conversation` | بدء/عرض محادثة |
| `GET`  | `/api/conversations/{id}/messages` | رسائل المحادثة |
| `POST` | `/api/conversations/{id}/messages` | إرسال رسالة |
| `GET`  | `/api/chat/unread-counts` | عدد الرسائل غير المقروءة |

### الإشعارات (Notifications)

| Method | Endpoint | الوصف |
|--------|----------|-------|
| `GET`  | `/api/notifications/mine` | إشعاراتي |
| `GET`  | `/api/notifications/unread-count` | العدد غير المقروء |
| `POST` | `/api/notifications/{id}/read` | تحديد كمقروء |
| `POST` | `/api/notifications/read-all` | تحديد الكل كمقروء |

### المحتوى العام (Public)

| Method | Endpoint | الوصف |
|--------|----------|-------|
| `GET`  | `/api/landing` | بيانات الصفحة الرئيسية |
| `GET`  | `/api/articles` | المقالات الطبية |
| `GET`  | `/api/faqs` | الأسئلة الشائعة |
| `GET`  | `/api/testimonials` | شهادات المرضى |
| `POST` | `/api/contact` | رسالة تواصل |

---

## 🔒 الأمان والصلاحيات

### نظام المصادقة
- **Laravel Sanctum**: توكنات API آمنة
- **Google OAuth 2.0**: تسجيل دخول اجتماعي
- **Middleware مخصص**: `CheckRole` للتحقق من الأدوار

### الأدوار المدعومة (Roles)
```php
'admin', 'doctor', 'patient', 'reception', 
'lab', 'pharmacy', 'radiology', 'inventory_manager'
```

### حماية المسارات
```php
Route::middleware(['auth:sanctum', 'checkRole:admin'])->group(...);
Route::middleware(['auth:sanctum', 'checkRole:doctor'])->group(...);
Route::middleware(['auth:sanctum', 'checkRole:inventory_manager,pharmacy,admin'])->group(...);
```

### حالات المواعيد (Appointment Status)
```
pending → confirmed → checked_in → in_progress → completed → cancelled
```

---

## 🧪 الاختبارات

```bash
# تشغيل جميع الاختبارات
php artisan test

# أو باستخدام Pest
./vendor/bin/pest
```

---

## 📁 هيكل المشروع

```
CareLink-backend-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/              # كونترولرز الـ API
│   │   │   │   ├── Admin/        # كونترولرز الأدمن
│   │   │   │   ├── Reception/    # كونترولرز الاستقبال
│   │   │   │   ├── AdminAuthController.php
│   │   │   │   ├── AppointmentController.php
│   │   │   │   ├── ChatController.php
│   │   │   │   ├── DoctorAuthController.php
│   │   │   │   ├── DoctorController.php
│   │   │   │   ├── InventoryController.php
│   │   │   │   ├── LabOrderController.php
│   │   │   │   ├── PatientAuthController.php
│   │   │   │   ├── PatientController.php
│   │   │   │   ├── PharmacyController.php
│   │   │   │   ├── RadiologyController.php
│   │   │   │   └── ...
│   │   │   └── Auth/
│   │   │       └── GoogleAuthController.php
│   │   └── Middleware/
│   │       ├── CheckRole.php
│   │       ├── EnsureUserIsAdmin.php
│   │       ├── EnsureUserIsDoctor.php
│   │       └── EnsureUserIsPatient.php
│   ├── Models/                   # 28 Model
│   └── ...
├── database/
│   ├── migrations/               # 40+ migration
│   └── seeders/
│       └── RoleAccountsSeeder.php
├── routes/
│   ├── api.php                   # API Routes الرئيسية
│   └── web.php
├── tests/                        # Pest PHP Tests
├── composer.json
├── .env.example
└── README.md
```

---

## 📝 الترخيص

هذا المشروع مرخص بموجب [MIT License](LICENSE).

---

<div align="center">

**صنع بـ ❤️ لإدارة العيادات بذكاء**

</div>
