<?php
    $name        = trim((string) session()->get('name'));
    $displayName = $name !== '' ? $name : 'Guest';
    $hour        = (int) date('G');

    if ($hour >= 5 && $hour < 12) {
        $greeting   = 'Good Morning';
        $background = 'morning-city.png';

        $titleColor  = '#1F2937';
        $textColor   = '#4B5563';
        $kickerColor = '#374151';
        $badgeBg     = 'rgba(255,255,255,.75)';
        $badgeBorder = 'rgba(0,0,0,.08)';
        $badgeColor  = '#1F2937';

    } elseif ($hour >= 12 && $hour < 16) {
        $greeting   = 'Good Afternoon';
        $background = 'sunrise-city.png';

        $titleColor  = '#1E293B';
        $textColor   = '#475569';
        $kickerColor = '#334155';
        $badgeBg     = 'rgba(255,255,255,.72)';
        $badgeBorder = 'rgba(0,0,0,.08)';
        $badgeColor  = '#1E293B';

    } elseif ($hour >= 16 && $hour < 19) {
        $greeting   = 'Good Evening';
        $background = 'sunset-city.png';

        $titleColor  = '#FFFFFF';
        $textColor   = 'rgba(255,255,255,.88)';
        $kickerColor = 'rgba(255,255,255,.75)';
        $badgeBg     = 'rgba(255,255,255,.12)';
        $badgeBorder = 'rgba(255,255,255,.15)';
        $badgeColor  = '#FFFFFF';

    } else {
        $greeting   = 'Good Night';
        $background = 'night-city.png';

        $titleColor  = '#FFFFFF';
        $textColor   = 'rgba(255,255,255,.85)';
        $kickerColor = 'rgba(255,255,255,.72)';
        $badgeBg     = 'rgba(255,255,255,.12)';
        $badgeBorder = 'rgba(255,255,255,.15)';
        $badgeColor  = '#FFFFFF';

    }
?>

<style>
    .welcome-banner{
        position:relative;
        overflow:hidden;
        min-height:280px;
        border:1px solid rgba(255,255,255,.22);
        border-radius:22px;
        box-shadow:0 18px 45px rgba(25,48,86,.16);

        background-image:url('<?= base_url('assets/media/ambient/'.$background); ?>');
        background-position:center;
        background-size:cover;
        background-repeat:no-repeat;

        --title-color: <?= $titleColor ?>;
        --text-color: <?= $textColor ?>;
        --kicker-color: <?= $kickerColor ?>;
        --badge-bg: <?= $badgeBg ?>;
        --badge-border: <?= $badgeBorder ?>;
        --badge-color: <?= $badgeColor ?>;
    }

    .welcome-banner::before{
        content:"";
        position:absolute;
        inset:0;
        background:linear-gradient(100deg,rgba(8,23,54,.92) 0%,rgba(15,43,81,.75) 52%,rgba(15,43,81,.2) 100%);
        z-index:1;
    }

    .welcome-banner::after{
        content:"";
        position:absolute;
        width:360px;
        height:360px;
        right:4%;
        top:-190px;
        border:1px solid rgba(173,238,255,.26);
        border-radius:50%;
        box-shadow:0 0 0 34px rgba(173,238,255,.06),0 0 0 70px rgba(173,238,255,.04);
        pointer-events:none;
    }

    .welcome-banner-content{
        position:relative;
        z-index:2;
    }

    .welcome-kicker{
        color:var(--kicker-color);
        color:#a6edff;
        font-size:.78rem;
        font-weight:700;
        text-transform:uppercase;
        letter-spacing:.08em;
    }

    .welcome-title{
        color:#fff;
        font-size:clamp(1.8rem,3vw,3rem);
        font-weight:700;
        line-height:1.15;
        margin-bottom:.75rem;
    }

    .welcome-subtitle{
        color:rgba(255,255,255,.86);
        font-size:1rem;
        max-width:560px;
    }

    .welcome-date{
        color:#eafaff;
        background:rgba(222,245,255,.12);
        border:1px solid rgba(222,245,255,.24);
        border-radius:999px;
        backdrop-filter:blur(10px);
        -webkit-backdrop-filter:blur(10px);
    }

    @media(max-width:768px){

        .welcome-banner{
            min-height:220px;
        }

        .welcome-title{
            font-size:2rem;
        }
    }
</style>

<section class="welcome-banner d-flex align-items-center p-6 p-lg-8">
    <div class="welcome-banner-content">
        <div class="welcome-kicker mb-2">
            Infinite Workspace
        </div>
        <h1 class="welcome-title">
            <?= $greeting; ?>,
            <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?>
        </h1>
        <p class="welcome-subtitle mb-4">
            Welcome back. Your dashboard is ready, and everything you need is one step away.
        </p>
        <div class="welcome-date d-inline-flex align-items-center px-4 py-2 fw-semibold">
            <i class="bi bi-calendar3 me-2"></i>
            <?= date('l, F j, Y'); ?>
        </div>
    </div>
</section>
