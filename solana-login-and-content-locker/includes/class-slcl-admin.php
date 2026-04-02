<?php
/**
 * SLCL - Admin Settings Class
 * Handles the creation of the admin menu, settings pages, and option registration.
 *
 * @package    SLCL
 */

if ( ! defined( 'WPINC' ) ) {
    die( 'Access denied.' );
}

class SLCL_Admin {

    public function init() {
        add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function add_plugin_admin_menu() {
        add_menu_page(
            __( 'Solana Web3', 'slcl' ),
            __( 'Solana Web3', 'slcl' ),
            'manage_options',
            'slcl-settings',
            array( $this, 'display_settings_page' ),
            'dashicons-unlock', 
            80
        );
    }

    public function register_settings() {
        // --- FRONTEND UI SETTINGS (Only text fields left) ---
        register_setting( 'slcl-settings-group', 'slcl_btn_text', 'sanitize_text_field' );
        register_setting( 'slcl-settings-group', 'slcl_msg_success', 'sanitize_text_field' );
        register_setting( 'slcl-settings-group', 'slcl_msg_error', 'sanitize_text_field' );

        add_settings_section( 'slcl_ui_section', __( 'Frontend UI Settings', 'slcl' ), null, 'slcl-settings' );

        add_settings_field( 'slcl_btn_text', __( 'Button Text', 'slcl' ), array( $this, 'render_text_field' ), 'slcl-settings', 'slcl_ui_section', array( 'label_for' => 'slcl_btn_text', 'default' => 'Connect Phantom' ) );
        add_settings_field( 'slcl_msg_success', __( 'Success Message', 'slcl' ), array( $this, 'render_text_field' ), 'slcl-settings', 'slcl_ui_section', array( 'label_for' => 'slcl_msg_success', 'default' => 'Access Granted.' ) );
        add_settings_field( 'slcl_msg_error', __( 'Error Message', 'slcl' ), array( $this, 'render_text_field' ), 'slcl-settings', 'slcl_ui_section', array( 'label_for' => 'slcl_msg_error', 'default' => 'Wallet not authorized.' ) );
    }

    public function render_text_field( $args ) {
        $option_name = $args['label_for'];
        $default     = isset( $args['default'] ) ? $args['default'] : '';
        $value       = get_option( $option_name, $default );
        
        echo '<input type="text" id="' . esc_attr( $option_name ) . '" name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $value ) . '" class="regular-text" />';
        
        if ( isset( $args['description'] ) ) {
            echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
        }
    }

    public function display_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'slcl-settings-group' );
                do_settings_sections( 'slcl-settings' );
                submit_button( __( 'Save Settings', 'slcl' ) );
                ?>
            </form>
        </div>
        <?php
    }
}