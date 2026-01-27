<?php

namespace Controllers;

use Models\ProductPdoDataStore;

class ProductPdoController extends AbstractProductController
{
    protected function initStore()
    {
        $this->store = new ProductPdoDataStore('products');

        $params = require __DIR__ . '/../config/params.php';
        $this->baseUrl = $params['pdo_baseUrl'];;
    }
}