<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', '\Modules\Auth\Controllers\Auth::index');

$routes->post('auth/signin', '\Modules\Auth\Controllers\Auth::signin');
$routes->get('auth/logout', '\Modules\Auth\Controllers\Auth::logoutsystem');

$routes->get('(:segment)/(:segment)', function ($module, $controller) {
    $class = ucfirst($controller);
    $namespace = 'Modules\\' . ucfirst($module) . '\\Controllers\\' . $class;

    if (!class_exists($namespace)) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    $instance = new $namespace();

    if (!method_exists($instance, 'index')) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    return $instance->index();
});

$routes->post('(:segment)/(:segment)/(:segment)', function ($module, $controller, $method) {
    $class = ucfirst($controller);
    $namespace = 'Modules\\' . ucfirst($module) . '\\Controllers\\' . $class;

    if (!class_exists($namespace)) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    $instance = new $namespace();

    if (!method_exists($instance, $method)) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    return $instance->$method();
});