<script src="<?= base_url('assets/routingsystem/global/plugins.bundle.js') ?>"></script>
<script src="<?= base_url('assets/routingsystem/custom/datatables/datatables.bundle.js') ?>"></script>
<script src="<?= base_url('assets/routingsystem/scripts.bundle.js') ?>"></script>

<?php

$uri = service('uri');

$segment1 = $uri->getSegment(1);
$segment2 = $uri->getSegment(2);

$jspathroot = FCPATH . 'assets/js/root/';

if (is_dir($jspathroot)) {

    $jsFiles = glob($jspathroot . '*.js');

    echo PHP_EOL . '<!-- Load Js Root System -->' . PHP_EOL;

    foreach ($jsFiles as $jsFile) {

        $jsFilename = basename($jsFile);

        echo "\t\t<script type='text/javascript' src='"
            . base_url('assets/js/root/' . $jsFilename)
            . "'></script>"
            . PHP_EOL;
    }
}

$jspath = FCPATH
    . 'assets/js/'
    . $segment1
    . '/'
    . $segment2
    . '.js';

if (file_exists($jspath)) {

    echo PHP_EOL
        . '<!-- Load JS Files Folder '
        . $segment1
        . '/'
        . $segment2
        . ' -->'
        . PHP_EOL;

    echo "\t\t<script type='text/javascript' src='"
        . base_url(
            'assets/js/'
            . $segment1
            . '/'
            . $segment2
            . '.js?v='
            . time()
        )
        . "'></script>"
        . PHP_EOL;
}
?>