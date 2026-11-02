<?php

namespace Modules\Qi\Controllers;

use App\Controllers\BaseController;
use Modules\Qi\Models\MasterindikatorModel;

class Masterindikator extends BaseController{

    protected $md;

    public function __construct(){
        $this->md = new MasterindikatorModel();
    }

    public function index(){
        $data = $this->loadcombobox();
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Qi\Views\v_masterindikator',$data)
        ]);
    }

    private function loadcombobox(){
        $resultdatamastersatuan     = $this->md->datamastersatuan();
        $resultdatamasterfrekuensi  = $this->md->datamasterfrekuensi();
        $resultdatamastersumber     = $this->md->datamastersumber();
        $resultdatamasterdonabedian = $this->md->datamasterdonabedian();
        $resultdatamastertarget     = $this->md->datamastertarget();
        $resultdatamasterbenchmark  = $this->md->datamasterbenchmark();

        $data['mastersatuan'] = '';
        foreach ($resultdatamastersatuan as $row) {
            $data['mastersatuan'] .= '<option value="' . $row->satuan_id . '">' . $row->keterangan . '</option>';
        }

        $data['masterfrekuensi'] = '';
        foreach ($resultdatamasterfrekuensi as $row) {
            $data['masterfrekuensi'] .= '<option value="' . $row->frekuensi_id . '">' . $row->frekuensi . '</option>';
        }

        $data['mastersumber'] = '';
        foreach ($resultdatamastersumber as $row) {
            $data['mastersumber'] .= '<option value="' . $row->sumber_id . '">' . $row->sumber . '</option>';
        }

        $data['masterdonabedian'] = '';
        foreach ($resultdatamasterdonabedian as $row) {
            $data['masterdonabedian'] .= '<option value="' . $row->donabedian_id . '">' . $row->donabedian . '</option>';
        }

        $data['mastertarget'] = '';
        foreach ($resultdatamastertarget as $row) {
            $data['mastertarget'] .= '<option value="' . $row->value . '">' . $row->label . '</option>';
        }

        $data['masterbenchmark'] = '';
        foreach ($resultdatamasterbenchmark as $row) {
            $data['masterbenchmark'] .= '<option value="' . $row->benchmark_id . '">' . $row->benchmark . '</option>';
        }

        return $data;
    }

    public function datamasterindikator(){
        $result = $this->md->datamasterindikator(session()->get('groupid'), session()->get('orgid'));

        if (!empty($result)) {
            $json["responseCode"]   = "00";
            $json["responseHead"]   = "success";
            $json["responseDesc"]   = "Data found";
            $json["responseResult"] = $result;
        } else {
            $json["responseCode"]   = "01";
            $json["responseHead"]   = "info";
            $json["responseDesc"]   = "No data found";
        }

        return response()->setJSON($json);
    }

    public function updateindikator(){
        $indikatorid = request()->getPost('indikatorid');
        $active           = request()->getPost('active');

        if (empty($indikatorid)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Indikator ID is required.'
            ]);
        }

        $dataupdate['active'] = $active;

        if ($this->md->updatemasterindikator($indikatorid, $dataupdate)) {
            $json["responseCode"] = "00";
            $json["responseHead"] = "success";
            $json["responseDesc"] = $active == "1" ? "Indicator has been activated successfully." : "Indicator has been deactivated successfully.";
        } else {
            $json["responseCode"] = "01";
            $json["responseHead"] = "info";
            $json["responseDesc"] = "Failed to update Indicator activation status.";
        }

        return response()->setJSON($json);
    }

    public function addmasterindikator(){

        $dataupdate = [
            'GROUP_ID'                  => session()->get('groupid'),
            'ORG_ID'                    => session()->get('orgid'),
            'INDIKATOR'                 => request()->getPost("modal_add_masterindikator_indikator"),
            'DASAR_PEMIKIRAN'           => request()->getPost("modal_add_masterindikator_dasarpemikiran"),

            'DIMENSI_MUTU_KESELAMATAN'  => request()->getPost("modal_add_masterindikator_dimensikeselamatan") == "Y" ? "Y" : "N",
            'DIMENSI_MUTU_WAKTU'        => request()->getPost("modal_add_masterindikator_dimensiwaktu") == "Y" ? "Y" : "N",
            'DIMENSI_MUTU_EFEKTIF'      => request()->getPost("modal_add_masterindikator_dimensiefektif") == "Y" ? "Y" : "N",
            'DIMENSI_MUTU_EFESIEN'      => request()->getPost("modal_add_masterindikator_dimensiefisien") == "Y" ? "Y" : "N",
            'DIMENSI_MUTU_PASIEN'       => request()->getPost("modal_add_masterindikator_dimensipasien") == "Y" ? "Y" : "N",
            'DIMENSI_MUTU_INTEGRASI'    => request()->getPost("modal_add_masterindikator_dimensiintegrasi") == "Y" ? "Y" : "N",

            'TUJUAN'                    => request()->getPost("modal_add_masterindikator_tujuan"),
            'DEFINISI'                  => request()->getPost("modal_add_masterindikator_definisi"),
            'NUMERATOR'                 => request()->getPost("modal_add_masterindikator_numerator"),
            'DENUMERATOR'               => request()->getPost("modal_add_masterindikator_denominator"),
            'FORMULA'                   => request()->getPost("modal_add_masterindikator_formula"),

            'SATUAN_ID'                 => request()->getPost("modal_add_masterindikator_satuanid"),
            'FREKUENSI_ID'              => request()->getPost("modal_add_masterindikator_frekuensiid"),

            'INKLUSI'                   => request()->getPost("modal_add_masterindikator_kriteriainklusi"),
            'EKSKLUSI'                  => request()->getPost("modal_add_masterindikator_kriteriaeksklusi"),
            'METODE'                    => request()->getPost("modal_add_masterindikator_metodepengumpulan"),
            'INSTRUMENT'                => request()->getPost("modal_add_masterindikator_instrumen"),
            'POPULASI'                  => request()->getPost("modal_add_masterindikator_populasi"),

            'SUMBER_ID'                 => request()->getPost("modal_add_masterindikator_sumberid"),
            'DONABEDIAN_ID'             => request()->getPost("modal_add_masterindikator_donabedianid"),
            'TARGET_CAPAIAN'            => request()->getPost("modal_add_masterindikator_targetcapaian"),
            'BENCHMARK_ID'              => request()->getPost("modal_add_masterindikator_benchmarkid"),

            'CREATED_BY'                => session()->get('userid'),
            'LAST_UPDATE_BY'            => session()->get('userid'),
            'LAST_UPDATE_DATE'          => date('Y-m-d H:i:s')
        ];

        if($this->md->insertmasterindikator($dataupdate)){
            $json['responCode']="00";
            $json['responHead']="success";
            $json['responDesc']="Data Updated Successfully";
        }else{
            $json['responCode']="01";
            $json['responHead']="info";
            $json['responDesc']="Data failed to update";
        }
        

        return response()->setJSON($json);
    }

    public function editmasterindikator(){
        $indikatorid = request()->getPost("modal_edit_masterindikator_indikatorid");

        $dataupdate = [
            'INDIKATOR'                 => request()->getPost("modal_edit_masterindikator_indikator"),
            'DASAR_PEMIKIRAN'           => request()->getPost("modal_edit_masterindikator_dasarpemikiran"),

            'DIMENSI_MUTU_KESELAMATAN'  => request()->getPost("modal_edit_masterindikator_dimensikeselamatan") == "Y" ? "Y" : "N",
            'DIMENSI_MUTU_WAKTU'        => request()->getPost("modal_edit_masterindikator_dimensiwaktu") == "Y" ? "Y" : "N",
            'DIMENSI_MUTU_EFEKTIF'      => request()->getPost("modal_edit_masterindikator_dimensiefektif") == "Y" ? "Y" : "N",
            'DIMENSI_MUTU_EFESIEN'      => request()->getPost("modal_edit_masterindikator_dimensiefisien") == "Y" ? "Y" : "N",
            'DIMENSI_MUTU_PASIEN'       => request()->getPost("modal_edit_masterindikator_dimensipasien") == "Y" ? "Y" : "N",
            'DIMENSI_MUTU_INTEGRASI'    => request()->getPost("modal_edit_masterindikator_dimensiintegrasi") == "Y" ? "Y" : "N",

            'TUJUAN'                    => request()->getPost("modal_edit_masterindikator_tujuan"),
            'DEFINISI'                  => request()->getPost("modal_edit_masterindikator_definisi"),
            'NUMERATOR'                 => request()->getPost("modal_edit_masterindikator_numerator"),
            'DENUMERATOR'               => request()->getPost("modal_edit_masterindikator_denominator"),
            'FORMULA'                   => request()->getPost("modal_edit_masterindikator_formula"),

            'SATUAN_ID'                 => request()->getPost("modal_edit_masterindikator_satuanid"),
            'FREKUENSI_ID'              => request()->getPost("modal_edit_masterindikator_frekuensiid"),

            'INKLUSI'                   => request()->getPost("modal_edit_masterindikator_kriteriainklusi"),
            'EKSKLUSI'                  => request()->getPost("modal_edit_masterindikator_kriteriaeksklusi"),
            'METODE'                    => request()->getPost("modal_edit_masterindikator_metodepengumpulan"),
            'INSTRUMENT'                => request()->getPost("modal_edit_masterindikator_instrumen"),
            'POPULASI'                  => request()->getPost("modal_edit_masterindikator_populasi"),

            'SUMBER_ID'                 => request()->getPost("modal_edit_masterindikator_sumberid"),
            'DONABEDIAN_ID'             => request()->getPost("modal_edit_masterindikator_donabedianid"),
            'TARGET_CAPAIAN'            => request()->getPost("modal_edit_masterindikator_targetcapaian"),
            'BENCHMARK_ID'              => request()->getPost("modal_edit_masterindikator_benchmarkid"),

            'ACTIVE'                    => request()->getPost("modal_edit_masterindikator_active"),

            'LAST_UPDATE_BY'            => session()->get('userid'),
            'LAST_UPDATE_DATE'          => date('Y-m-d H:i:s')
        ];


        if($this->md->updatemasterindikator($indikatorid, $dataupdate)){
            $json['responCode']="00";
            $json['responHead']="success";
            $json['responDesc']="Data Updated Successfully";
        }else{
            $json['responCode']="01";
            $json['responHead']="info";
            $json['responDesc']="Data failed to update";
        }
        

        return response()->setJSON($json);
    }

}