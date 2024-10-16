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

    public function findById(int $id)
    {
        return Property::findOrFail($id);
    }


     public function store(array $data, array $customers = [])
    {
        return DB::transaction(function () use ($data, $customers) {
            $property = Property::create($data);

            if (!empty($customers)) {
                foreach ($customers as $index => $customerId) {
                    $role = $this->determineRole($index);
                    $property->customers()->attach($customerId, ['role' => $role]);
                }
            }

            return $property;
        });
    }

    public function update(array $data, $uuid, array $customers = [])
    {
        return DB::transaction(function () use ($data, $uuid, $customers) {
            $property = $this->getByUuid($uuid);

            if (!$property) {
                throw new ModelNotFoundException("Property not found with UUID: {$uuid}");
            }

            $property->update($data);

            // Detach existing customers
            $property->customers()->detach();

            // Attach new customers with appropriate roles
            if (!empty($customers)) {
                foreach ($customers as $index => $customerId) {
                    $role = $this->determineRole($index);
                    $property->customers()->attach($customerId, ['role' => $role]);
                }
            }

            return $property;
        });
    }


   public function delete($uuid)
    {
        $property = Property::where('uuid', $uuid)->firstOrFail();

        return $property->delete();
    }

     private function determineRole(int $index): string
    {
        switch ($index) {
            case 0:
                return 'owner';
            case 1:
                return 'co-owner';
            default:
                return 'additional-signer';
        }
    }
}
