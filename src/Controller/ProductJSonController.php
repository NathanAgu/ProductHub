<?php

namespace Controllers;

use Models\ProductJsonDataStore;

class ProductJsonController extends AbstractProductController
{
    protected function initStore()
    {
        $this->store = new ProductJsonDataStore();
        $params = require __DIR__ . '/../config/params.php';
        $this->baseUrl = $params['json_baseUrl'];;
    }
}