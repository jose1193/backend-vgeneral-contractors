<?php // app/Services/UserService.php
namespace App\Services;

use App\Interfaces\UsersRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Cache;


class UserService
{
    protected $baseController;
    protected $usersRepositoryInterface;
    protected $cacheKey;
    protected $cacheTime = 720;
    protected $userId;
    
    public function __construct(UsersRepositoryInterface $usersRepositoryInterface, BaseController $baseController)
    {
        $this->usersRepositoryInterface = $usersRepositoryInterface;
        $this->baseController = $baseController;

        
    }


   public function all()
    {
        try {
             // Configurar cacheKey y userId en el momento de la actualización de la caché
        $this->userId = Auth::id();
        $this->cacheKey = 'users_' . $this->userId . '_total_list';

            // Refrescar la caché si es necesario
            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return $this->usersRepositoryInterface->index();
            });

            // Obtener los datos actualizados desde la caché
            $data = Cache::get($this->cacheKey);

            // Verificar si la colección es válida
            if ($data === null || !is_iterable($data)) {
                Log::warning('Data fetched from cache is null or not iterable');
                return null; // O manejo de error según tu lógica
            }

            return $data;

        } catch (QueryException $e) {
            Log::error('Database error occurred while fetching users: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e; // Lanza la excepción para manejarla en el controlador si es necesario

        } catch (\Exception $e) {
            Log::error('Error occurred while fetching users: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e; // Lanza la excepción para manejarla en el controlador si es necesario
        }
    }


     public function updateUser(array $updateDetails, string $uuid)
    {
        DB::beginTransaction();

        try {
            $this->updatePassword($updateDetails);
            $user = $this->usersRepositoryInterface->update($updateDetails, $uuid);

            if (isset($updateDetails['role_id'])) {
                $this->syncRoles($user, $updateDetails['role_id']);
            }

            $this->updateUsersCache();
            DB::commit();

            return $user;

        } catch (\Exception $ex) {
            DB::rollBack();

           
            throw new \Exception('Error occurred while updating user: ' . $ex->getMessage());
        }
    }

    private function updatePassword(array &$input)
    {
        if (!empty($input['password'])) {
            // Encriptar la nueva contraseña
            $input['password'] = bcrypt($input['password']);
        }
    }

    private function syncRoles(User $user, $roleId)
    {
        // Verificar que roleId es un array o un valor único
        if (is_array($roleId)) {
            // Si es un array, sincronizar los roles
            $user->roles()->sync($roleId);
        } else {
            // Si es un solo ID, sincronizar con un solo rol
            $user->roles()->sync([$roleId]);
        }
    }

    private function updateUsersCache()
    {
        // Configurar cacheKey y userId en el momento de la actualización de la caché
        $this->userId = Auth::id();
        $this->cacheKey = 'users_' . $this->userId . '_total_list';

        if (!empty($this->cacheKey)) {
            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return User::withTrashed()->orderBy('id', 'DESC')->get();
            });
        } else {
            throw new \Exception('Invalid cacheKey provided');
        }
    }



    public function storeUser(array $details)
    {
        DB::beginTransaction();

        try {
           
             $details['uuid'] = Uuid::uuid4()->toString();

          
        $user = $this->usersRepositoryInterface->store($details);

         if (isset($details['role_id'])) {
                $this->syncRoles($user, $details['role_id']);
            }

            $this->updateUsersCache();
            DB::commit();

            return $user;

        } catch (\Exception $ex) {
            DB::rollBack();

           
            throw new \Exception('Error occurred while updating user: ' . $ex->getMessage());
        }
    }


    public function showUser(string $uuid)
{
    try {
        $cacheKey = 'user_' . $uuid;

        // Obtiene los datos del usuario desde la caché o desde la base de datos si no están en caché
        $user = $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($uuid) {
            return $this->usersRepositoryInterface->getByUuid($uuid);
        });

        
        return $user;

    } catch (\Exception $ex) {
        throw new \Exception('Error occurred while retrieving user: ' . $ex->getMessage());
    }
}


public function deleteUser(string $uuid)
{
    try {
       $user = $this->usersRepositoryInterface->delete($uuid);
         // Invalidar el caché del usuario
        $this->baseController->invalidateCache('user_' . $uuid);

           // Actualizar la caché de la lista de usuarios
            $this->updateUsersCache();

        return $user;

    } catch (\Exception $ex) {
        throw new \Exception('Error occurred while retrieving user: ' . $ex->getMessage());
    }
}


public function restoreUser(string $uuid)
{
    try {
        $user = $this->usersRepositoryInterface->restore($uuid);
         // Invalidar el caché del usuario
        $this->baseController->invalidateCache('user_' . $uuid);

           // Actualizar la caché de la lista de usuarios
            $this->updateUsersCache();
        return $user;

    } catch (\Exception $ex) {
        throw new \Exception('Error occurred while retrieving user: ' . $ex->getMessage());
    }
}

}