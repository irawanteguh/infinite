<?php

namespace App\Libraries;

use App\Models\Modelrouting;

class Routingsystem
{
    protected $session;
    protected $uri;
    protected $model;

    protected $segment1 = '';
    protected $segment2 = '';

    protected $resultmenu = [];

    protected $pageTitle = 'Infinite';
    protected $activeMenu = [];

    protected $children = [];
    protected $activeCache = [];

    protected $publicRoutes = [
        '',
        'auth',
        'booking',
        'public',
        'api',
        'cron',
        'callback',
        'webhook',
    ];

    protected $publicRoutePairs = [
        'additional/welcomepage',
        'developer/testingpage',
    ];

    public function __construct()
    {
        $this->session = session();
        $this->uri     = service('uri');
        $this->model   = new Modelrouting();
    }

    /**
     * Initialize routing system
     */
    public function system(): array
    {
        $this->init();

        $this->checkSession();

        return $this->category();
    }

    /**
     * Initialize routing data
     */
    protected function init(): void
    {
        $this->children    = [];
        $this->activeCache = [];
        $this->activeMenu  = [];
        $this->pageTitle   = 'Infinite';

        $segments = $this->uri->getSegments();

        $this->segment1 = (string) ($segments[0] ?? '');
        $this->segment2 = (string) ($segments[1] ?? '');

        $this->resultmenu = $this->model->menu();

        $this->buildIndex();
    }

    /**
     * Check authentication
     */
    protected function checkSession(): void
    {
        $segment = $this->segment1;

        $route = trim(
            $segment . '/' . $this->segment2,
            '/'
        );

        if (in_array($segment, $this->publicRoutes, true)) {
            return;
        }

        if (in_array($route, $this->publicRoutePairs, true)) {
            return;
        }

        if ($this->isLoggedIn()) {
            return;
        }

        $this->session->setFlashdata(
            'redirect_url',
            current_url()
        );

        redirect()->to(site_url('/'))->send();

        exit;
    }

    /**
     * Check login status
     */
    protected function isLoggedIn(): bool
    {
        return (bool) $this->session->get('loggedin');
    }

    /**
     * Build menu index
     */
    protected function buildIndex(): void
    {
        foreach ($this->resultmenu as $menu) {

            $parentId = trim(
                (string) ($menu['modules_header_id'] ?? '')
            );

            if ($parentId !== '') {
                $this->children[$parentId][] = $menu;
            }

            if ($this->menuActive($menu)) {

                $this->activeMenu = $menu;

                $this->pageTitle = (string) (
                    $menu['modules_name'] ?? 'Infinite'
                );
            }
        }
    }

    /**
     * Build global template variables
     */
    protected function category(): array
    {
        return [
            'menu'             => $this->buildSidebar(),
            'menunavbar'       => $this->buildNavbar(),
            'menuorganization' => $this->buildOrganization(),
            'pageTitle'        => $this->escape($this->pageTitle),
            'activeMenu'       => $this->activeMenu,
        ];
    }

    /**
     * Build sidebar
     */
    protected function buildSidebar(): string
    {
        $html = '';

        /*
         * Root header
         */
        foreach ($this->resultmenu as $menu) {

            if (!$this->isRootHeader($menu)) {
                continue;
            }

            $html .= '
                <div class="menu-item">
                    <div class="menu-content pb-2">
                        <span class="menu-section text-muted text-uppercase fs-8 ls-1 fw-bolder">
                            ' . $this->escape(
                                $menu['modules_name'] ?? ''
                            ) . '
                        </span>
                    </div>
                </div>';

            $id = $this->menuId($menu);

            $html .= $this->generateSidebar(
                $id,
                [
                    $id => true
                ]
            );
        }

        /*
         * Top level menu
         */
        foreach ($this->resultmenu as $menu) {

            if (!$this->isTopLevelMenu($menu)) {
                continue;
            }

            $html .= $this->generateSidebarItem($menu);
        }

