<?php

namespace App\Repositories;

use App\Models\CauseOfLoss;
use App\Models\User;
use App\Interfaces\CauseOfLossRepositoryInterface;

class CauseOfLossRepository implements CauseOfLossRepositoryInterface
{
    public function index()
    {
        return CauseOfLoss::orderBy('id', 'DESC')->get();
    }

    public function getByUuid(string $uuid)
    {
        return CauseOfLoss::where('uuid', $uuid)->firstOrFail();
    }

    public function findByName(string $findName)
    {
        return CauseOfLoss::where('cause_loss_name', $findName)->first();
    }

    public function isSuperAdmin(int $userId): bool
    {
        return User::findOrFail($userId)->hasRole('Super Admin');
    }

    public function store(array $data)
    {
        return CauseOfLoss::create($data);
    }

    public function update(array $data, string $uuid)
    {
        $causeOfLoss = $this->getByUuid($uuid);
        $causeOfLoss->update($data);
        return $causeOfLoss;
    }

    public function delete(string $uuid)
    {
        $causeOfLoss = $this->getByUuid($uuid);
        $causeOfLoss->delete();
        return $causeOfLoss;
    }
}
