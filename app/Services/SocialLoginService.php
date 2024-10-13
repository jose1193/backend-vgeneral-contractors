<?php

namespace App\Services;

use App\Models\User;
use App\Models\Provider;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\UserResource;
use App\DTOs\SocialLoginDTO;

class SocialLoginService
{
    public function handleProviderCallback(SocialLoginDTO $dto)
    {
        $provider = $this->validateProvider($dto->provider);
        if ($provider instanceof \Illuminate\Http\JsonResponse) {
            return $provider;
        }

        $providerUser = $this->getProviderUser($dto);
        $email = $this->validateEmail($providerUser->getEmail());
        $user = $this->getUserByEmail($email);

        if ($user) {
            $this->updateUser($user, $providerUser, $dto);
            $token = $this->createUserToken($user);
            $userResource = $this->cacheUserResource($user);
            return [
                'user' => $user,
                'token' => $token,
                'userResource' => $userResource
            ];
        } else {
            throw new \Exception('User not found. Social login is only allowed for existing users.');
        }
    }

    private function validateProvider($provider)
    {
        if (!in_array($provider, ['google', 'facebook', 'twitter'])) {
            throw new \Exception('You can only login via google, facebook, or twitter account');
        }
    }

    private function getProviderUser(SocialLoginDTO $dto)
    {
        $cacheKey = 'provider_user_' . $dto->provider . '_' . md5($dto->access_provider_token);
        return Cache::remember($cacheKey, 43200, function () use ($dto) {
            return Socialite::driver($dto->provider)->userFromToken($dto->access_provider_token);
        });
    }

    private function validateEmail($email)
    {
        $validatedEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
        if (!$validatedEmail) {
            throw new \Exception('Invalid email address from provider');
        }
        return $validatedEmail;
    }

    private function getUserByEmail($email)
    {
        return Cache::remember('user_email_' . $email, 43200, function () use ($email) {
            return User::where('email', $email)->first();
        });
    }

    private function updateUser($user, $providerUser, SocialLoginDTO $dto)
    {
        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
            $user->save();
            Cache::put('user_email_' . $user->email, $user, 43200);
        }

        $existingProvider = $user->providers()->firstWhere('provider', $dto->provider);

        if ($existingProvider) {
            $existingProvider->update(['provider_avatar' => $providerUser->getAvatar()]);
        } else {
            $user->providers()->create([
                'uuid' => Uuid::uuid4()->toString(),
                'provider' => $dto->provider,
                'provider_id' => $providerUser->getId(),
                'provider_avatar' => $providerUser->getAvatar(),
            ]);
        }

        Auth::login($user);
    }

    private function createUserToken($user)
    {
        $token = $user->createToken('auth_token')->plainTextToken;
        return [
            'token' => explode('|', $token)[1],
            'created_at' => $user->tokens()->where('name', 'auth_token')->first()->created_at->format('Y-m-d H:i:s')
        ];
    }

    private function cacheUserResource($user)
    {
        return Cache::remember("user_{$user->id}_resource", 43200, function () use ($user) {
            return new UserResource($user);
        });
    }
}