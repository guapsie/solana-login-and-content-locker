<?php
/**
 * SLCL - Shortcodes
 */

if ( ! defined( 'WPINC' ) ) {
    die( 'Access denied.' );
}

// Defensive programming: Prevent fatal errors if the class is loaded twice or redeclared.
if ( ! class_exists( 'SLCL_Shortcodes' ) ) {

    class SLCL_Shortcodes {

        public function render_login_button( $atts ) {
            wp_enqueue_style( 'slcl-style' );
            wp_enqueue_script( 'slcl-script' );

            if ( is_user_logged_in() ) {
                $logout_url = wp_logout_url( home_url() ); 
                return '<div class="slcl-wrapper" style="--slcl-bg: #111111; --slcl-text: #ffffff; --slcl-radius: 8px;">
                            <a href="' . esc_url( $logout_url ) . '" class="slcl-pill slcl-logout-pill">
                                <span class="dashicons dashicons-no-alt" style="margin-top: 2px;"></span> Log Out
                            </a>
                        </div>';
            }

            ob_start(); ?>
        
        <div class="slcl-wrapper" style="--slcl-bg: #6400CC; --slcl-text: #ffffff; --slcl-radius: 8px;">
            <button class="slcl-main-btn slcl-pill slcl-login-pill" style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%;">
                <span class="slcl-btn-text">Connect Phantom</span>
            </button>
            <p class="slcl-status-message" style="display:none; margin-top: 10px; font-size: 12px; font-weight: bold; text-align: center; text-transform: uppercase;"></p>
        </div>

        <?php
        return ob_get_clean();
    }

    public function enqueue_scripts() {
        wp_enqueue_style( 'dashicons' );
        wp_enqueue_style( 'slcl-style', plugins_url( '../assets/css/slcl-style.css', __FILE__ ), array(), '1.0.0' );
        
        // Inject ghost skeleton styles safely (only once) into the head to avoid loop duplication.
        $ghost_css = "
            /* Hide padlock icons on grid items, but keep them on the modal */
            .slcl-locked-grid-item::after { display: none !important; content: none !important; }
            
            /* The ghost wrap applies a solid blur over the injected mock text */
            .slcl-ghost-wrap { 
                filter: blur(8px); 
                pointer-events: none; 
                user-select: none; 
                padding: 20px; 
            }
            .slcl-ghost-text { color: inherit; font-size: 1.1em; line-height: 1.6; margin-bottom: 20px; opacity: 0.8; }
            .slcl-ghost-img { width: 100%; height: 350px; background: rgba(128, 128, 128, 0.3); border-radius: 12px; margin: 30px 0; }
            ";
            wp_add_inline_style( 'slcl-style', $ghost_css );

            wp_enqueue_script( 'solana-web3', 'https://unpkg.com/@solana/web3.js@latest/lib/index.iife.min.js', array(), null, true );
            wp_enqueue_script( 'slcl-script', plugins_url( '../assets/js/slcl-frontend.js', __FILE__ ), array('jquery', 'solana-web3'), '1.0.0', true );
            
            wp_localize_script( 'slcl-script', 'slcl_ajax', array(
                'ajax_url'    => admin_url( 'admin-ajax.php' ),
                'nonce'       => wp_create_nonce( 'slcl_auth_nonce' ),
                'msg_success' => 'Access Granted',
                'msg_error'   => 'Validation failed'
            ) );
        }

        public function auto_protect_full_content( $content ) {
            // Only protect on the frontend.
            if ( is_admin() ) {
                return $content;
            }

            if ( get_post_meta( get_the_ID(), '_slcl_is_vip', true ) ) {
                
                if ( is_user_logged_in() ) {
                    return $content;
                }

                // 1. WE DO NOT EXTRACT ANY REAL CONTENT. ZERO LEAKS.
                // We merely count the words to maintain structural consistency for the illusion.
                $stripped_content = wp_strip_all_tags( $content );
                $total_words      = str_word_count( $stripped_content );
                
                // Perform a highly optimized check for images to conditionally render a fake image block.
                $has_images = preg_match( '/<img[^>]+>/i', $content );

                // 2. GENERATE PURE MOCK TEXT PARAGRAPHS PROPORTIONAL TO ORIGINAL LENGTH
                // We use an array of varied sentences to make the blurred text look natural.
                $mock_paragraphs = array(
                    "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Aenean lacinia bibendum nulla sed consectetur. Nulla vitae elit libero, a pharetra augue.",
                    "Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum. Sed posuere consectetur est at lobortis. Cras mattis consectetur purus sit amet fermentum.",
                    "Curabitur blandit tempus porttitor. Nullam quis risus eget urna mollis ornare vel eu leo. Vestibulum id ligula porta felis euismod semper. Maecenas sed diam eget risus varius blandit sit amet non magna.",
                    "Donec sed odio dui. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Sed posuere consectetur est at lobortis.",
                    "Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor."
                );
                
                // Calculate how many paragraphs we need (assuming ~40 words per paragraph).
                $num_paragraphs = max( 1, ceil( $total_words / 40 ) );
                // Cap at 6 paragraphs to prevent massive DOM injection.
                $num_paragraphs = min( $num_paragraphs, 6 );

                ob_start();
                ?>
                <div class="slcl-locked-grid-item" style="position: relative; overflow: hidden; margin: 20px 0; display: block; min-height: 120px; cursor: pointer;">
                    <div class="slcl-ghost-wrap">
                        <?php 
                        // Loop through and output the mock paragraphs
                        for ( $i = 0; $i < $num_paragraphs; $i++ ) {
                            // Cycle through the mock array to keep text looking varied
                            $current_mock = $mock_paragraphs[ $i % count( $mock_paragraphs ) ];
                            
                            echo '<div class="slcl-ghost-text">' . esc_html( $current_mock ) . '</div>';
                            
                            // Inject the image placeholder after the first paragraph if the original had media
                            if ( $i === 0 && $has_images ) {
                                echo '<div class="slcl-ghost-img"></div>';
                            }
                        }
                        ?>
                    </div>
                </div>
                <?php
                return ob_get_clean();
            }

            return $content;
        }
    }
}