<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {
        $backups = Backup::latest()->get()->map(fn ($b) => [
            'id' => $b->id,
            'name' => $b->filename,
            'size' => $b->size_bytes,
            'created_at' => $b->created_at,
            'type' => $b->type,
            'download_url' => null, // الفرونت بيستخدم /download endpoint بدلها
        ]);

        return response()->json(['data' => $backups]);
    }

    public function runManual(Request $request)
    {
        Artisan::call('backup:run');

        Backup::create([
            'filename' => 'backup_' . now()->format('Y-m-d_H-i-s') . '.zip',
            'type' => 'manual',
            'triggered_by' => $request->user()->id,
            'status' => 'success',
        ]);

        return response()->json(['message' => 'تم إنشاء نسخة احتياطية بنجاح']);
    }

    public function download($id)
    {
        $backup = Backup::findOrFail($id);
        return Storage::disk($backup->disk)->download($backup->filename);
    }

    public function restore($id)
    {
        // ⚠️ هاد أخطر جزء بالميزة - راجع الشرح تحت قبل ما تفعّله فعلياً
        return response()->json(['message' => 'ميزة الاسترجاع التلقائي غير مفعّلة حالياً لأسباب أمنية'], 501);
    }

    public function destroy($id)
    {
        $backup = Backup::findOrFail($id);
        Storage::disk($backup->disk)->delete($backup->filename);
        $backup->delete();

        return response()->json(['message' => 'تم حذف النسخة الاحتياطية']);
    }

    public function getSettings()
    {
        $interval = Setting::where('key', 'backup_interval_hours')->value('value') ?? 12;
        return response()->json(['interval_hours' => (int) $interval]);
    }

    public function updateSettings(Request $request)
    {
        $request->validate(['interval_hours' => 'required|integer|min:1']);

        Setting::updateOrCreate(
            ['key' => 'backup_interval_hours'],
            ['value' => $request->interval_hours]
        );

        return response()->json(['message' => 'تم تحديث فترة النسخ الاحتياطي']);
    }
}