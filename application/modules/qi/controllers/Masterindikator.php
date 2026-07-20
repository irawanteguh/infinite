<?php
    defined("BASEPATH") OR exit("No direct script access allowed");

    class Masterindikator extends MX_Controller{ 

        public function __construct(){
            parent:: __construct();
            $this->load->model("Modelmasterindikator","md");
        }
        
        public function index(){
            $data = $this->loadcombobox();
            $this->template->load("template/dashboard-light-aside","v_masterindikator",$data);
        }

        public function loadcombobox(){
            $resultdatamastersatuan     = $this->md->datamastersatuan();
            $resultdatamasterfrekuensi  = $this->md->datamasterfrekuensi();
            $resultdatamastersumber     = $this->md->datamastersumber();
            $resultdatamasterdonabedian = $this->md->datamasterdonabedian();
            $resultdatamastertarget     = $this->md->datamastertarget();
            $resultdatamasterbenchmark  = $this->md->datamasterbenchmark();
            
            $mastersatuan="";
            foreach($resultdatamastersatuan as $a ){
                $mastersatuan.="<option value='".$a->satuan_id."'>".$a->keterangan."</option>";
            }

            $masterfrekuensi="";
            foreach($resultdatamasterfrekuensi as $a ){
                $masterfrekuensi.="<option value='".$a->frekuensi_id."'>".$a->frekuensi."</option>";
            }

            $mastersumber="";
            foreach($resultdatamastersumber as $a ){
                $mastersumber.="<option value='".$a->sumber_id."'>".$a->sumber."</option>";
            }

            $masterdonabedian="";
            foreach($resultdatamasterdonabedian as $a ){
                $masterdonabedian.="<option value='".$a->donabedian_id."'>".$a->donabedian."</option>";
            }

            $mastertarget="";
            foreach($resultdatamastertarget as $a ){
                $mastertarget.="<option value='".$a->value."'>".$a->label."</option>";
            }

            $masterbenchmark="";
            foreach($resultdatamasterbenchmark as $a ){
                $masterbenchmark.="<option value='".$a->benchmark_id."'>".$a->benchmark."</option>";
            }


            $data['mastersatuan']     = $mastersatuan;
            $data['masterfrekuensi']  = $masterfrekuensi;
            $data['mastersumber']     = $mastersumber;
            $data['masterdonabedian'] = $masterdonabedian;
            $data['mastertarget']     = $mastertarget;
            $data['masterbenchmark']  = $masterbenchmark;
            
            return $data;
		}

        public function dataindikator(){
            $result = $this->md->dataindikator($_SESSION['groupid'],$_SESSION['orgid']);
            
			if(!empty($result)){
                $json["responCode"]="00";
                $json["responHead"]="success";
                $json["responDesc"]="Data found successfully";
				$json['responResult']=$result;
            }else{
                $json["responCode"]="01";
                $json["responHead"]="info";
                $json["responDesc"]="No data found";
            }

            echo json_encode($json);
        }

        public function editmasterindikator(){
            $indikatorid = $this->input->post("modal_edit_masterindikator_indikatorid");

            $dataupdate = [
                'INDIKATOR'                 => $this->input->post("modal_edit_masterindikator_indikator"),
                'DASAR_PEMIKIRAN'           => $this->input->post("modal_edit_masterindikator_dasarpemikiran"),

                'DIMENSI_MUTU_KESELAMATAN'  => $this->input->post("modal_edit_masterindikator_dimensikeselamatan") == "Y" ? "Y" : "N",
                'DIMENSI_MUTU_WAKTU'        => $this->input->post("modal_edit_masterindikator_dimensiwaktu") == "Y" ? "Y" : "N",
                'DIMENSI_MUTU_EFEKTIF'      => $this->input->post("modal_edit_masterindikator_dimensiefektif") == "Y" ? "Y" : "N",
                'DIMENSI_MUTU_EFESIEN'      => $this->input->post("modal_edit_masterindikator_dimensiefisien") == "Y" ? "Y" : "N",
                'DIMENSI_MUTU_PASIEN'       => $this->input->post("modal_edit_masterindikator_dimensipasien") == "Y" ? "Y" : "N",
                'DIMENSI_MUTU_INTEGRASI'    => $this->input->post("modal_edit_masterindikator_dimensiintegrasi") == "Y" ? "Y" : "N",

                'TUJUAN'                    => $this->input->post("modal_edit_masterindikator_tujuan"),
                'DEFINISI'                  => $this->input->post("modal_edit_masterindikator_definisi"),
                'NUMERATOR'                 => $this->input->post("modal_edit_masterindikator_numerator"),
                'DENUMERATOR'               => $this->input->post("modal_edit_masterindikator_denominator"),
                'FORMULA'                   => $this->input->post("modal_edit_masterindikator_formula"),

                'SATUAN_ID'                 => $this->input->post("modal_edit_masterindikator_satuanid"),
                'FREKUENSI_ID'              => $this->input->post("modal_edit_masterindikator_frekuensiid"),

                'INKLUSI'                   => $this->input->post("modal_edit_masterindikator_kriteriainklusi"),
                'EKSKLUSI'                  => $this->input->post("modal_edit_masterindikator_kriteriaeksklusi"),
                'METODE'                    => $this->input->post("modal_edit_masterindikator_metodepengumpulan"),
                'INSTRUMENT'                => $this->input->post("modal_edit_masterindikator_instrumen"),
                'POPULASI'                  => $this->input->post("modal_edit_masterindikator_populasi"),

                'SUMBER_ID'                 => $this->input->post("modal_edit_masterindikator_sumberid"),
                'DONABEDIAN_ID'             => $this->input->post("modal_edit_masterindikator_donabedianid"),
                'TARGET_CAPAIAN'            => $this->input->post("modal_edit_masterindikator_targetcapaian"),
                'BENCHMARK_ID'              => $this->input->post("modal_edit_masterindikator_benchmarkid"),

                'ACTIVE'                    => $this->input->post("modal_edit_masterindikator_active"),

                'LAST_UPDATE_BY'            => $_SESSION['userid'],
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
            

            echo json_encode($json);
        }

        public function addmasterindikator(){

            $dataupdate = [
                'GROUP_ID'                  => $_SESSION['groupid'],
                'ORG_ID'                    => $_SESSION['orgid'],
                'INDIKATOR'                 => $this->input->post("modal_add_masterindikator_indikator"),
                'DASAR_PEMIKIRAN'           => $this->input->post("modal_add_masterindikator_dasarpemikiran"),

                'DIMENSI_MUTU_KESELAMATAN'  => $this->input->post("modal_add_masterindikator_dimensikeselamatan") == "Y" ? "Y" : "N",
                'DIMENSI_MUTU_WAKTU'        => $this->input->post("modal_add_masterindikator_dimensiwaktu") == "Y" ? "Y" : "N",
                'DIMENSI_MUTU_EFEKTIF'      => $this->input->post("modal_add_masterindikator_dimensiefektif") == "Y" ? "Y" : "N",
                'DIMENSI_MUTU_EFESIEN'      => $this->input->post("modal_add_masterindikator_dimensiefisien") == "Y" ? "Y" : "N",
                'DIMENSI_MUTU_PASIEN'       => $this->input->post("modal_add_masterindikator_dimensipasien") == "Y" ? "Y" : "N",
                'DIMENSI_MUTU_INTEGRASI'    => $this->input->post("modal_add_masterindikator_dimensiintegrasi") == "Y" ? "Y" : "N",

                'TUJUAN'                    => $this->input->post("modal_add_masterindikator_tujuan"),
                'DEFINISI'                  => $this->input->post("modal_add_masterindikator_definisi"),
                'NUMERATOR'                 => $this->input->post("modal_add_masterindikator_numerator"),
                'DENUMERATOR'               => $this->input->post("modal_add_masterindikator_denominator"),
                'FORMULA'                   => $this->input->post("modal_add_masterindikator_formula"),

                'SATUAN_ID'                 => $this->input->post("modal_add_masterindikator_satuanid"),
                'FREKUENSI_ID'              => $this->input->post("modal_add_masterindikator_frekuensiid"),

                'INKLUSI'                   => $this->input->post("modal_add_masterindikator_kriteriainklusi"),
                'EKSKLUSI'                  => $this->input->post("modal_add_masterindikator_kriteriaeksklusi"),
                'METODE'                    => $this->input->post("modal_add_masterindikator_metodepengumpulan"),
                'INSTRUMENT'                => $this->input->post("modal_add_masterindikator_instrumen"),
                'POPULASI'                  => $this->input->post("modal_add_masterindikator_populasi"),

                'SUMBER_ID'                 => $this->input->post("modal_add_masterindikator_sumberid"),
                'DONABEDIAN_ID'             => $this->input->post("modal_add_masterindikator_donabedianid"),
                'TARGET_CAPAIAN'            => $this->input->post("modal_add_masterindikator_targetcapaian"),
                'BENCHMARK_ID'              => $this->input->post("modal_add_masterindikator_benchmarkid"),

                'CREATED_BY'                => $_SESSION['userid'],
                'LAST_UPDATE_BY'            => $_SESSION['userid'],
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
            

            echo json_encode($json);
        }

    }
?>