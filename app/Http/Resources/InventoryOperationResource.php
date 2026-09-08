<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryOperationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'itemId' => $this->inventory_id,
            'itemName' => $this->item_name,
            'type' => $this->type,
            'delta' => (int) $this->delta,
            'actorName' => $this->actor_name,
            'notes' => $this->notes,
            'createdAt' => optional($this->created_at)->toIso8601String(),
        ];
    }
}