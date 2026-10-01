<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get(
    '/',
    '\Modules\Auth\Controllers\Auth::index'
);

$routes->post(
    'auth/signin',
    '\Modules\Auth\Controllers\Auth::signin'
);

$routes->get(
    'auth/logout',
    '\Modules\Auth\Controllers\Auth::logoutsystem'
);


/*
|--------------------------------------------------------------------------
| GET - Module / Controller
|--------------------------------------------------------------------------
| Contoh:
| /developer/testingpage
| /farmasi/masterobat
*/
$routes->get(
    '(:segment)/(:segment)',
    function ($module, $controller) {

        $class = ucfirst($controller);

        $namespace =
            'Modules\\' .
            ucfirst($module) .
            '\\Controllers\\' .
            $class;

        if (!class_exists($namespace)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $instance = new $namespace();

        if (!method_exists($instance, 'index')) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $instance->index();
    }
);


/*
|--------------------------------------------------------------------------
| GET - Module / Controller / Method
|--------------------------------------------------------------------------
| Contoh:
| /developer/backupdb/backup
| /developer/backupdb/listbackup
| /farmasi/masterobat/masterobat
*/
$routes->get(
    '(:segment)/(:segment)/(:segment)',
    function (
        $module,
        $controller,
        $method
    ) {

        $class = ucfirst($controller);

        $namespace =
            'Modules\\' .
            ucfirst($module) .
            '\\Controllers\\' .
            $class;

        if (!class_exists($namespace)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $instance = new $namespace();

        if (!method_exists($instance, $method)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $instance->$method();
    }
);


/*
|--------------------------------------------------------------------------
| GET - Module / Controller / Method / Parameter
|--------------------------------------------------------------------------
| Contoh:
| /developer/backupdb/download/infinite_backup_20261001_101713.sql
|
| Digunakan untuk:
| Backupdb::download($filename)
*/
$routes->get(
    '(:segment)/(:segment)/(:segment)/(:any)',
    function (
        $module,
        $controller,
        $method,
        $parameter
    ) {

        $class = ucfirst($controller);

        $namespace =
            'Modules\\' .
            ucfirst($module) .
            '\\Controllers\\' .
            $class;

        if (!class_exists($namespace)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $instance = new $namespace();

        if (!method_exists($instance, $method)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $instance->$method($parameter);
    }
);


/*
|--------------------------------------------------------------------------
| POST - Module / Controller / Method
|--------------------------------------------------------------------------
| Contoh:
| /farmasi/masterobat/masterobat
*/
$routes->post(
    '(:segment)/(:segment)/(:segment)',
    function (
        $module,
        $controller,
        $method
    ) {

        $class = ucfirst($controller);

        $namespace =
            'Modules\\' .
            ucfirst($module) .
            '\\Controllers\\' .
            $class;

        if (!class_exists($namespace)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $instance = new $namespace();

        if (!method_exists($instance, $method)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $instance->$method();
    }
);