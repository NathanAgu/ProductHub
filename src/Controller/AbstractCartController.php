<?php

namespace Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Util\View;

abstract class AbstractCartController
{
    protected $store;
    protected $view;
    protected $baseUrl;

    public function __construct()
    {
        $this->view = new View();
        $this->initStore();
    }

    abstract protected function initStore();

    public function index()
    {
        $carts = $this->store->getAll();
        return $this->view->render('cart/list', [
            'baseUrl' => $this->baseUrl,
            'carts'   => $carts
        ]);
    }

    public function show($id)
    {
        $cart = $this->store->getById($id);
        if (!$cart) {
            return $this->view->render('cart/error', [
                'baseUrl' => $this->baseUrl,
                'message' => 'Panier introuvable'
            ]);
        }

        return $this->view->render('cart/show', [
            'baseUrl' => $this->baseUrl,
            'cart'    => $cart
        ]);
    }

    public function create()
    {
        $cart = $this->store->create([]);
        return new RedirectResponse($this->baseUrl . '/cart/' . $cart['id']);
    }

    public function delete($id)
    {
        $this->store->delete($id);
        return new RedirectResponse($this->baseUrl . '/cart');
    }

    public function edit($id)
    {
        $cart = $this->store->getById($id);
        if (!$cart) {
            return $this->view->render('cart/error', ['baseUrl' => $this->baseUrl, 'message' => 'Panier non trouvé']);
        }
        return $this->view->render('cart/edit', ['baseUrl' => $this->baseUrl, 'cart' => $cart]);
    }

    public function update(Request $request, $cartId)
    {
        // Récupérer le panier

        $cart = $this->store->getById($cartId);
        if (!$cart) {
            return $this->view->render('cart/error', [
                'baseUrl' => $this->baseUrl,
                'message' => 'Panier introuvable'
            ]);
        }

        // Récupérer les données POST
        $items = $request->request->all('items'); // tableau [product_id => quantity]

        foreach ($items as $productId => $qty) {
            $qty = (int) $qty;
            if ($qty <= 0) {
                // Supprimer le produit du panier si quantité <= 0
                $this->store->removeItem($cartId, $productId);
            } else {
                // Ajouter ou mettre à jour la quantité
                $this->store->addOrUpdateItem($cartId, $productId, $qty);
            }
        }

        // Mettre à jour la date du panier
        $this->store->update($cartId, []);

        // Rediriger vers la page du panier
        return new RedirectResponse($this->baseUrl . "/cart/$cartId");
    }

}
