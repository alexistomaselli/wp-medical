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
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo-horizontal-3.png"
                            alt="<?php bloginfo('name'); ?>" class="main-logo">
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