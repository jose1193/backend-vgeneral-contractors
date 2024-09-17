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
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMailWithCredentials;
use App\Jobs\SendWelcomeEmailWithCredentials;

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

    public function getUsersByRole(string $role)
    {
        try {
        $this->userId = Auth::id();
        $this->cacheKey = 'users_' . $this->userId . '_role_' . $role . '_list';

        // Refrescar la caché si es necesario
        $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () use ($role) {
            return $this->usersRepositoryInterface->getByRole($role);
        });

        // Obtener los datos actualizados desde la caché
        $data = Cache::get($this->cacheKey);

        // Verificar si la colección está vacía
        if ($data === null || $data->isEmpty()) {
            Log::warning('No users found or cache is empty for role: ' . $role);
            return collect(); // Devolver una colección vacía en lugar de null
        }

        return $data;

        } catch (QueryException $e) {
        Log::error('Database error occurred while fetching users with role ' . $role . ': ' . $e->getMessage(), [
            'exception' => $e
        ]);
        throw $e;

        } catch (\Exception $e) {
        Log::error('Error occurred while fetching users with role ' . $role . ': ' . $e->getMessage(), [
            'exception' => $e
        ]);
        throw $e;
        }
    }


     public function updateUser(array $updateDetails, string $uuid)
{
    DB::beginTransaction();

    try {
        // Obtener el usuario actual por UUID
        $user = $this->usersRepositoryInterface->findByUuid($uuid);
        
        // Actualizar la contraseña si es necesario
        $this->updatePassword($updateDetails, $user);

        // Actualizar el usuario
        $user = $this->usersRepositoryInterface->update($updateDetails, $uuid);

        // Sincronizar roles si se han proporcionado
        if (isset($updateDetails['user_role'])) {
            $this->syncRoles($user, $updateDetails['user_role']);
        }

        // Actualizar la caché de usuarios
        $this->updateUsersCache();

        // Confirmar la transacción
        DB::commit();

        return $user;
    } catch (\Exception $ex) {
        // Revertir la transacción en caso de error
        DB::rollBack();

        throw new \Exception('Error occurred while updating user: ' . $ex->getMessage());
    }
}


    private function updatePassword(array &$input, $user)
{
    // Verificar si se ha proporcionado una nueva contraseña y si es diferente de la actual
    if (!empty($input['password']) && !Hash::check($input['password'], $user->password)) {
        // Encriptar la nueva contraseña
        $input['password'] = bcrypt($input['password']);
    } else {
        // Si la contraseña es la misma, mantener la actual
        $input['password'] = $user->password;
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
        // Generar UUID para el usuario
        $details['uuid'] = Uuid::uuid4()->toString();

        // Verificar si se ha proporcionado una contraseña, de lo contrario usar la contraseña por defecto
        $isDefaultPassword = false;
        if (!isset($details['password'])) {
            $details['password'] = 'Gc98765=';
            $isDefaultPassword = true;
        }
        
        // Almacenar la contraseña cifrada en la base de datos
        $encryptedPassword = bcrypt($details['password']);
        $details['password'] = $encryptedPassword;

        // Guardar el usuario en el repositorio
        $user = $this->usersRepositoryInterface->store($details);

        // Sincronizar roles del usuario si se han proporcionado
        if (isset($details['user_role'])) {
            $this->syncRoles($user, $details['user_role']);
        }

        // Preparar el mensaje de contraseña para el correo electrónico
        $passwordMessage = $isDefaultPassword ? 'Gc98765=' : 'password registered by user';

        // Establecer email_verified_at a la fecha y hora actual
        $user->email_verified_at = now();
        $user->save();

        // Enviar correo electrónico al usuario con las credenciales
        //SendWelcomeEmailWithCredentials::dispatch($user,$passwordMessage);
        Mail::to($user->email)->send(new WelcomeMailWithCredentials($user,$passwordMessage));

        // Actualizar la caché de usuarios
        $this->updateUsersCache();

        // Confirmar la transacción
        DB::commit();

        return $user;
    } catch (\Exception $ex) {
        // Revertir la transacción en caso de error
        DB::rollBack();

        throw new \Exception('Error occurred while creating user: ' . $ex->getMessage());
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