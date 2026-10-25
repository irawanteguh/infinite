
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

$routes->get('/', '\Modules\Auth\Controllers\Auth::index');
$routes->post('auth/signin', '\Modules\Auth\Controllers\Auth::signin');
$routes->get('auth/logout', '\Modules\Auth\Controllers\Auth::logoutsystem');

/*
|--------------------------------------------------------------------------
| ClaimGuard REST API
|--------------------------------------------------------------------------
| Harus diletakkan sebelum route dinamis HMVC.
*/

$routes->group('api/v1', [
    'namespace' => 'Modules\\RestAPI\\Controllers',
], static function ($routes) {

    // ClaimGuard
    $routes->post('claimguard/rulesclaimguard', 'Claimguard::evaluate');
    $routes->get('claimguard/request-template', 'Claimguard::requestTemplate');
    $routes->get('claimguard/request-presets', 'Claimguard::requestPresets');
    $routes->get('claimguard/request-body/(:num)', 'Claimguard::requestBody/$1');
    $routes->get('claimguard/export-collection', 'Claimguard::exportPostmanCollection');

    // Dynamic API Router
    $routes->match(
        ['get', 'post', 'put', 'patch', 'delete'],
        '(:segment)',
        'ApiRouter::dispatch/$1'
    );

    $routes->match(
        ['get', 'post', 'put', 'patch', 'delete'],
        '(:segment)/(:segment)',
        'ApiRouter::dispatch/$1/$2'
    );
});

/*
|--------------------------------------------------------------------------
| UR - Legacy Controller
|--------------------------------------------------------------------------
*/

$routes->post('ur/importtxteklaim', '\App\Controllers\Ur::importtxteklaim');
$routes->post('bahv/importtxtbahv', '\App\Controllers\Bahv::importtxtbahv');

/*
|--------------------------------------------------------------------------
| GET - Module / Controller
|--------------------------------------------------------------------------
| Contoh: /developer/testingpage
|         /farmasi/masterobat
*/

$routes->get(
    '(:segment)/(:segment)',
    static function ($module, $controller) {

        $class = ucfirst($controller);
        $namespace = 'Modules\\' . ucfirst((string) $module) . '\\Controllers\\' . $class;

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
*/

$routes->get(
    '(:segment)/(:segment)/(:segment)',
    static function ($module, $controller, $method) {

        $class = ucfirst($controller);
        $namespace = 'Modules\\' . ucfirst((string) $module) . '\\Controllers\\' . $class;

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
*/

$routes->get(
    '(:segment)/(:segment)/(:segment)/(:any)',
    static function ($module, $controller, $method, $parameter) {

        $class = ucfirst($controller);
        $namespace = 'Modules\\' . ucfirst((string) $module) . '\\Controllers\\' . $class;

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
*/

$routes->post(
    '(:segment)/(:segment)/(:segment)',
    static function ($module, $controller, $method) {

        $class = ucfirst($controller);
        $namespace = 'Modules\\' . ucfirst((string) $module) . '\\Controllers\\' . $class;

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
*/

$routes->delete(
    '(:segment)/(:segment)/(:segment)',
    static function ($module, $controller, $method) {

        $class = ucfirst($controller);
        $namespace = 'Modules\\' . ucfirst((string) $module) . '\\Controllers\\' . $class;

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
*/

$routes->delete(
    '(:segment)/(:segment)/(:segment)/(:any)',
    static function ($module, $controller, $method, $parameter) {

        $class = ucfirst($controller);
        $namespace = 'Modules\\' . ucfirst((string) $module) . '\\Controllers\\' . $class;

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
