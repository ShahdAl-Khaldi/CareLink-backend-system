<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InventoryOperationResource;
use App\Models\InventoryOperation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InventoryOperationController extends Controller
{
    /**
     * GET /api/inventory/operations
     * يرجّع كل العمليات (الأحدث أولاً). الفرونت بيفلترها حسب itemId عند الحاجة.
     *
     * Query params اختيارية:
     *  - item_id: لجلب عمليات دواء معيّن فقط
     *  - limit: تحديد عدد النتائج (مثلاً لأحدث 6 عمليات في لوحة التحكم)
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = InventoryOperation::query()->latest();

        if ($request->filled('item_id')) {
            $query->where('inventory_id', $request->input('item_id'));
        }

        if ($request->filled('limit')) {
            $query->limit((int) $request->input('limit'));
        }

        return InventoryOperationResource::collection($query->get());
    }
}
