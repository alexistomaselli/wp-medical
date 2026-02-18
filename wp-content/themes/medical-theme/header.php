<!doctype html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    <div id="page" class="site">
        <header id="masthead" class="site-header">
            <div class="header-container">
                <div class="site-branding">
                    <a href="<?php echo esc_url(home_url('/')); ?>" rel="home" class="logo-link">
                        <div class="logo-icon">
                            <svg viewBox="0 0 40 40">
                                <path d="M8 32V8H14L20 20L26 8H32V32H26V16L20 28L14 16V32H8Z" class="bg-m"></path>
                                <path d="M10 30V10H14L20 22L26 10H30V30H26V18L20 30L14 18V30H10Z"></path>
                                <rect class="fill-white" height="4" rx="1" width="4" x="18" y="16"></rect>
                                <rect class="fill-white" height="1" rx="0.5" width="10" x="15" y="17.5"></rect>
                            </svg>
                            <div class="add-badge">
                                <span class="material-icons">add</span>
                            </div>
                        </div>
                        <?php if (is_front_page() && is_home()): ?>
                            <h1 class="site-title"><?php bloginfo('name'); ?></h1>
                        <?php else: ?>
                            <p class="site-title"><?php bloginfo('name'); ?></p>
                        <?php endif; ?>
                    </a>
                </div>

                <div class="menu-toggle" aria-controls="site-navigation" aria-expanded="false" role="button"
                    tabindex="0">
                    <span class="material-symbols-outlined">menu</span>
                </div>

                <nav id="site-navigation" class="main-navigation">
                    <ul>
                        <li><a href="<?php echo esc_url(home_url('/')); ?>">Inicio</a></li>
                        <li><a href="<?php echo esc_url(home_url('/medicos')); ?>">Staff Médico</a></li>
                    </ul>
                    <div class="sidebar-cta">
                        <a href="<?php echo esc_url(home_url('/reservar-cita')); ?>" class="btn-primary">
                            <span class="material-symbols-outlined">calendar_month</span>
                            Reservar Cita
                        </a>
                    </div>
                </nav>

                <?php if (medical_is_woocommerce_activated()): ?>
                    <div class="header-cta">
                        <div class="header-cart">
                            <a class="cart-customlocation" href="<?php echo wc_get_cart_url(); ?>"
                                title="<?php _e('View your shopping cart'); ?>">
                                <span class="dashicons dashicons-cart"></span>
                                <span
                                    class="cart-contents-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </header>
    </div>