        return $html;
    }

    /**
     * Build organization menu
     */
    protected function buildOrganization(): string
    {
        $html = '';

        $allowedPackage = [
            'organization',
            'hr',
        ];

        foreach ($this->resultmenu as $menu) {

            if (!in_array(
                (string) ($menu['package'] ?? ''),
                $allowedPackage,
                true
            )) {
                continue;
            }

            if (trim(
                (string) ($menu['def_controller'] ?? '')
            ) === '') {
                continue;
            }

            if (
                (string) ($menu['parent'] ?? '') === 'H'
            ) {
                continue;
            }

            $active = $this->menuActive($menu)
                ? 'active'
                : '';

            $html .= '
                <li class="nav-item">
                    <a class="nav-link text-active-primary me-6 '
                . $active
                . '"
                        href="'
                . $this->escape(
                    $this->menuUrl($menu)
                )
                . '">
                        '
                . $this->escape(
                    $menu['modules_name'] ?? ''
                )
                . '
                    </a>
                </li>';
        }

        return $html;
    }

    /**
     * Build navbar
     */
    protected function buildNavbar(): string
    {
        $html = '';

        /*
         * Root header
         */
        foreach ($this->resultmenu as $menu) {

            if (!$this->isRootHeader($menu)) {
                continue;
            }

            $html .= $this->generateNavbar($menu);
        }

        /*
         * Top level menu
         */
        foreach ($this->resultmenu as $menu) {

            if (!$this->isTopLevelMenu($menu)) {
                continue;
            }

            $html .= $this->generateNavbar($menu);
        }

        return $html;
    }

    /**
     * Generate sidebar children
     */
    protected function generateSidebar(
        string $parentId,
        array $visited = []
    ): string {

        $html = '';

        foreach (
            $this->children[$parentId] ?? []
            as $menu
        ) {

            $id = $this->menuId($menu);

            if (
                $id === ''
                || isset($visited[$id])
            ) {
                continue;
            }

            $hasChild = !empty(
                $this->children[$id]
            );

            $isOpen = $this->isMenuActive($id);

            $isActive = $this->menuActive($menu);

            $nextVisited = $visited + [
                $id => true,
            ];

            if ($hasChild) {

                $html .= '
                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion '
                    . ($isOpen ? 'show' : '')
                    . '">

                        <span class="menu-link '
                    . ($isOpen ? 'active' : '')
                    . '">

                            '
                    . $this->menuLeading($menu)
                    . '

                            <span class="menu-title">
                                '
                    . $this->escape(
                        $menu['modules_name'] ?? ''
                    )
                    . '
                            </span>

                            <span class="menu-arrow"></span>

                        </span>

                        <div class="menu-sub menu-sub-accordion">
                            '
                    . $this->generateSidebar(
                        $id,
                        $nextVisited
                    )
                    . '
                        </div>

                    </div>';

                continue;
            }

            $html .= '
                <div class="menu-item">

                    <a class="menu-link '
                . ($isActive ? 'active' : '')
                . '"
                        href="'
                . $this->escape(
                    $this->menuUrl($menu)
                )
                . '">

                        '
                . $this->menuLeading($menu)
                . '

                        <span class="menu-title">
                            '
                . $this->escape(
                    $menu['modules_name'] ?? ''
                )
                . '
                        </span>

                    </a>

                </div>';
        }

        return $html;
    }

    /**
     * Generate top level sidebar item
     */
    protected function generateSidebarItem(
        array $menu,
        array $visited = []
    ): string {

        $id = $this->menuId($menu);

        if (
            $id === ''
            || isset($visited[$id])
        ) {
            return '';
        }

        $hasChild = !empty(
            $this->children[$id]
        );

        $isOpen = $this->isMenuActive($id);

        $isActive = $this->menuActive($menu);

        $nextVisited = $visited + [
            $id => true,
        ];

        if ($hasChild) {

            return '
                <div data-kt-menu-trigger="click"
                    class="menu-item menu-accordion '
                . ($isOpen ? 'show' : '')
                . '">

                    <span class="menu-link '
                . ($isOpen ? 'active' : '')
                . '">

                        '
                . $this->menuLeading($menu)
                . '

                        <span class="menu-title">
                            '
                . $this->escape(
                    $menu['modules_name'] ?? ''
                )
                . '
                        </span>

                        <span class="menu-arrow"></span>

                    </span>

                    <div class="menu-sub menu-sub-accordion">
                        '
                . $this->generateSidebar(
                    $id,
                    $nextVisited
                )
                . '
                    </div>

                </div>';
        }

        return '
            <div class="menu-item">

                <a class="menu-link '
            . ($isActive ? 'active' : '')
            . '"
                    href="'
            . $this->escape(
                $this->menuUrl($menu)
            )
            . '">

                    '
            . $this->menuLeading($menu)
            . '

                    <span class="menu-title">
                        '
            . $this->escape(
                $menu['modules_name'] ?? ''
            )
            . '
                    </span>

                </a>

            </div>';
    }

    /**
     * Generate navbar item
     */
    protected function generateNavbar(
        array $menu
    ): string {

        $id = $this->menuId($menu);

        $children = $this->children[$id] ?? [];

        $isActive = $this->menuActive($menu);

        $isOpen =
            $isActive
            || $this->isMenuActive($id);

        if (empty($children)) {

            return '
                <div class="menu-item me-lg-1">

                    <a href="'
                . $this->escape(
                    $this->menuUrl($menu)
                )
                . '"
                        class="menu-link py-3 '
                . ($isActive ? 'active' : '')
                . '">

                        <span class="menu-title">
                            '
                . $this->escape(
                    $menu['modules_name'] ?? ''
                )
                . '
                        </span>

                    </a>

                </div>';
        }

        $html = '
            <div data-kt-menu-trigger="{default:\'click\', lg:\'hover\'}"
                data-kt-menu-placement="bottom-start"
                class="menu-item menu-lg-down-accordion me-lg-1">

                <span class="menu-link py-3 '
            . ($isOpen ? 'active' : '')
            . '">

                    <span class="menu-title">
                        '
            . $this->escape(
                $menu['modules_name'] ?? ''
            )
            . '
                    </span>

                    <span class="menu-arrow d-lg-none"></span>

                </span>

                <div class="menu-sub menu-sub-lg-dropdown menu-rounded-0 py-lg-4 w-lg-225px">';

        foreach ($children as $child) {

            $html .= $this->generateNavbarChild(
                $child,
                [
                    $id => true,
                ]
            );
        }

        return $html . '
                </div>
            </div>';
    }

    /**
     * Generate navbar child
     */
    protected function generateNavbarChild(
        array $menu,
        array $visited = []
    ): string {

        $id = $this->menuId($menu);

        if (
            $id === ''
            || isset($visited[$id])
        ) {
            return '';
        }

        $children = $this->children[$id] ?? [];

        $isActive = $this->menuActive($menu);

        $isOpen =
            $isActive
            || $this->isMenuActive($id);

        $nextVisited = $visited + [
            $id => true,
        ];

        if (empty($children)) {

            return '
                <div class="menu-item">

                    <a href="'
                . $this->escape(
                    $this->menuUrl($menu)
                )
                . '"
                        class="menu-link py-3 '
                . ($isActive ? 'active' : '')
                . '">

                        '
                . $this->menuLeading($menu)
                . '

                        <span class="menu-title">
                            '
                . $this->escape(
                    $menu['modules_name'] ?? ''
                )
                . '
                        </span>

                    </a>

                </div>';
        }

        $html = '
            <div data-kt-menu-trigger="{default:\'click\', lg:\'hover\'}"
                data-kt-menu-placement="right-start"
                class="menu-item menu-lg-down-accordion">

                <span class="menu-link py-3 '
            . ($isOpen ? 'active' : '')
            . '">

                    '
            . $this->menuLeading($menu)
            . '

                    <span class="menu-title">
                        '
            . $this->escape(
                $menu['modules_name'] ?? ''
            )
            . '
                    </span>

                    <span class="menu-arrow"></span>

                </span>

                <div class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-active-bg py-lg-4 w-lg-225px">';

        foreach ($children as $child) {

            $html .= $this->generateNavbarChild(
                $child,
                $nextVisited
            );
        }

        return $html . '
                </div>
            </div>';
    }

    /**
     * Check whether menu has active child
     */
    protected function isMenuActive(
        string $id,
        array $visited = []
    ): bool {

        if (isset($this->activeCache[$id])) {
            return $this->activeCache[$id];
        }

        if (
            $id === ''
            || isset($visited[$id])
        ) {
            return false;
        }

        $visited[$id] = true;

        foreach (
            $this->children[$id] ?? []
            as $child
        ) {

            if (
                $this->menuActive($child)
                || $this->isMenuActive(
                    $this->menuId($child),
                    $visited
                )
            ) {

                $this->activeCache[$id] = true;

                return true;
            }
        }

        $this->activeCache[$id] = false;

        return false;
    }

    /**
     * Check current active menu
     */
    protected function menuActive(
        array $menu
    ): bool {

        return
            (string) ($menu['package'] ?? '')
                === $this->segment1
            &&
            (string) ($menu['def_controller'] ?? '')
                === $this->segment2;
    }

    /**
     * Generate menu URL
     */
    protected function menuUrl(
        array $menu
    ): string {

        $package = (string) (
            $menu['package'] ?? ''
        );

        $controller = (string) (
            $menu['def_controller'] ?? ''
        );

        if (
            $package === ''
            || $controller === ''
        ) {
            return '#';
        }

        return site_url(
            $package . '/' . $controller
        );
    }

    /**
     * Get menu ID
     */
    protected function menuId(
        array $menu
    ): string {

        return trim(
            (string) (
                $menu['modules_id'] ?? ''
            )
        );
    }

    /**
     * Generate menu icon
     */
    protected function menuLeading(
        array $menu
    ): string {

        $icon = trim(
            (string) (
                $menu['icon'] ?? ''
            )
        );

        if ($icon !== '') {

            return '
                <span class="menu-icon">
                    <i class="'
                . $this->escape($icon)
                . ' fs-2"></i>
                </span>';
        }

        return '
            <span class="menu-bullet">
                <span class="bullet bullet-dot"></span>
            </span>';
    }

    /**
     * Check root header
     */
    protected function isRootHeader(
        array $menu
    ): bool {

        return
            empty(
                $menu['modules_header_id']
            )
            &&
            (string) (
                $menu['parent'] ?? ''
            ) === 'H';
    }

    /**
     * Check top level menu
     */
    protected function isTopLevelMenu(
        array $menu
    ): bool {

        return
            empty(
                $menu['modules_header_id']
            )
            &&
            (string) (
                $menu['parent'] ?? ''
            ) !== 'H';
    }

    /**
     * Escape HTML
     */
    protected function escape($value): string
    {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES,
            'UTF-8'
        );
    }
}