<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Libraries\Routingsystem;

abstract class BaseController extends Controller{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */

    protected $request;
    protected $helpers = [];
    protected $routing;
    protected $session;

    public function __construct(){
        $this->session = session();
        $this->routing = new Routingsystem();
    }

    public function initController(RequestInterface $request,ResponseInterface $response,LoggerInterface $logger){
        parent::initController(
            $request,
            $response,
            $logger
        );

        $this->request = $request;
    }
}