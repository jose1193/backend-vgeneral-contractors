<?php

namespace App\Interfaces;

use App\Models\User;

interface AuthRepositoryInterface
{
    public function getByUuid(string $uuid);
    
    /**
     * Find a user by a specific field (email or username).
     *
     * @param string $field
     * @param string $value
     * @return User|null
     */
    public function findByField(string $field, string $value): ?User;

    /**
     * Find a user by their ID.
     *
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User;

    /**
     * Update a user's information.
     *
     * @param User $user
     * @return User
     */
    public function update(User $user): User;

    /**
     * Check if an email already exists.
     *
     * @param string $email
     * @return bool
     */
    public function emailExists(string $email): bool;

    /**
     * Check if a username already exists.
     *
     * @param string $username
     * @return bool
     */
    public function usernameExists(string $username): bool;
}