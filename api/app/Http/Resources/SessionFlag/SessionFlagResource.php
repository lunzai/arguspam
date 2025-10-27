<?php

namespace App\Http\Resources\SessionFlag;

use App\Http\Resources\Resource;
use App\Http\Resources\Session\SessionResource;
use Illuminate\Http\Request;

class SessionFlagResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'attributes' => [
                'id' => $this->id,
                'session_id' => $this->session_id,
                'flag' => $this->flag,
                'created_at' => $this->created_at,
            ],
            $this->mergeWhen($this->hasRelation(), [
                'relationships' => [
                    'session' => SessionResource::make(
                        $this->whenLoaded('session')
                    ),
                ],
            ]),
        ];
    }
}
