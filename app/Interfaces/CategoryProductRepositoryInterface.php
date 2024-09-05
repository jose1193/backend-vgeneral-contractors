<?php

namespace App\Interfaces;

interface CategoryProductRepositoryInterface
{
    public function index();
    public function getByUuid(string $uuid);
    public function store(array $data);
    public function update(array $data, $uuid);
    public function delete($uuid);
}
