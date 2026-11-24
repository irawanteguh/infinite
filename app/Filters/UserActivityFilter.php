<?php

namespace App\Filters;

use App\Libraries\UserActivity;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class UserActivityFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if (session_status() !== PHP_SESSION_ACTIVE && !$request->getCookie(config('Session')->cookieName)) {
            return;
        }

        $route = service('router')->getMatchedRoute()[0] ?? '';
        if ($route === 'auth/signin' || $route === 'auth/logout') {
            return;
        }

        $isRead = in_array(strtoupper($request->getMethod()), ['GET', 'HEAD'], true);
        $path = strtolower(implode('/', $request->getUri()->getSegments()));
        if ($isRead && ($path === 'developer/userlog' || $path === 'developer/userlog/index')) {
            return;
        }

        $event = 'request';
        if ($response instanceof DownloadResponse) {
            $event = 'download';
        } elseif ($isRead) {
            $event = !$request->isAJAX() && stripos($response->getHeaderLine('Content-Type'), 'text/html') !== false
                ? 'page' : 'read';
        }
        UserActivity::record($request, $event, $response->getStatusCode());
    }
}
