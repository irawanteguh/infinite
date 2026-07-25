<?php
    defined("BASEPATH") OR exit("No direct script access allowed");

    class Masterkfa extends MX_Controller{ 

        public function __construct(){
            parent:: __construct();
            $this->load->model("Modelmasterkfa","md");
        }
        
        public function index(){
            $this->template->load("template/dashboard-light-aside","v_masterkfa");
        }

        public function getallproductkfa(){
            $type    = $this->input->post("type");
            $keyword = $this->input->post("keyword");

            $resultgetallproductkfa = Satusehat::getallproductkfa($type,$keyword);
            $data = $resultgetallproductkfa['items']['data'];

            foreach ($data as $key => $value) {

                $resultcheck = $this->md->checkdatakfa(
                    $_SESSION['groupid'],
                    $_SESSION['orgid'],
                    $value['kfa_code']
                );

                $data[$key]['obat_id'] = "";
                $data[$key]['distributor_price'] = 0;

                if (!empty($resultcheck)) {
                    $data[$key]['obat_id'] = $resultcheck[0]->obat_id;
                    $data[$key]['distributor_price'] = $resultcheck[0]->hrg_total;
                }
            }

            $json["responCode"]   = "00";
            $json["responHead"]   = "success";
            $json["responDesc"]   = "Data found successfully";
            $json['responResult'] = $data;
            
            echo json_encode($json);

        }

        public function updatekfa(){
            $kfaid    = $this->input->post('modal_import_kfa_kfaid');
            $nama     = $this->input->post('modal_import_kfa_nama_obat');
            $produsen = $this->input->post('modal_import_kfa_produsen');

            $het      = (float)str_replace('.', '', $this->input->post('modal_import_kfa_het'));
            $harga    = (float)str_replace('.', '', $this->input->post('modal_import_kfa_harga_distributor'));
            $disc     = (float)$this->input->post('modal_import_kfa_disc');
            $ppn      = (float)$this->input->post('modal_import_kfa_ppn');

            $subtotal = $harga - ($harga * $disc / 100);
            $total    = $subtotal + ($subtotal * $ppn / 100);

            $resultcheckdatakfa = $this->md->checkdatakfa($_SESSION['groupid'],$_SESSION['orgid'],$kfaid);

            $dataupdate = array(
                'GROUP_ID'         => $_SESSION['groupid'],
                'ORG_ID'           => $_SESSION['orgid'],
                'KFA_ID'           => $kfaid,
                'NAME'             => $nama,
                'PRODUSEN'         => $produsen,
                'HET'              => $het,
                'HRG_DISTRIBUTOR'  => $harga,
                'DISC'             => $disc,
                'PPN'              => $ppn,
                'HRG_TOTAL'        => $total,
                'ACTIVE'           => '1',
                'CREATED_BY'       => $_SESSION['userid']
            );

            if(empty($resultcheckdatakfa)){
                if ($this->md->insertkfa($dataupdate)) {
                    $json['responCode'] = "00";
                    $json['responHead'] = "success";
                    $json['responDesc'] = "Data imported successfully";
                } else {
                    $json['responCode'] = "01";
                    $json['responHead'] = "info";
                    $json['responDesc'] = "Data failed to import";
                }
            }else{
                if($this->md->updatekfa($kfaid,$dataupdate)){
                    $json['responCode'] = "00";
                    $json['responHead'] = "success";
                    $json['responDesc'] = "Data Updated Successfully";
                }else{
                    $json['responCode'] = "01";
                    $json['responHead'] = "info";
                    $json['responDesc'] = "Data failed to update";
                }
            }

            echo json_encode($json);
        }

    }
?>