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
        $items = $request->request->all('items'); // tableau [product_id => ['quantity' => X, 'size' => 'M']]
        
        // Vérifier le format des données reçues
        // Si c'est un tableau associatif avec product_id comme clé et quantité comme valeur
        // mais que vous avez besoin de la taille, il faut adapter la structure du formulaire
        
        foreach ($items as $productId => $itemData) {
            // Gérer deux formats possibles
            if (is_array($itemData)) {
                // Format: items[product_id][quantity] et items[product_id][size]
                $qty = (int) ($itemData['quantity'] ?? 0);
                $size = $itemData['size'] ?? '';
            } else {
                // Format simple: items[product_id] = quantity (pas de taille)
                $qty = (int) $itemData;
                $size = ''; // Valeur par défaut si pas de taille
            }
            
            if ($qty <= 0) {
                // Supprimer le produit du panier si quantité <= 0
                // Note: Il faudrait aussi passer la taille pour la suppression
                if (!empty($size)) {
                    $this->store->removeItem($cartId, $productId, $size);
                } else {
                    $this->store->removeItem($cartId, $productId);
                }
            } else {
                // Ajouter ou mettre à jour la quantité avec la taille
                $this->store->addOrUpdateItem($cartId, $productId, $qty, $size);
            }
        }

        // Mettre à jour la date du panier
        $this->store->update($cartId, []);

        // Rediriger vers la page du panier
        return new RedirectResponse($this->baseUrl . "/cart/$cartId");
    }

}
