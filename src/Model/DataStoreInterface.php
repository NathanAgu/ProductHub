<?php
namespace Models;

interface DataStoreInterface
{
    public function getAll();
    public function getById(string $id);
    public function getAllBrands();
    public function getAllColors();
    public function searchFilters(string $name = '', array $priceRange = [], array $brands = [], array $colors = []);
    public function create(array $data);
    public function update(string $id, array $data);
    public function delete(string $id): bool;
}
