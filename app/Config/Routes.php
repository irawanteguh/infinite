<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// =====================================================
// AUTH
// =====================================================

// Halaman Login
$routes->get(
    '/',
    '\Modules\Auth\Controllers\Auth::index'
);

// Proses Login
$routes->post(
    '/auth/signin',
    '\Modules\Auth\Controllers\Auth::signin'
);

// Logout
$routes->get(
    '/auth/logout',
    '\Modules\Auth\Controllers\Auth::logoutsystem'
);


// =====================================================
// ADDITIONAL
// =====================================================

// Welcome Page
$routes->get(
    '/additional/welcomepage',
    '\Modules\Additional\Controllers\Welcomepage::index'
);
