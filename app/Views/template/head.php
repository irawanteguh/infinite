<script> var url = '<?php echo base_url();?>'; </script>

<title>INFINITE | Integrated Healthcare Data Analytics Platform</title>
<meta name="description" content="INFINITE is an integrated healthcare data analytics platform for hospital performance monitoring, clinical analytics, financial analysis, operational reporting, and business intelligence." />
<meta name="keywords" content="INFINITE, Healthcare Analytics, Hospital Data Analytics, Healthcare Business Intelligence, Hospital Dashboard, Medical Analytics, Clinical Analytics, Financial Analytics, Patient Analytics, Hospital Information System, INA-CBG, BPJS" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta charset="utf-8" />
<meta property="og:locale" content="en_US" />
<meta property="og:type" content="website" />
<meta property="og:title" content="INFINITE | Integrated Healthcare Data Analytics Platform" />
<meta property="og:description" content="Integrated healthcare data analytics platform for hospital performance, clinical analytics, financial analysis, operational monitoring, and executive reporting." />
<meta property="og:url" content="<?= base_url() ?>" />
<meta property="og:site_name" content="INFINITE" />


<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
<link rel="shortcut icon" href="<?= base_url('assets/favicon/favicon.ico') ?>" />

<link href="<?= base_url('assets/plugins/bootstrap-icons/bootstrap-icons.css') ?>" rel="stylesheet" type="text/css" />
<link href="<?= base_url('assets/plugins/animate.css/animate.min.css') ?>" rel="stylesheet" type="text/css" />
<link href="<?= base_url('assets/plugins/fontawesome-6.5.1/css/all.min.css') ?>" rel="stylesheet" type="text/css" />

<link href="<?= base_url('assets/routingsystem/custom/fullcalendar/fullcalendar.bundle.css') ?>" rel="stylesheet" type="text/css" />
<link href="<?= base_url('assets/routingsystem/global/plugins.bundle.css') ?>" rel="stylesheet" type="text/css" />
<link href="<?= base_url('assets/routingsystem/css/style.bundle.css') ?>" rel="stylesheet" type="text/css" />

<?php

    $uri      = service('uri');
    $segments = $uri->getSegments();
    $segment1 = $segments[0] ?? '';
    $segment2 = $segments[1] ?? '';

    if ($segment1 === '' && $segment2 === '') {
        $cssFile = FCPATH . 'assets/css/auth/sign.css';
        if (file_exists($cssFile)) {
            echo PHP_EOL.'<!-- Load Auth Sign CSS -->'.PHP_EOL;
            echo "\t\t<link rel='stylesheet' type='text/css' href='".base_url('assets/css/auth/sign.css?v=' . time())."' />".PHP_EOL;
        }
    }


    if ($segment1 !== '' && $segment2 !== '') {
        $cssFile = FCPATH . 'assets/css/' . $segment1 . '/' . $segment2 . '.css';
        if (file_exists($cssFile)) {
            echo PHP_EOL . '<!-- Load CSS Files Folder ' . $segment1 . '/' . $segment2 . ' -->' . PHP_EOL;
            echo "\t\t<link rel='stylesheet' type='text/css' href='" . base_url('assets/css/' . $segment1 . '/' . $segment2 . '.css?v=' . time()) . "'>" . PHP_EOL;
        }
    }

?>