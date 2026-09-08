<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'company' => $this->company,
            'quantity' => (int) $this->quantity,
            'minQuantity' => (int) $this->min_quantity,
            'unit' => $this->unit,
            'price' => (float) $this->price,
            'expiryDate' => optional($this->expiry_date)->toDateString(),
            'keywords' => $this->keywords ?? [],
            'batches' => InventoryBatchResource::collection($this->whenLoaded('batches')),
            'updatedAt' => optional($this->updated_at)->toIso8601String(),
            'updatedBy' => $this->updated_by,
            'createdAt' => optional($this->created_at)->toIso8601String(),
        ];
    }
}