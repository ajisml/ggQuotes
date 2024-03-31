<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('api', function($routes){
    $routes->post('pGenerate', 'Apiajax::pGenerate');
    $routes->get('listQuotes', 'Apiajax::listQuotes');
});