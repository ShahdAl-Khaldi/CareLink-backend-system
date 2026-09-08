<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdjustInventoryQuantityRequest;
use App\Http\Requests\StoreInventoryRequest;
use App\Http\Requests\UpdateInventoryRequest;
use App\Http\Resources\InventoryResource;
use App\Models\Inventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * GET /api/inventory/items
     * يرجّع كل الأدوية مع دفعاتها. الفلترة/الفرز/البحث تتم بالكامل في الفرونت (useMemo).
     */
    public function index(): AnonymousResourceCollection
    {
        $items = Inventory::with(['batches' => function ($query) {
            $query->orderBy('expiry_date');
        }])->orderBy('name')->get();

        return InventoryResource::collection($items);
    }

    /**
     * GET /api/inventory/items/{inventory}
     */
    public function show(Inventory $inventory): InventoryResource
    {
        $inventory->load(['batches' => function ($query) {
            $query->orderBy('expiry_date');
        }]);

        return new InventoryResource($inventory);
    }

    /**
     * POST /api/inventory/items
     * إضافة دواء جديد مع دفعته الأولى.
     */
    public function store(StoreInventoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $actorName = $request->user()->name;

        $item = DB::transaction(function () use ($data, $actorName) {
            $item = Inventory::create([
                'name' => $data['name'],
                'category' => $data['category'],
                'company' => $data['company'],
                'unit' => $data['unit'],
                'min_quantity' => $data['min_quantity'],
                'price' => $data['price'],
                'keywords' => $data['keywords'] ?? [],
                'updated_by' => $actorName,
            ]);

            $item->batches()->create([
                'batch_number' => $data['batch_number'],
                'quantity' => $data['quantity'],
                'expiry_date' => $data['expiry_date'],
            ]);

            $item->recalculateFromBatches();

            $item->operations()->create([
                'item_name' => $item->name,
                'type' => 'create',
                'delta' => $data['quantity'],
                'actor_name' => $actorName,
                'notes' => $data['notes'] ?? 'إضافة دواء جديد',
            ]);

            return $item;
        });

        return (new InventoryResource($item->load('batches')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * PUT /api/inventory/items/{inventory}
     * - إذا أُرسل مفتاح "batches": استبدال شامل للدفعات (يُستخدم من لوحة تتبع الدفعات).
     * - إذا أُرسلت "quantity" و"expiry_date": تعديل الدفعة الوحيدة مباشرة (نموذج التعديل البسيط).
     */
    public function update(UpdateInventoryRequest $request, Inventory $inventory): InventoryResource
    {
        $data = $request->validated();
        $actorName = $request->user()->name;

        DB::transaction(function () use ($data, $inventory, $actorName) {
            $inventory->fill([
                'name' => $data['name'],
                'category' => $data['category'],
                'company' => $data['company'],
                'unit' => $data['unit'],
                'min_quantity' => $data['min_quantity'],
                'price' => $data['price'],
                'keywords' => $data['keywords'] ?? [],
                'updated_by' => $actorName,
            ])->save();

            if (array_key_exists('batches', $data)) {
                $this->replaceBatches($inventory, $data['batches']);
            } elseif (array_key_exists('quantity', $data) && array_key_exists('expiry_date', $data)) {
                $batch = $inventory->batches()->first();

                if ($batch) {
                    $batch->update([
                        'quantity' => $data['quantity'],
                        'expiry_date' => $data['expiry_date'],
                    ]);
                } else {
                    $inventory->batches()->create([
                        'batch_number' => 'LOT-1',
                        'quantity' => $data['quantity'],
                        'expiry_date' => $data['expiry_date'],
                    ]);
                }
            }

            $inventory->recalculateFromBatches();

            $inventory->operations()->create([
                'item_name' => $inventory->name,
                'type' => array_key_exists('batches', $data) ? 'batches_update' : 'update',
                'delta' => 0,
                'actor_name' => $actorName,
                'notes' => $data['notes'] ?? (array_key_exists('batches', $data)
                    ? 'تحديث بيانات الدفعات'
                    : 'تعديل بيانات الدواء'),
            ]);
        });

        return new InventoryResource($inventory->fresh(['batches' => function ($query) {
            $query->orderBy('expiry_date');
        }]));
    }

    /**
     * استبدال شامل لدفعات الصنف: تحديث الموجود، إنشاء الجديد، وحذف ما لم يُرسل.
     *
     * @param  array<int, array{id?: int|null, batch_number: string, quantity: int, expiry_date: string}>  $batches
     */
    private function replaceBatches(Inventory $inventory, array $batches): void
    {
        $existingIds = $inventory->batches()->pluck('id')->all();
        $keepIds = [];

        foreach ($batches as $batchData) {
            $batchId = $batchData['id'] ?? null;

            if ($batchId && in_array($batchId, $existingIds, true)) {
                $inventory->batches()->where('id', $batchId)->update([
                    'batch_number' => $batchData['batch_number'],
                    'quantity' => $batchData['quantity'],
                    'expiry_date' => $batchData['expiry_date'],
                ]);
                $keepIds[] = $batchId;
                continue;
            }

            $created = $inventory->batches()->create([
                'batch_number' => $batchData['batch_number'],
                'quantity' => $batchData['quantity'],
                'expiry_date' => $batchData['expiry_date'],
            ]);
            $keepIds[] = $created->id;
        }

        $inventory->batches()->whereNotIn('id', $keepIds)->delete();
    }

    /**
     * DELETE /api/inventory/items/{inventory}
     */
    public function destroy(Request $request, Inventory $inventory): JsonResponse
    {
        DB::transaction(function () use ($request, $inventory) {
            $inventory->operations()->create([
                'item_name' => $inventory->name,
                'type' => 'delete',
                'delta' => -$inventory->quantity,
                'actor_name' => $request->user()->name,
                'notes' => $request->input('notes', 'حذف نهائي من قائمة الأدوية'),
            ]);

            // حذف الصنف يحذف دفعاته تلقائياً (cascadeOnDelete)،
            // وسجل العملية أعلاه يبقى محفوظاً وتتحول inventory_id فيه إلى null (nullOnDelete).
            $inventory->delete();
        });

        return response()->json(['message' => 'تم حذف الدواء من المخزون بنجاح']);
    }

    /**
     * POST /api/inventory/items/{inventory}/adjust
     * تعديل سريع للكمية (+1 / +10 / -1) من صفحة تفاصيل الدواء.
     * الإضافة تذهب لأقرب دفعة صلاحية، والخصم يتبع FEFO عبر كل الدفعات.
     */
    public function adjust(AdjustInventoryQuantityRequest $request, Inventory $inventory): InventoryResource|JsonResponse
    {
        $delta = (int) $request->validated()['delta'];

        if ($delta < 0 && ($inventory->quantity + $delta) < 0) {
            return response()->json(['message' => 'الكمية الحالية تساوي صفراً'], 422);
        }

        DB::transaction(function () use ($delta, $inventory, $request) {
            if ($delta > 0) {
                $batch = $inventory->batches()->orderBy('expiry_date')->first();

                if ($batch) {
                    $batch->increment('quantity', $delta);
                } else {
                    $inventory->batches()->create([
                        'batch_number' => 'LOT-1',
                        'quantity' => $delta,
                        'expiry_date' => now()->addYear()->toDateString(),
                    ]);
                }
            } else {
                $remaining = abs($delta);
                $batches = $inventory->batches()->orderBy('expiry_date')->get();

                foreach ($batches as $batch) {
                    if ($remaining <= 0) {
                        break;
                    }

                    $deduct = min($batch->quantity, $remaining);
                    $batch->decrement('quantity', $deduct);
                    $remaining -= $deduct;
                }
            }

            $inventory->recalculateFromBatches();

            $inventory->operations()->create([
                'item_name' => $inventory->name,
                'type' => $request->input('type', $delta > 0 ? 'restock' : 'adjust'),
                'delta' => $delta,
                'actor_name' => $request->user()->name,
                'notes' => $request->input('notes', "تعديل سريع بمقدار " . ($delta > 0 ? "+{$delta}" : $delta)),
            ]);
        });

        return new InventoryResource($inventory->fresh(['batches' => function ($query) {
            $query->orderBy('expiry_date');
        }]));
    }
}
