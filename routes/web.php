<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// Added to improve test coverage
$router->get('/error', 'Controller@error');

$router->group(['prefix' => 'api'], function () use ($router) {
    // products endpoints
    $router->get('/products', 'ProductsController@index');
    $router->post('/products', 'ProductsController@create');
    $router->get('/products/{id}', 'ProductsController@show');
    $router->put('/products/{id}', 'ProductsController@updateProduct');

    // auth endpoints
    $router->post('/register', 'AuthController@createUser');
    $router->post('/login', 'AuthController@login');

    // user endpoints
    $router->get('/orders', 'OrderController@index');
    $router->get('/orders/{id}', 'OrderController@getOrder');

    // user actions
    $router->post('/cart', 'CartController@addItemToCart');
    $router->get('/cart', 'CartController@getCart');
    $router->post('/cart/checkout', 'CartController@checkoutCart');

});
