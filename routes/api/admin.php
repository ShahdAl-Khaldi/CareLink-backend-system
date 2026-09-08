<?php

use App\Http\Controllers\Api\Admin\AdController;
use App\Http\Controllers\Api\Admin\ArticleController;
use App\Http\Controllers\Api\Admin\BackupController;
use App\Http\Controllers\Api\Admin\ContactMessageController;
use App\Http\Controllers\Api\Admin\FaqController;
use App\Http\Controllers\Api\Admin\NewsletterSubscriberController;
use App\Http\Controllers\Api\Admin\NotificationController;
use App\Http\Controllers\Api\Admin\PostController;
use App\Http\Controllers\Api\Admin\SettingController;
use App\Http\Controllers\Api\Admin\StaffController;
use App\Http\Controllers\Api\Admin\TestimonialController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\PatientController;
 
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'checkRole:admin'])->prefix('admin')->group(function () {

    Route::get('/doctors', [DoctorController::class, 'index']);
    Route::post('/doctors', [AdminController::class, 'store']);

    Route::get('/pending-doctors', [AdminController::class, 'showPending']);
    Route::patch('/approve-doctor/{id}', [AdminController::class, 'approveDoctor']);
    Route::put('/suspend-doctor/{id}', [AdminController::class, 'suspendDoctor']);
    Route::put('/activate-doctor/{id}', [AdminController::class, 'activateDoctor']);

    Route::delete('/reject-doctor/{id}', [AdminController::class, 'rejectDoctor']);
    Route::post('/doctors/{id}/email', [AdminController::class, 'sendEmailToDoctor']);

    // Route::apiResource('ads', AdController::class);
    // Route::apiResource('posts', PostController::class)->only(['index', 'store', 'destroy']);

    Route::get('/ads', [AdController::class, 'index']);
    Route::post('/ads', [AdController::class, 'store']);
    Route::put('/ads/{id}', [AdController::class, 'update']);
    Route::delete('/ads/{id}', [AdController::class, 'destroy']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/general', [NotificationController::class, 'sendGeneral']);
    Route::post('/notifications/user', [NotificationController::class, 'sendToUser']);

    Route::patch('/posts/{id}/approve', [PostController::class, 'approve']);

    Route::post('/broadcast', [AdminController::class, 'sendBroadcast']);
    Route::get('/broadcasts', [AdminController::class, 'getAllBroadcasts']);
    Route::delete('/broadcasts/{id}', [AdminController::class, 'deleteBroadcast']);

    Route::get('/articles', [ArticleController::class, 'index']);
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::put('/articles/{id}', [ArticleController::class, 'update']);
    Route::delete('/articles/{id}', [ArticleController::class, 'destroy']);

    Route::get('/faqs', [FaqController::class, 'index']);
    Route::post('/faqs', [FaqController::class, 'store']);
    Route::put('/faqs/{id}', [FaqController::class, 'update']);
    Route::delete('/faqs/{id}', [FaqController::class, 'destroy']);

    Route::get('/testimonials', [TestimonialController::class, 'index']);
    Route::post('/testimonials', [TestimonialController::class, 'store']);
    Route::put('/testimonials/{id}', [TestimonialController::class, 'update']);
    Route::delete('/testimonials/{id}', [TestimonialController::class, 'destroy']);

    Route::get('/contact-messages', [ContactMessageController::class, 'index']);
    Route::patch('/contact-messages/{id}/read', [ContactMessageController::class, 'markAsRead']);
    Route::delete('/contact-messages/{id}', [ContactMessageController::class, 'destroy']);

    Route::get('/newsletter-subscribers', [NewsletterSubscriberController::class, 'index']);
    Route::delete('/newsletter-subscribers/{id}', [NewsletterSubscriberController::class, 'destroy']);

    Route::get('/settings', [SettingController::class, 'index']);
    Route::post('/settings', [SettingController::class, 'update']);

    Route::get('/', [AdminController::class, 'show']);
    Route::put('/profile', [AdminController::class, 'updateProfile']);
    Route::put('/password', [AdminController::class, 'updatePassword']);

    Route::get('/reception-patients', [PatientController::class, 'getReceptionPatients']);
    Route::get('/appointments', [AdminController::class, 'getAllAppointments']);

    Route::get('/staff', [StaffController::class, 'index']);
    Route::post('/staff', [StaffController::class, 'store']);
    Route::put('/staff/{id}', [StaffController::class, 'update']);
    Route::patch('/staff/{id}/status', [StaffController::class, 'updateStatus']);
    Route::delete('/staff/{id}', [StaffController::class, 'destroy']);
    
    
   

});
 

    Route::middleware(['auth:sanctum', 'checkRole:admin'])->prefix('admin/backups')->group(function () {
    Route::get('/', [BackupController::class, 'index']);
    Route::post('/', [BackupController::class, 'runManual']);              // بدل /run
    Route::post('/{id}/restore', [BackupController::class, 'restore']);    // جديد
    Route::get('/{id}/download', [BackupController::class, 'download']);
    Route::delete('/{id}', [BackupController::class, 'destroy']);
    Route::get('/settings', [BackupController::class, 'getSettings']);
    Route::patch('/settings', [BackupController::class, 'updateSettings']);
});