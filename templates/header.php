<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- SEO: Prevent indexing of application pages -->
    <meta name="robots" content="noindex, nofollow" />

    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>ZCA Inventory</title>
    <?php wp_head(); ?>
</head>
<body class="zca-inventory-page">
    <?php
    $user_role = ZCA_Auth::get_user_role();
    $current_user = wp_get_current_user();
    $is_owner = ZCA_Roles::is_owner();
    $is_cashier = ZCA_Roles::is_cashier();
    ?>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="<?php echo home_url('/zca-inventory/dashboard'); ?>">
                <i class="bi bi-box-seam"></i> ZCA Inventory
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo !isset($active_page) || $active_page == 'dashboard' ? 'active' : ''; ?>"
                           href="<?php echo home_url('/zca-inventory/dashboard'); ?>">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>

                    <?php if ($is_owner): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isset($active_page) && $active_page == 'products' ? 'active' : ''; ?>"
                               href="<?php echo home_url('/zca-inventory/products'); ?>">
                                <i class="bi bi-box"></i> Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isset($active_page) && $active_page == 'cashiers' ? 'active' : ''; ?>"
                               href="<?php echo home_url('/zca-inventory/cashiers'); ?>">
                                <i class="bi bi-people"></i> Cashiers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isset($active_page) && $active_page == 'sales-report' ? 'active' : ''; ?>"
                               href="<?php echo home_url('/zca-inventory/sales-report'); ?>">
                                <i class="bi bi-graph-up"></i> Sales Report
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isset($active_page) && $active_page == 'inventory' ? 'active' : ''; ?>"
                               href="<?php echo home_url('/zca-inventory/inventory'); ?>">
                                <i class="bi bi-clipboard-data"></i> Inventory
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isset($active_page) && $active_page == 'settings' ? 'active' : ''; ?>"
                               href="<?php echo home_url('/zca-inventory/settings'); ?>">
                                <i class="bi bi-gear"></i> Settings
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($is_cashier): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isset($active_page) && $active_page == 'pos' ? 'active' : ''; ?>"
                               href="<?php echo home_url('/zca-inventory/pos'); ?>">
                                <i class="bi bi-cash-register"></i> Point of Sale
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> <?php echo $current_user->display_name; ?>
                            <span class="badge bg-light text-dark ms-1"><?php echo ucfirst($user_role); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li>
                                <a class="dropdown-item" href="<?php echo home_url('/zca-inventory/logout'); ?>">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
