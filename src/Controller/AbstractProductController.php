<?php


namespace Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Util\View;

abstract class AbstractProductController
{
    protected $store;
    protected $view;
    protected $baseUrl;

    public function __construct()
    {
        $this->view = new View();
        $this->initStore();
    }

    // Méthode abstraite à implémenter dans les classes enfants
    abstract protected function initStore();

    public function index(Request $request = null)
    {
        //Récupération du paramètre de la méthode GET (recherche par nom)
        $search = '';
        if ($request instanceof Request) {
            $search = $request->query->get('search', '');
        } elseif (isset($_GET['search'])) {
            $search = $_GET['search'];
        }

        if (!empty($search)) {
            $products = $this->store->getByName($search);
        } else {
            $products = $this->store->getAll();
        }

        return $this->view->render('product/list', ['baseUrl' => $this->baseUrl, 'products' => $products]);
    }
    
    public function create()
    {
        return $this->view->render('product/create', ['baseUrl' => $this->baseUrl]);
    }

    public function store(Request $request)
    {
        $name = $request->request->get('name');
        $description = $request->request->get('description');
        $price = $request->request->get('price');
        $stock = $request->request->get('stock');

        if (empty($name)) {
            return $this->view->render('product/create', ['baseUrl' => $this->baseUrl, 'error' => 'Le nom est obligatoire']);
        }

        $this->store->create([
            'name' => $name,
            'description' => $description,
            'price' => floatval($price),
            'stock' => intval($stock)
        ]);

        return new RedirectResponse($this->baseUrl . '/product');
    }

    public function edit($id)
    {
        $product = $this->store->getById($id);
        if (!$product) {
            return $this->view->render('product/error', ['baseUrl' => $this->baseUrl, 'message' => 'Produit non trouvé']);
        }
        return $this->view->render('product/edit', ['baseUrl' => $this->baseUrl, 'product' => $product]);
    }

    public function update(Request $request, $id)
    {
        $name = $request->request->get('name');
        $description = $request->request->get('description');
        $price = $request->request->get('price');
        $stock = $request->request->get('stock');

        $product = $this->store->update($id, [
            'name' => $name,
            'description' => $description,
            'price' => floatval($price),
            'stock' => intval($stock)
        ]);

        if (!$product) {
            return $this->view->render('product/error', ['baseUrl' => $this->baseUrl, 'message' => 'Produit non trouvé']);
        }

        return new RedirectResponse($this->baseUrl . '/product');
    }

    public function delete($id)
    {
        $this->store->delete($id);
        return new RedirectResponse($this->baseUrl . '/product');
    }
}