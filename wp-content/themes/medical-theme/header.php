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
                    <?php
                    if (is_front_page() && is_home()):
                        ?>
                        <h1 class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                                <?php bloginfo('name'); ?>
                            </a></h1>
                        <?php
                    else:
                        ?>
                        <p class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                                <?php bloginfo('name'); ?>
                            </a></p>
                        <?php
                    endif;
                    ?>
                </div>

                <nav id="site-navigation" class="main-navigation">
                    <ul>
                        <li><a href="<?php echo esc_url(home_url('/')); ?>">Inicio</a></li>
                        <li><a href="<?php echo esc_url(home_url('/medicos/')); ?>">Staff Médico</a></li>
                    </ul>
                </nav>

                <div class="header-cta">
                    <div class="header-cart">
                        <a class="cart-customlocation" href="<?php echo wc_get_cart_url(); ?>"
                            title="<?php _e('View your shopping cart'); ?>">
                            <span class="dashicons dashicons-cart"></span>
                            <span
                                class="cart-contents-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
                        </a>
                    </div>
                    <a href="<?php echo esc_url(home_url('/medicos/')); ?>" class="btn-primary">RESERVAR CITA</a>
                </div>
            </div>
        </header>

    </div>
<?php wp_footer(); ?>
</body>
</html>