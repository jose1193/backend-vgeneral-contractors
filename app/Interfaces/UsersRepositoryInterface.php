<?php

namespace App\Interfaces;

interface UsersRepositoryInterface
{
    public function index();
    public function getByUuid($uuid);
    public function store(array $data);
    public function update(array $data, $uuid);
    public function delete($uuid);
    public function restore($uuid);
}
