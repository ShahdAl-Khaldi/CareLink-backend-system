<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Broadcast;
use Illuminate\Http\Request;

class BroadcastController extends Controller
{
    // نفس تطابق target/role الموجود بالفرونت (ROLE_TARGET_MAP في broadcastNotifications.js) —
    // مسار عام لأي دور ما إله controller بث خاص فيه (صيدلية/مختبر/أشعة/استقبال/مخزون)
    private const ROLE_TARGETS = [
        'patient' => ['all', 'patients', 'patient'],
        'doctor' => ['all', 'doctors', 'doctor'],
        'pharmacy' => ['all', 'pharmacists', 'pharmacist', 'pharmacy'],
        'laboratory' => ['all', 'laboratory', 'lab'],
        'radiology' => ['all', 'radiology'],
        'reception' => ['all', 'reception'],
        'inventory_manager' => ['all', 'inventory_manager', 'inventory_managers', 'inventory'],
        'admin' => ['all'],
    ];

    public function mine(Request $request)
    {
        $targets = self::ROLE_TARGETS[$request->user()->role] ?? ['all'];

        $broadcasts = Broadcast::whereIn('target', $targets)
            ->latest()
            ->get();

        return response()->json(['data' => $broadcasts], 200);
    }
}
