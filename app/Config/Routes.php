<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', '\Modules\Auth\Controllers\Auth::index');

$routes->post('/auth/signin', '\Modules\Auth\Controllers\Auth::signin');
$routes->get('/auth/logout', '\Modules\Auth\Controllers\Auth::logoutsystem');

$routes->get('/additional/welcomepage', '\Modules\Additional\Controllers\Welcomepage::index');