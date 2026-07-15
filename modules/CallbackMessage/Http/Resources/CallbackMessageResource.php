<?php

namespace Modules\CallbackMessage\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CallbackMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'phone_number' => $this->phone_number,
            'message' => $this->message,
            'is_read' => (bool) $this->is_read,
            'is_resolved' => (bool) $this->is_resolved
        ];
    }
}