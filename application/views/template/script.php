<script src="<?= base_url('assets/plugins/routing/global/plugins.bundle.js') ?>"></script>
<script src="<?= base_url('assets/js/routing/scripts.bundle.js') ?>"></script>
<!-- <script src="<?= base_url('assets/plugins/routing/custom/fullcalendar/fullcalendar.bundle.js') ?>"></script>
<script src="<?= base_url('assets/js/routing/custom/widgets.js') ?>"></script>
<script src="<?= base_url('assets/js/routing/custom/apps/chat/chat.js') ?>"></script>
<script src="<?= base_url('assets/js/routing/custom/modals/create-app.js') ?>"></script>
<script src="<?= base_url('assets/js/routing/custom/modals/upgrade-plan.js') ?>"></script> -->

<?php
    if(file_exists(FCPATH . "assets/js/" . $this->uri->segment(1) . "/" . $this->uri->segment(2) . ".js")){
        echo PHP_EOL . '<!-- Load JS Files Folder ' . $this->uri->segment(1) . '/' . $this->uri->segment(2) . ' -->' . PHP_EOL;
        echo "\t\t<script type='text/javascript' src='" . base_url('assets/js/' . $this->uri->segment(1) . "/" . $this->uri->segment(2) . ".js?v=" . time()) . "'></script>" . PHP_EOL;
    };
?>