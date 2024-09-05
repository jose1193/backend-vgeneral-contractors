<?php

namespace App\Repositories;
use App\Models\Property;
use App\Interfaces\PropertyRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PropertyRepository implements PropertyRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function index(){
        return Property::orderBy('id', 'DESC')->get();
    }

     public function getByUuid(string $uuid)
    {
        return Property::where('uuid', $uuid)->firstOrFail();
    }

     public function store(array $data, array $customers = [])
    {
        \DB::beginTransaction();

        try {
            $property = Property::create($data);

            if (!empty($customers)) {
                $property->customers()->attach($customers[0], ['role' => 'owner']);
                if (isset($customers[1])) {
                    $property->customers()->attach($customers[1], ['role' => 'co-owner']);
                }
            }

            \DB::commit();
            return $property;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    public function update(array $data, $uuid, array $customers = [])
    {
        \DB::beginTransaction();

        try {
            $property = $this->getByUuid($uuid);

            if (!$property) {
                throw new ModelNotFoundException("Property not found with UUID: {$uuid}");
            }

            $property->update($data);

            // Detach existing customers
            $property->customers()->detach();

            // Attach new customers
            if (!empty($customers)) {
                $property->customers()->attach($customers[0], ['role' => 'owner']);
                if (isset($customers[1])) {
                    $property->customers()->attach($customers[1], ['role' => 'co-owner']);
                }
            }

            \DB::commit();
            return $property;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }


   public function delete($uuid)
    {
        $property = Property::where('uuid', $uuid)->firstOrFail();

        return $property->delete();
    }
}
