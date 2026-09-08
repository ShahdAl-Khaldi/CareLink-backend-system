<?php

use App\Http\Controllers\Api\Admin\AdController;
use App\Http\Controllers\Api\Admin\PostController;
use App\Http\Controllers\Api\Admin\SettingController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\BroadcastController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\InventoryOperationController;
use App\Http\Controllers\Api\LabOrderController;
use App\Http\Controllers\Api\LandingController;
use App\Http\Controllers\Api\MyNotificationController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/articles', [LandingController::class, 'getPublishedArticles']);
Route::get('/articles/{slug}', [LandingController::class, 'showArticles']);
Route::get('/home/doctors', [LandingController::class, 'getDoctors']);
Route::get('/faqs', [LandingController::class, 'getFaqs']);
Route::get('/testimonials', [LandingController::class, 'getTestimonials']);
Route::post('/contact', [ContactController::class, 'store']);
Route::get('/settings/contact', [ContactController::class, 'getContactSettings']); // اختياري لجلب بيانات التواصل ديناميكياً
Route::get('/home/settings', [SettingController::class, 'index']);

Route::get('/landing/ads', [AdController::class, 'index']);

Route::get('/offers', [OfferController::class, 'index']);
Route::post('/newsletter/subscribe', [OfferController::class, 'subscribe']);

Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/appointments/{appointmentId}/conversation', [ChatController::class, 'startOrGetConversation']);
    // رفع/تحديث الصورة الشخصية - شغالة لأي دور (موظف، طبيب، مريض...)
    Route::post('/profile/picture', [ProfileController::class, 'updatePicture']);
    Route::get('/conversations/{conversationId}/messages', [ChatController::class, 'getMessages']);
    Route::post('/conversations/{conversationId}/messages', [ChatController::class, 'sendMessage']);
    Route::get('/broadcasts', [BroadcastController::class, 'mine']);

});

Route::middleware(['auth:sanctum', 'checkRole:admin'])->group(function () {
    Route::get('/admin/posts', [PostController::class, 'index']);
    Route::post('/admin/posts', [PostController::class, 'store']);
    Route::put('/admin/posts/{id}', [PostController::class, 'update']);
    Route::delete('/admin/posts/{id}', [PostController::class, 'destroy']);
    Route::patch('/admin/posts/{id}/approve', [PostController::class, 'approve']);

    Route::get('/admin/patients', [PatientController::class, 'getAllPatients']);

    Route::get('/admin/appointments', [AppointmentController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'checkRole:laboratory'])->prefix('laboratory')->group(function () {
    Route::get('/orders', [LabOrderController::class, 'index']);
    Route::post('/orders/{id}/start', [LabOrderController::class, 'start']);
    Route::post('/orders/{id}/complete', [LabOrderController::class, 'complete']);
    Route::post('/orders/{id}/redo', [LabOrderController::class, 'redo']);
});

Route::middleware(['auth:sanctum', 'checkRole:inventory_manager,pharmacy,admin'])->prefix('inventory')->group(function () {
    Route::get('/items', [InventoryController::class, 'index']);
    Route::get('/items/{inventory}', [InventoryController::class, 'show']);
    Route::get('/operations', [InventoryOperationController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'checkRole:inventory_manager,admin'])->prefix('inventory')->group(function () {
    Route::post('/items', [InventoryController::class, 'store']);
    Route::put('/items/{inventory}', [InventoryController::class, 'update']);
    Route::delete('/items/{inventory}', [InventoryController::class, 'destroy']);
    Route::post('/items/{inventory}/adjust', [InventoryController::class, 'adjust']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications/mine', [MyNotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [MyNotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [MyNotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [MyNotificationController::class, 'markAllAsRead']);
    Route::get('/chat/unread-counts', [ChatController::class, 'unreadCounts']);
});
