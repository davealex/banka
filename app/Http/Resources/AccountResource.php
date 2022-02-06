<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'ref' => $this->ref,
            'number' => $this->number,
            'balance' => $this->balance,
            'status' => $this->status,
            'user' => new UserResource($this->user),
            'type' => new TypeResource($this->type),
            'manager' => new UserResource($this->manager),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
