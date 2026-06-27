<div data-kt-swapper="true" data-kt-swapper-mode="prepend" data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}" class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
    <h1 class="d-flex align-items-center text-dark fw-bolder fs-3 my-1"><?= $pageTitle; ?></h1>
    <span class="h-20px border-gray-200 border-start mx-4"></span>
    <?php
        $directory = APPPATH.'modules/'.$this->uri->segment(1).'/breadcrumb/'.$this->uri->segment(2).".php";
        if(file_exists($directory)){
            include($directory);
        }
    ?>
</div>

<div class="d-flex align-items-center py-1">
    <?php
        $directory = APPPATH.'modules/'.$this->uri->segment(1).'/filter/'.$this->uri->segment(2).".php";
        if(file_exists($directory)){
            include($directory);
        }

        $directory = APPPATH.'modules/'.$this->uri->segment(1).'/toolbar/'.$this->uri->segment(2).".php";
        if(file_exists($directory)){
            include($directory);
        }
    ?>
</div>