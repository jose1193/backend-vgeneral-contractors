<?php // app/Services/TypeDamageService.php
namespace App\Services;

use App\Interfaces\TypeDamageRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BaseController;
use App\Models\TypeDamage;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\QueryException;

class TypeDamageService
{
    protected $baseController;
    protected $typeDamageRepositoryInterface;
    protected $cacheKey;
    protected $cacheTime = 720;

    public function __construct(TypeDamageRepositoryInterface $typeDamageRepositoryInterface, BaseController $baseController)
    {
        $this->typeDamageRepositoryInterface = $typeDamageRepositoryInterface;
        $this->baseController = $baseController;
    }

    public function all()
    {
        try {
            $this->cacheKey = 'type_damages_total_list';

            // Refrescar la caché si es necesario
            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return $this->typeDamageRepositoryInterface->index();
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
            Log::error('Database error occurred while fetching type damages: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e; // Lanza la excepción para manejarla en el controlador si es necesario
        } catch (\Exception $e) {
            Log::error('Error occurred while fetching type damages: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e; // Lanza la excepción para manejarla en el controlador si es necesario
        }
    }

    public function updateTypeDamage(array $updateDetails, string $uuid)
    {
        DB::beginTransaction();

        try {
            $typeDamage = $this->typeDamageRepositoryInterface->update($updateDetails, $uuid);

            $this->updateTypeDamagesCache();
            DB::commit();

            return $typeDamage;
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception('Error occurred while updating type damage: ' . $ex->getMessage());
        }
    }

    private function updateTypeDamagesCache()
    {
        $this->cacheKey = 'type_damages_total_list';

        if (!empty($this->cacheKey)) {
            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return TypeDamage::orderBy('id', 'DESC')->get();
            });
        } else {
            throw new \Exception('Invalid cacheKey provided');
        }
    }

    public function storeTypeDamage(array $details)
    {
        DB::beginTransaction();

        try {
            $details['uuid'] = Uuid::uuid4()->toString();

            $typeDamage = $this->typeDamageRepositoryInterface->store($details);

            $this->updateTypeDamagesCache();
            DB::commit();

            return $typeDamage;
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception('Error occurred while storing type damage: ' . $ex->getMessage());
        }
    }

    public function showTypeDamage(string $uuid)
    {
        try {
            $cacheKey = 'type_damage_' . $uuid;

            // Obtiene los datos del type damage desde la caché o desde la base de datos si no están en caché
            $typeDamage = $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($uuid) {
                return $this->typeDamageRepositoryInterface->getByUuid($uuid);
            });

            return $typeDamage;
        } catch (\Exception $ex) {
            throw new \Exception('Error occurred while retrieving type damage: ' . $ex->getMessage());
        }
    }

    public function deleteTypeDamage(string $uuid)
    {
        try {
            $typeDamage = $this->typeDamageRepositoryInterface->delete($uuid);

            // Invalidar el caché del type damage
            $this->baseController->invalidateCache('type_damage_' . $uuid);

            // Actualizar la caché de la lista de type damages
            $this->updateTypeDamagesCache();

            return $typeDamage;
        } catch (\Exception $ex) {
            throw new \Exception('Error occurred while deleting type damage: ' . $ex->getMessage());
        }
    }
}
