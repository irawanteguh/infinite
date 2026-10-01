<script src="<?= base_url('assets/routingsystem/global/plugins.bundle.js') ?>"></script>
<script src="<?= base_url('assets/routingsystem/custom/datatables/datatables.bundle.js') ?>"></script>
<script src="<?= base_url('assets/routingsystem/scripts.bundle.js') ?>"></script>

<?php

$uri = service('uri');

$segments = $uri->getSegments();

$segment1 = $segments[0] ?? '';
$segment2 = $segments[1] ?? '';

/*
|--------------------------------------------------------------------------
| Root JS
|--------------------------------------------------------------------------
|
| URL:
| /
|
| Load:
| assets/js/auth/sign.js
|
*/

if ($segment1 === '' && $segment2 === '') {

    $jsFile = FCPATH . 'assets/js/auth/sign.js';

    if (file_exists($jsFile)) {

        echo PHP_EOL
            . '<!-- Load Auth Sign JS -->'
            . PHP_EOL;

        echo "\t\t<script type='text/javascript' src='"
            . base_url('assets/js/auth/sign.js?v=' . time())
            . "'></script>"
            . PHP_EOL;
    }
}

/*
|--------------------------------------------------------------------------
| Root System JS
|--------------------------------------------------------------------------
*/

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

/*
|--------------------------------------------------------------------------
| Dynamic Page JS
|--------------------------------------------------------------------------
|
| Contoh:
|
| /additional/welcomepage
|
| segment1 = additional
| segment2 = welcomepage
|
| akan mencari:
|
| assets/js/additional/welcomepage.js
|
*/

if ($segment1 !== '' && $segment2 !== '') {

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
}

?>