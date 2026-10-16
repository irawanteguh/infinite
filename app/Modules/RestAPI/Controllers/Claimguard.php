<?php

namespace Modules\RestAPI\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Modules\RestAPI\Models\ClaimGuardModel;

class Claimguard extends ResourceController{
    protected $format = 'json';
    protected $md;

    public function __construct(){
        $this->md = new ClaimGuardModel();
    }

    public function index(){
        return $this->respond([
            'status'  => 200,
            'success' => true,
            'system'  => 'CLAIMGUARD',
            'message' => 'ClaimGuard REST API is running',
            'version' => '1.0.0',
            'data'    => [
                'service' => 'Claim Intelligence & Audit System',
            ],
        ]);
    }

    public function health(){
        return $this->respond([
            'status'    => 200,
            'success'   => true,
            'system'    => 'CLAIMGUARD',
            'message'   => 'API is healthy',
            'timestamp' => date('c'),
        ]);
    }

    public function rulesclaimguard(){
        $response = [];
        $mdc = [];
        $code = ResponseInterface::HTTP_OK;
        $status = true;

        $resultdatamastermdc = $this->md->datamastermdc();

        foreach ($resultdatamastermdc as $row) {
            $mdc[] = [
                'code'    => $row->mdc_code,
                'name_en' => $row->name_en,
                'name_id' => $row->name_id
            ];
        }

        $response['MDC']=$mdc;

        $data['code'] = $code;
        $data['status']=$status;
        $data['message']='OK';
        $data['system']='Claim Guard';
        $data['timestamp'] = date('c');
        $data['response']=$response;


        return $this->respond($data, $code);
    }
}