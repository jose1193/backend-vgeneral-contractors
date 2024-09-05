<?php

namespace App\Interfaces;

interface ProductRepositoryInterface
{
    public function index();
    public function getByUuid(string $uuid);
    public function store(array $data);
    public function update(array $data, $uuid);
    public function delete(string $uuid);
    public function restore($uuid);
}
