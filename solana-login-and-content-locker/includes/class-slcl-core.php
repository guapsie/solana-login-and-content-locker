<?php
/**
 * The core plugin class.
 *
 * This is the engine of the plugin. It loads dependencies, defines the locale,
 * and hooks all the separate classes to WordPress.
 *
 * @package    SLCL
 * @subpackage SLCL/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die( 'Access denied.' );
}

class SLCL_Core {

    protected $plugin_name;
    protected $version;

    public function __construct() {
        if ( defined( 'SLCL_VERSION' ) ) {
            $this->version = SLCL_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'slcl';

        $this->load_dependencies();
        $this->define_public_hooks();
        $this->define_admin_hooks();
        
        $plugin_protection = new SLCL_Protection();
        $plugin_protection->init();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        require_once SLCL_PLUGIN_DIR . 'includes/class-slcl-admin.php';
        require_once SLCL_PLUGIN_DIR . 'includes/class-slcl-shortcodes.php';
        require_once SLCL_PLUGIN_DIR . 'includes/class-slcl-auth.php';
        require_once SLCL_PLUGIN_DIR . 'includes/class-slcl-protection.php';
    }

    /**
     * Register all of the hooks related to the public-facing side of the site.
     */
    private function define_public_hooks() {
        $plugin_shortcodes = new SLCL_Shortcodes();
        $plugin_auth       = new SLCL_Auth();

        // 1. FRONTEND: Register the main login shortcode
        add_shortcode( 'slcl_login', array( $plugin_shortcodes, 'render_login_button' ) );
        
        // 2. FRONTEND: Enqueue CSS and Phantom JS
        add_action( 'wp_enqueue_scripts', array( $plugin_shortcodes, 'enqueue_scripts' ) );
        
        // 3. FRONTEND: Auto-protect content if VIP box is checked
        add_filter( 'the_content', array( $plugin_shortcodes, 'auto_protect_full_content' ), 99 );

        // 4. BACKEND: AJAX endpoint for signature verification
        add_action( 'wp_ajax_nopriv_slcl_verify_wallet', array( $plugin_auth, 'verify_wallet_signature' ) );
    }
    
    /**
     * Register all of the hooks related to the admin area functionality.
     */
    private function define_admin_hooks() {
        $plugin_admin = new SLCL_Admin();
        $plugin_admin->init();
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run() {
        // Core execution sequence initialized via constructor.
    }

    /**
     * The reference to the name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}