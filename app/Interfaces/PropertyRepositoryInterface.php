<?php

namespace App\Interfaces;

interface PropertyRepositoryInterface
{
    public function index();
    public function getByUuid(string $uuid);
    public function store(array $data, array $customers = []);
    public function update(array $data, $uuid, array $customers = []);
    public function delete($uuid);
}
