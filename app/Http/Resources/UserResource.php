<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\UserHelper;
use Illuminate\Support\Facades\Cache;

class UserResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'profile_photo_path' => Cache::remember("user.{$this->id}.photo", now()->addMinutes(60), function () {
            return $this->profile_photo_path ? asset($this->profile_photo_path) : UserHelper::generateAvatarUrl($this->name);
            }),
            'name' => $this->name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'phone' => $this->phone,
            'address' => $this->address,
            'address_2' => $this->address_2,
            'zip_code' => $this->zip_code,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'gender' => $this->gender,
            'latitude' => (double) $this->latitude,
            'longitude' => (double) $this->longitude,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
            'deleted_at' => $this->deleted_at ? $this->deleted_at->toDateTimeString() : null,
            'user_role' => $this->roles->pluck('name')->first() ?? null,
            'role_id' => $this->roles->pluck('id')->first() ?? null,
            'social_provider' => $this->providers->isNotEmpty() ? $this->providers : [],
           
        ];
    }
}


