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
        $request = Request::createFromGlobals();
        $tableProducts = new ProductPdoDataStore('products');
        $allProducts = $tableProducts->getAll();
        $cart = $this->store->getById($id);
        
        if (!$cart) {
            return $this->view->render('error', [
                'baseUrl' => $this->baseUrl, 
                'message' => 'Panier non trouvé'
            ]);
        }
        
        $selected = $request->request->all('products');

        foreach ($selected as $productId => $data) {
            if (isset($data["checked"]) && $data["checked"] == 1) {
                $quantity = intval($data["quantity"] ?? 1);
                $size = $data["size"] ?? ''; // Récupérer la taille
                
                if ($quantity > 0 && $quantity <= $allProducts[$productId]["stock"]) {
                    // ✅ Passer la taille à addItem
                    $this->store->addItem($id, $productId, $quantity, $size);
                }
            }
        }
        
        return new RedirectResponse($this->baseUrl . "/cart/$id");
    }

    public function removeProduct($cartId, $productId, Request $request)
    {
        // Récupérer la taille depuis la requête GET
        $size = $request->query->get('size', '');
        
        // Récupérer le panier pour vérifier qu'il existe
        $cart = $this->store->getById($cartId);
        if (!$cart) {
            return $this->view->render('error', [
                'baseUrl' => $this->baseUrl, 
                'message' => 'Panier non trouvé'
            ]);
        }
        
        // Supprimer le produit du panier
        $this->store->removeItem($cartId, $productId, $size);
        
        // Rediriger vers la page d'édition du panier
        return new RedirectResponse($this->baseUrl . "/cart/$cartId/edit");
    }
}
