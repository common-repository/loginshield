<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://loginshield.com/
 * @since      1.0.0
 *
 * @package    LoginShield
 * @subpackage LoginShield/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    LoginShield
 * @subpackage LoginShield/public
 * @author     Luka Modric <lukamodric.world@gmail.com>
 */
class LoginShield_Public {

	/**
	 * The unique ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The unique ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The display name of this plugin.
	 *
	 * @since    1.0.8
	 * @access   private
	 * @var      string    $plugin_display_name    The display name of this plugin.
	 */
	private $plugin_display_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $plugin_display_name ) {

		$this->plugin_name = $plugin_name;
		$this->plugin_display_name = $plugin_display_name;
		$this->version = $version;

        add_action( 'login_form_login', array( $this, 'redirect_to_custom_login' ) );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in LoginShield_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The LoginShield_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        wp_enqueue_style( $this->plugin_name . 'snackbar', LOGINSHIELD_PLUGIN_URL . 'public/css/snackbar.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, LOGINSHIELD_PLUGIN_URL . 'public/css/loginshield-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in LoginShield_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The LoginShield_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        wp_enqueue_script( $this->plugin_name . 'snackbar', LOGINSHIELD_PLUGIN_URL . 'public/js/snackbar.js', array( 'jquery' ), $this->version, false );
        wp_enqueue_script( $this->plugin_name . 'realmClientBrowser', LOGINSHIELD_PLUGIN_URL . 'public/js/realm-client-browser.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . 'loginShieldPublic', LOGINSHIELD_PLUGIN_URL . 'public/js/loginshield-public.js', array( 'jquery' ), $this->version, false );

        wp_localize_script( $this->plugin_name . 'loginShieldPublic', 'loginShieldPublicAjax', array(
            'ajax_url'  => admin_url( 'admin-ajax.php' ),
            'site_url'  => get_site_url(),
            'api_base'  => esc_url_raw(rest_url()),
            'nonce'     => wp_create_nonce( 'wp_rest' )
        ));
	}

    /**
     * Redirect the user to the custom login page instead of wp-login.php.
     *
     * @since 1.0.3
     */
    public function redirect_to_custom_login() {
        if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
            $redirect_to = isset($_REQUEST['redirect_to']) && wp_validate_redirect($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : '';
            $reauth = isset( $_REQUEST['reauth'] ) && filter_var($_REQUEST['reauth'], FILTER_VALIDATE_BOOLEAN );

            if ( is_user_logged_in() && !$reauth ) {
                $this->redirect_logged_in_user( $redirect_to );
                exit;
            }

            // The rest are redirected to the login page
            $login_page_id = get_option( 'loginshield_login_page' );
            $login_url = get_permalink( $login_page_id );
            if ($redirect_to) {
                $login_url = add_query_arg( 'redirect_to', $redirect_to, $login_url );
            }
            $login_url = add_query_arg( 't', time(), $login_url );

            wp_redirect( $login_url );
            exit;
        }
    }

    private function redirect_logged_in_user( $redirect_to = null ) {
	    $user = wp_get_current_user();
        if ($redirect_to) {
            $redirect_to = add_query_arg( 't', time(), $redirect_to );
            wp_safe_redirect($redirect_to);
        } else {
            if (user_can($user, 'manage_options')) {
                wp_redirect(admin_url());
            } else {
                wp_redirect(get_dashboard_url());
            }
        }
    }

}
