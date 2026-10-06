<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */


/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
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
| UR - Legacy Controller
|--------------------------------------------------------------------------
| Controller:
| app/Controllers/Ur.php
|
| URL:
| POST /ur/importtxteklaim
*/

$routes->post(
    'ur/importtxteklaim',
    '\App\Controllers\Ur::importtxteklaim'
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
| /developer/backupdb/download/file.sql
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


/*
|--------------------------------------------------------------------------
| DELETE - Module / Controller / Method
|--------------------------------------------------------------------------
| Contoh:
| DELETE /developer/backupdb/delete
| DELETE /farmasi/masterobat/delete
*/

$routes->delete(
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
| DELETE - Module / Controller / Method / Parameter
|--------------------------------------------------------------------------
| Contoh:
| DELETE /developer/backupdb/delete/file.sql
*/

$routes->delete(
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