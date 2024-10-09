<?php

namespace App\Repositories;

use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Interfaces\UsersRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UsersRepository implements UsersRepositoryInterface
{
    /**
     * Retrieve all users.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function index(): Collection
    {
        return User::withTrashed()->orderBy('id', 'DESC')->get();
    }

    /**
     * Find a user by UUID.
     *
     * @param  string  $uuid
     * @return \App\Models\User
     */
    public function getByUuid(string $uuid): object
    {
        return User::where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Create a new user.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    public function store(array $data): object
    {
        return User::create($data);
    }

    /**
     * Update a user by UUID.
     *
     * @param  array   $data
     * @param  string  $uuid
     * @return bool
     */
    public function update(array $data, string $uuid): object
    {
        $user = $this->getByUuid($uuid);
        $user->update($data);
        return $user;
    }


    /**
     * Delete a user by UUID.
     *
     * @param  string  $uuid
     * @return bool|null
     */
    public function delete(string $uuid): bool
    {
        $user = $this->getByUuid($uuid);
       

        return $user->delete();
    }

    public function restore(string $uuid): object
    {
        
        
        $user = User::withTrashed()->where('uuid', $uuid)->firstOrFail();
        if (!$user->trashed()) {
            throw new \Exception('User already restored');
        }

        $user->restore();

        return $user;
    }

     public function getByRole(string $role)
    {
        // Utiliza el método de Spatie para obtener usuarios por rol
         return User::role($role, 'api')->get();
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    
    public function isSuperAdmin(int $userId): bool
    {
    return User::findOrFail($userId)->hasRole('Super Admin');
    }

    public function getAllSuperAdmins(): Collection
    {
        return User::whereHas('roles', function ($query) {
            $query->where('name', 'Super Admin');
        })->get();
    }


    public function getAllRoles(): Collection
    {
        return Role::orderBy('id', 'DESC')->get();
    }
}
