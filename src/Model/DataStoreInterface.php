<?php
namespace Models;

interface DataStoreInterface
{
    public function getAll();
    public function getById(string $id);
    // public function getAllBrands();
    // public function getAllColors();
    // public function getAllCategories();
    // public function searchFilters(string $name = '', array $priceRange = [], array $brands = [], array $colors = [], array $categoreies = []);
    public function create(array $data);
    public function update(string $id, array $data);
    public function delete(string $id): bool;
}
