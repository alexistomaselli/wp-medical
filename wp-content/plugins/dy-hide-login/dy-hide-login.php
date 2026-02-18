<?php
/**
 * Plugin Name: DyD Hide Login
 * Description: Cambia la ruta de acceso al wp-admin por una personalizada para mejorar la seguridad.
 * Version: 1.0.0
 * Author: DyD Labs
 */

if (!defined('ABSPATH'))
    exit;

class DyD_Hide_Login
{
    private $slug;

    public function __construct()
    {
        $this->slug = get_option('dyd_hide_login_slug', 'admin-secreto');

        // Admin Menu
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);

        // Redirection Logic
        add_action('init', [$this, 'handle_hide_login'], 1);
        add_action('wp_loaded', [$this, 'protect_wp_admin']);

        // Link filtering
        add_filter('site_url', [$this, 'filter_site_url'], 10, 4);
        add_filter('network_site_url', [$this, 'filter_site_url'], 10, 4);
        add_filter('wp_redirect', [$this, 'filter_wp_redirect'], 10, 2);
    }

    public function add_admin_menu()
    {
        add_options_page(
            'Hide Login Config',
            '🛡️ Hide Login',
            'manage_options',
            'dyd-hide-login',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings()
    {
        register_setting('dyd_hide_login_group', 'dyd_hide_login_slug', 'sanitize_title');
    }

    public function render_settings_page()
    {
        ?>
        <div class="wrap">
            <h1>🛡️ DyD Hide Login — Seguridad</h1>
            <p>Cambia la URL de acceso a tu panel para evitar ataques de fuerza bruta.</p>

            <form method="post" action="options.php">
                <?php settings_fields('dyd_hide_login_group'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">URL de Acceso Personalizada</th>
                        <td>
                            <code><?php echo home_url('/'); ?></code>
                            <input type="text" name="dyd_hide_login_slug" value="<?php echo esc_attr($this->slug); ?>"
                                class="regular-text" />
                            <p class="description">Ejemplo: <code>mientrada-secreta</code>. No olvides guardarla, ya que
                                <code>wp-admin</code> quedará bloqueado.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>

            <div class="notice notice-info">
                <p>Tu URL actual de login es: <a href="<?php echo home_url($this->slug); ?>" target="_blank"><strong>
                            <?php echo home_url($this->slug); ?>
                        </strong></a></p>
            </div>
        </div>
        <?php
    }

    public function handle_hide_login()
    {
        if (is_admin())
            return;

        $request_path = untrailingslashit(str_replace(home_url(), '', home_url(add_query_arg([], $_SERVER['REQUEST_URI']))));
        $request_path = ltrim(parse_url($request_path, PHP_URL_PATH), '/');

        if ($request_path === $this->slug) {
            status_header(200);
            require_once ABSPATH . 'wp-login.php';
            exit;
        }
    }

    public function protect_wp_admin()
    {
        if (is_user_logged_in())
            return;

        $pagenow = $GLOBALS['pagenow'] ?? '';
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';

        // Bloquear wp-login.php directo
        if ($pagenow === 'wp-login.php' && !isset($_GET['action']) && !isset($_POST['log'])) {
            if (basename($_SERVER['PHP_SELF']) === 'wp-login.php' && $request_uri !== '/' . $this->slug) {
                wp_safe_redirect(home_url('404'), 302);
                exit;
            }
        }

        // Bloquear /wp-admin directo para no logueados
        if (is_admin() && !defined('DOING_AJAX') && !is_user_logged_in()) {
            wp_safe_redirect(home_url('404'), 302);
            exit;
        }
    }

    public function filter_site_url($url, $path, $scheme, $blog_id)
    {
        if (strpos($url, 'wp-login.php') !== false && (is_null($scheme) || $scheme === 'login' || $scheme === 'login_post')) {
            return str_replace('wp-login.php', $this->slug, $url);
        }
        return $url;
    }

    public function filter_wp_redirect($location, $status)
    {
        if (strpos($location, 'wp-login.php') !== false) {
            return str_replace('wp-login.php', $this->slug, $location);
        }
        return $location;
    }
}

new DyD_Hide_Login();
