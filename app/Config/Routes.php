<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/infinite', '\Modules\Auth\Controllers\Auth::index');
