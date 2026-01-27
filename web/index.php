<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Controllers\ProductJSonController;
use Controllers\ProductPdoController;

$params = require __DIR__ . '/../src/config/params.php';
$pdo_baseUrl = $params['pdo_baseUrl'];;
$json_baseUrl = $params['json_baseUrl'];;

// Configuration des routes
$routes = new RouteCollection();

// ============================================
// Routes avec ProductJsonDataStore
// ============================================

$routes->add('json_product_list', new Route($json_baseUrl.'/product', [
    '_controller' => [ProductJSonController::class, 'index']
], [], [], '', [], ['GET']));

$routes->add('json_product_create', new Route($json_baseUrl.'/product/create', [
    '_controller' => [ProductJSonController::class, 'create']
], [], [], '', [], ['GET']));

$routes->add('json_product_store', new Route($json_baseUrl.'/product/store', [
    '_controller' => [ProductJSonController::class, 'store']
], [], [], '', [], ['POST']));

$routes->add('json_product_edit', new Route($json_baseUrl.'/product/{id}/edit', [
    '_controller' => [ProductJSonController::class, 'edit']
], [], [], '', [], ['GET']));

$routes->add('json_product_update', new Route($json_baseUrl.'/product/{id}/update', [
    '_controller' => [ProductJSonController::class, 'update']
], [], [], '', [], ['POST']));

$routes->add('json_product_delete', new Route($json_baseUrl.'/product/{id}/delete', [
    '_controller' => [ProductJSonController::class, 'delete']
], [], [], '', [], ['POST']));

$routes->add('home', new Route('/', [
    '_controller' => function() {
        return new RedirectResponse($json_baseUrl.'/product');
    }
]));

// ============================================
// Routes avec ProductPdoDataStore
// ============================================

$routes->add('pdo_product_list', new Route($pdo_baseUrl.'/product', [
    '_controller' => [ProductPdoController::class, 'index']
], [], [], '', [], ['GET']));

$routes->add('pdo_product_create', new Route($pdo_baseUrl.'/product/create', [
    '_controller' => [ProductPdoController::class, 'create']
], [], [], '', [], ['GET']));

$routes->add('pdo_product_store', new Route($pdo_baseUrl.'/product/store', [
    '_controller' => [ProductPdoController::class, 'store']
], [], [], '', [], ['POST']));

$routes->add('pdo_product_edit', new Route($pdo_baseUrl.'/product/{id}/edit', [
    '_controller' => [ProductPdoController::class, 'edit']
], [], [], '', [], ['GET']));

$routes->add('pdo_product_update', new Route($pdo_baseUrl.'/product/{id}/update', [
    '_controller' => [ProductPdoController::class, 'update']
], [], [], '', [], ['POST']));

$routes->add('pdo_product_delete', new Route($pdo_baseUrl.'/product/{id}/delete', [
    '_controller' => [ProductPdoController::class, 'delete']
], [], [], '', [], ['POST']));


//---


use Controllers\CartPdoController;

/* ============================================
 * LISTE DES PANIERS
 * ============================================ */

$routes->add('pdo_cart_add_form', new Route(
    $pdo_baseUrl . '/cart/{id}/add',
    ['_controller' => [CartPdoController::class, 'addProductForm']],
    [],
    [],
    '',
    [],
    ['GET']
));

$routes->add('pdo_cart_add_product', new Route(
    $pdo_baseUrl . '/cart/{id}/add',
    ['_controller' => [CartPdoController::class, 'addProduct']],
    [],
    [],
    '',
    [],
    ['POST']
));

$routes->add('pdo_cart_list', new Route(
    $pdo_baseUrl . '/cart',
    ['_controller' => [CartPdoController::class, 'index']],
    [],
    [],
    '',
    [],
    ['GET']
));

/* ============================================
 * CRÉATION D’UN PANIER
 * ============================================ */
$routes->add('pdo_cart_create', new Route(
    $pdo_baseUrl . '/cart/create',
    ['_controller' => [CartPdoController::class, 'create']],
    [],
    [],
    '',
    [],
    ['POST']
));

/* ============================================
 * AFFICHAGE D’UN PANIER
 * ============================================ */
$routes->add('pdo_cart_show', new Route(
    $pdo_baseUrl . '/cart/{id}',
    ['_controller' => [CartPdoController::class, 'show']],
    [],
    [],
    '',
    [],
    ['GET']
));

/* ============================================
 * AJOUT D’UN PRODUIT AU PANIER
 * ============================================ */
$routes->add('pdo_cart_add_product', new Route(
    $pdo_baseUrl . '/cart/{id}/add',
    ['_controller' => [CartPdoController::class, 'addProduct']],
    [],
    [],
    '',
    [],
    ['POST','GET']
));

/* ============================================
 * SUPPRESSION D’UN PANIER
 * ============================================ */
$routes->add('pdo_cart_delete', new Route(
    $pdo_baseUrl . '/cart/{id}/delete',
    ['_controller' => [CartPdoController::class, 'delete']],
    [],
    [],
    '',
    [],
    ['POST']
));

/* ============================================
 * EDITION D’UN PANIER
 * ============================================ */

$routes->add('pdo_cart_edit', new Route($pdo_baseUrl.'/cart/{id}/edit', [
    '_controller' => [CartPdoController::class, 'edit']
], [], [], '', [], ['GET']));

$routes->add('pdo_cart_update', new Route($pdo_baseUrl.'/cart/{id}/update', [
    '_controller' => [CartPdoController::class, 'update']
], [], [], '', [], ['POST']));

// Traitement de la requête
$request = Request::createFromGlobals();
$context = new RequestContext();
$context->fromRequest($request);

$matcher = new UrlMatcher($routes, $context);

try {
    $parameters = $matcher->match($request->getPathInfo());
    //print_r($parameters);die;
    $controller = $parameters['_controller'];
    unset($parameters['_controller'], $parameters['_route']);

    if (is_callable($controller)) {
        $response = $controller();
    } else {
        $controllerInstance = new $controller[0]();
        $method = $controller[1];

        // Préparer les arguments selon la méthode
        if (in_array($method, ['store', 'update'])) {
            // Ces méthodes nécessitent Request en premier paramètre
            $args = array_merge([$request], array_values($parameters));

        } else {
            // Ces méthodes (index, create, edit, delete) ne prennent pas Request
            $args = array_values($parameters);
        }

        $response = call_user_func_array([$controllerInstance, $method], $args);
    }

} catch (ResourceNotFoundException $e) {
    $response = new Response('<h1>404 - Page non trouvée</h1>', 404);
} catch (Exception $e) {
    print_r($e);die;

    $response = new Response('<h1>Erreur: ' . htmlspecialchars($e->getMessage()) . '</h1>', 500);
}

$response->send();