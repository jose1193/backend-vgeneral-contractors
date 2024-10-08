<?php

namespace App\Repositories;
use App\Models\InsuranceCompany;
use App\Models\User;

use App\Interfaces\InsuranceCompanyRepositoryInterface;

class InsuranceCompanyRepository implements InsuranceCompanyRepositoryInterface
    {
        public function index()
            {
            return InsuranceCompany::orderBy('id', 'DESC')->get();
        }

        public function getByUuid(string $uuid)
        {
        return InsuranceCompany::where('uuid', $uuid)->firstOrFail();
        }

        public function findByName(string $name): ?object
        {
            return InsuranceCompany::where('insurance_company_name', $name)->first();
        }

        public function isSuperAdmin(int $userId): bool
        {
        return User::findOrFail($userId)->hasRole('Super Admin');
        }

        public function store(array $data)
        {
        return InsuranceCompany::create($data);
        }

        public function update(array $data, string $uuid)
        {
            
        $insuranceCompany = $this->getByUuid($uuid);
        $insuranceCompany->update($data);
        return $insuranceCompany;
        }

        public function delete(string $uuid)
        {
        $data = InsuranceCompany::where('uuid', $uuid)->firstOrFail();
        $data->delete();
        return $data;
        }
    }
