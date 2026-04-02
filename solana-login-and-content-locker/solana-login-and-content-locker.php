<?php
/**
 * Plugin Name:       Solana Login and Content Locker
 * Plugin URI:        https://guapsie.dev/solana-login-and-content-locker
 * Description:       Web3 authentication gateway using Solana wallets. Say goodbye to passwords.
 * Version:           1.0.0
 * Author:            guapsie
 * Author URI:        https://guapsie.dev
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       slcl
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 *
 * @package           SLCL
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die( 'Access denied.' );
}

// Define plugin constants
define( 'SLCL_VERSION', '1.0.0' );
define( 'SLCL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SLCL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function slcl_activate() {
    // Placeholder
}
register_activation_hook( __FILE__, 'slcl_activate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require SLCL_PLUGIN_DIR . 'includes/class-slcl-core.php';

/**
 * Begins execution of the plugin.
 */
function slcl_run() {
    $plugin = new SLCL_Core();
    $plugin->run();
}
slcl_run();