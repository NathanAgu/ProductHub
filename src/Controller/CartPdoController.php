<?php

namespace Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Models\ProductPdoDataStore;
use Models\CartPdoDataStore;

class CartPdoController extends AbstractCartController
{
    protected function initStore()
    {
        $this->store = new CartPdoDataStore();

        $params = require __DIR__ . '/../config/params.php';
        $this->baseUrl = $params['pdo_baseUrl'];
    }

    public function addProductForm($id)
    {
        // Récupérer le panier
        $cart = $this->store->getById($id);
        if (!$cart) {
            return $this->view->render('error', ['baseUrl' => $this->baseUrl, 'message' => 'Panier non trouvé']);
        }

        // Récupérer tous les produits
        $productStore = new ProductPdoDataStore('products');
        $products = $productStore->getAll();
        return $this->view->render('cart/add', [
            'baseUrl' => $this->baseUrl,
            'cart' => $cart,
            'products' => $products
        ]);
    }

    public function addProduct($id)
    {
        // Récupérer la requête globale
        $request = Request::createFromGlobals();

        $tableProducts = new ProductPdoDataStore('products');
        $allProducts = $tableProducts->getAll();
        $cart = $this->store->getById($id);
        if (!$cart) {
            return $this->view->render('error', ['baseUrl' => $this->baseUrl, 'message' => 'Panier non trouvé']);
        }
        $selected = $request->request->all('products');

        foreach ($selected as $productId => $data) {
            if ($data["checked"] == 1) {
                if (intval($data["quantity"]) <= $allProducts[$productId]["stock"]) {
                    $this->store->addItem($id, $productId, intval($data["quantity"]));
                }
            }
        }
        return new RedirectResponse($this->baseUrl . "/cart/$id");
    }
}
