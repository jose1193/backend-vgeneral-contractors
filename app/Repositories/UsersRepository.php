<?php

namespace App\Repositories;

use App\Models\User;
use App\Interfaces\UsersRepositoryInterface;

class UsersRepository implements UsersRepositoryInterface
{
    /**
     * Retrieve all users.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function index()
    {
        return User::withTrashed()->orderBy('id', 'DESC')->get();
    }

    /**
     * Find a user by UUID.
     *
     * @param  string  $uuid
     * @return \App\Models\User
     */
    public function getByUuid($uuid)
    {
        return User::where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Create a new user.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    public function store(array $data)
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
    public function update(array $data, $uuid)
    {
       
        $user = User::where('uuid', $uuid)->firstOrFail();
        $user->update($data);

        
        return $user;
    }

    /**
     * Delete a user by UUID.
     *
     * @param  string  $uuid
     * @return bool|null
     */
    public function delete($uuid)
    {
        $user = User::where('uuid', $uuid)->firstOrFail();
       

        return $user->delete();
    }

    public function restore($uuid)
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



}
