<script>const logoutUrl = "<?= site_url('auth/logout'); ?>";</script>
<script src="<?= base_url('assets/routingsystem/global/plugins.bundle.js') ?>"></script>
<script src="<?= base_url('assets/routingsystem/custom/datatables/datatables.bundle.js') ?>"></script>
<script src="<?= base_url('assets/routingsystem/scripts.bundle.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>

<script src="<?= base_url('assets/plugins/amcharts_5.21.2/index.js') ?>"></script>
<script src="<?= base_url('assets/plugins/amcharts_5.21.2/themes/Animated.js') ?>"></script>
<script src="<?= base_url('assets/plugins/amcharts_5.21.2/wc.js') ?>"></script>

<?php
    $uri = service('uri');
    $segments = $uri->getSegments();
    $segment1 = $segments[0] ?? '';
    $segment2 = $segments[1] ?? '';
    $segment3 = $segments[2] ?? '';

    $isAuthLogin = ($segment1 === 'auth' && in_array($segment2, ['login', 'sign'], true));

    if ($isAuthLogin) {
        $jsFile = FCPATH . 'assets/js/auth/sign.js';
        if (file_exists($jsFile)) {
            echo '<script type="text/javascript" src="' . base_url('assets/js/auth/sign.js?v=' . time()) . '"></script>' . PHP_EOL;
        }
    }

    $jspathroot = FCPATH . 'assets/js/root/';
    if (is_dir($jspathroot)) {
        $jsFiles = glob($jspathroot . '*.js');
        foreach ($jsFiles as $jsFile) {
            $jsFilename = basename($jsFile);
            echo '<script type="text/javascript" src="' . base_url('assets/js/root/' . $jsFilename) . '"></script>' . PHP_EOL;
        }
    }

    if ($segment1 !== '' && $segment2 !== '') {
        if ($segment1 === 'ur' && $segment2 === 'analystsl' && $segment3 === 'simulation') {
            $segment2 = 'analystslsimulation';
        }
        $jspath = FCPATH . 'assets/js/' . $segment1 . '/' . $segment2 . '.js';
        if (file_exists($jspath)) {
            echo '<script type="text/javascript" src="' . base_url('assets/js/' . $segment1 . '/' . $segment2 . '.js?v=' . time()) . '"></script>' . PHP_EOL;
        }
    }

    if ($segment1 === 'qi' && $segment2 === 'pdsa' && $segment3 === 'diagram') {
        $diagramJs = FCPATH . 'assets/js/qi/pdsadiagram.js';
        if (file_exists($diagramJs)) {
            echo '<script type="text/javascript" src="' . base_url('assets/js/qi/pdsadiagram.js?v=' . time()) . '"></script>' . PHP_EOL;
        }
    }
?>
