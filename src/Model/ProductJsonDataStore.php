<?php
namespace Models;

use Models\DataStoreInterface;

class ProductJsonDataStore implements DataStoreInterface
{
    private $file;

    public function __construct($filename = 'data.json')
    {
        $this->file = __DIR__ . '/' . $filename;
        if (!file_exists($this->file)) {
            file_put_contents($this->file, json_encode([]));
        }
    }

    public function getAll()
    {
        $content = file_get_contents($this->file);
        return json_decode($content, true) ?: [];
    }

    public function getById($id)
    {
        $items = $this->getAll();
        return $items[$id] ?? null;
    }

    public function create($data)
    {
        $items = $this->getAll();
        $id = uniqid();
        $items[$id] = array_merge(['id' => $id, 'created_at' => date('Y-m-d H:i:s')], $data);
        $this->save($items);
        return $items[$id];
    }

    public function update($id, $data):array
    {
        $items = $this->getAll();
        if (!isset($items[$id])) {
            return [];
        }
        $items[$id] = array_merge($items[$id], $data, ['updated_at' => date('Y-m-d H:i:s')]);
        $this->save($items);
        return $items[$id];
    }

    public function delete($id):bool
    {
        $items = $this->getAll();
        if (!isset($items[$id])) {
            return false;
        }
        unset($items[$id]);
        $this->save($items);
        return true;
    }

    private function save($data)
    {
        file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT));
    }
}