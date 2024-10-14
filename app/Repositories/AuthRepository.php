<?php

namespace App\Repositories;

use App\Interfaces\AuthRepositoryInterface;
use App\Models\User;

class AuthRepository implements AuthRepositoryInterface
{
    /**
     * Find a user by a specific field (email or username).
     *
     * @param string $field
     * @param string $value
     * @return User|null
     */
    public function findByField(string $field, string $value): ?User
    {
        return User::where($field, $value)->first();
    }

    /**
     * Find a user by their ID.
     *
     * @param int $id
     * @return User|null
     */
    public function getByUuid(string $uuid)
    {
        return User::where('uuid', $uuid)->firstOrFail();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Update a user's information.
     *
     * @param User $user
     * @return User
     */
    public function update(User $user): User
    {
        $user->save();
        return $user;
    }

    /**
     * Check if an email already exists.
     *
     * @param string $email
     * @return bool
     */
    public function emailExists(string $email): bool
    {
        return User::where('email', $email)->exists();
    }

    /**
     * Check if a username already exists.
     *
     * @param string $username
     * @return bool
     */
    public function usernameExists(string $username): bool
    {
        return User::where('username', $username)->exists();
    }
}