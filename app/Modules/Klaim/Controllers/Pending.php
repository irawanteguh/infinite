<?php
namespace Modules\Klaim\Controllers;

use App\Controllers\BaseController;
use Modules\Klaim\Models\PendingModel;

class Pending extends BaseController
{
    public function index()
    {
        $periode='';foreach((new PendingModel())->periode() as $row)$periode.='<option value="'.$row->periode.'">'.$row->periode.'</option>';
        return view('template/dashboard-light-aside',['contents'=>view('Modules\Klaim\Views\v_pending',compact('periode'))]);
    }

    public function summary(){return $this->execute(static fn($model,$provider,$filters)=>['summary'=>$model->summary($provider,$filters)]);}
    public function dataraweklaim(){return $this->execute(static fn($model,$provider,$filters)=>['responseResult'=>$model->aggregate($provider,$filters)]);}
    public function dataalgoritmatype(){return $this->execute(static fn($model,$provider,$filters)=>['responseResult'=>$model->types($provider,$filters)]);}
    public function dataalgoritmabreakdown(){return $this->execute(static fn($model,$provider,$filters)=>['responseResult'=>$model->algorithms($provider,$filters)]);}
    public function dataraweklaimdetail()
    {
        return $this->execute(static function($model,$provider,$filters){
            $start=request()->getPost('start');$length=request()->getPost('length');$draw=request()->getPost('draw');$search=request()->getPost('search');
            if(!is_scalar($start)||!ctype_digit((string)$start)||(int)$start>10000000||!is_scalar($length)||!in_array((int)$length,[10,25,50,100],true)||!is_scalar($draw)||!ctype_digit((string)$draw))throw new \InvalidArgumentException('Parameter tabel tidak valid.');
            $term=is_array($search)?($search['value']??''):'';
            if(!is_string($term)||mb_strlen($term)>200)throw new \InvalidArgumentException('Kata pencarian maksimal 200 karakter.');
            return ['draw'=>(int)$draw]+$model->details($provider,$filters,(int)$start,(int)$length,trim($term));
        });
    }

    private function execute(callable $action)
    {
        if(!session()->get('loggedin')||!session()->get('kodeppk'))return response()->setStatusCode(403)->setJSON(['responseCode'=>'01','responseDesc'=>'Organisasi atau kode PPK pada sesi belum tersedia.']);
        try{
            $year=request()->getPost('selectperiode');$month=request()->getPost('month')??'';$service=request()->getPost('service')??'';$status=request()->getPost('status')??'';
            if(!is_string($year)||!preg_match('/^[0-9]{4}$/D',$year)||(int)$year<2014||(int)$year>(int)date('Y'))throw new \InvalidArgumentException('Pilih tahun pulang yang valid.');
            if(!is_string($month)||($month!==''&&(!ctype_digit($month)||(int)$month<1||(int)$month>12)))throw new \InvalidArgumentException('Bulan tidak valid.');
            if(!is_string($service)||!in_array($service,['','1','2'],true))throw new \InvalidArgumentException('Jenis pelayanan tidak valid.');
            if(!is_string($status)||!in_array($status,['','PENDING','TIDAK LAYAK'],true))throw new \InvalidArgumentException('Status tidak valid.');
            $result=$action(new PendingModel(),(string)session()->get('kodeppk'),compact('year','month','service','status'));
            return response()->setJSON(['responseCode'=>'00']+$result);
        }catch(\InvalidArgumentException $e){return response()->setStatusCode(422)->setJSON(['responseCode'=>'01','responseDesc'=>$e->getMessage()]);
        }catch(\Throwable $e){log_message('error','Klaim pending: {message}',['message'=>$e->getMessage()]);return response()->setStatusCode(503)->setJSON(['responseCode'=>'01','responseDesc'=>'Data klaim belum dapat dimuat. Silakan coba kembali.']);}
    }
}
