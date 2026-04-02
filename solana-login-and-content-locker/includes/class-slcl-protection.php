<?php
/**
 * SLCL - Content Protection Class
 */

if ( ! defined( 'WPINC' ) ) {
    die( 'Access denied.' );
}

class SLCL_Protection {

    public function init() {
        if ( is_admin() || wp_is_json_request() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
            add_action( 'add_meta_boxes', array( $this, 'add_vip_meta_box' ) );
            add_action( 'save_post', array( $this, 'save_vip_meta_box_data' ) );
            return; 
        }

        add_filter( 'body_class', array( $this, 'lock_entire_page_class' ) );
        add_filter( 'post_class', array( $this, 'lock_grid_items_class' ), 10, 3 );
        add_action( 'wp_footer', array( $this, 'inject_nuclear_protection' ), 999 );
    }

    public function add_vip_meta_box() {
        $post_types = get_post_types( array( 'public' => true ) );
        foreach ( $post_types as $post_type ) {
            add_meta_box(
                'slcl_vip_protection',
                __( 'Solana Locker - Access Control', 'slcl' ),
                array( $this, 'render_meta_box_html' ),
                $post_type,
                'side',
                'high'
            );
        }
    }

    public function render_meta_box_html( $post ) {
        wp_nonce_field( 'slcl_save_vip_meta', 'slcl_vip_meta_nonce' );
        $is_vip = get_post_meta( $post->ID, '_slcl_is_vip', true );
        
        echo '<div style="padding: 10px 0;">';
        echo '<label for="slcl_is_vip" style="font-weight: bold; font-size: 14px;">';
        echo '<input type="checkbox" id="slcl_is_vip" name="slcl_is_vip" value="1" ' . checked( 1, $is_vip, false ) . ' /> ';
        echo esc_html__( 'Protect this content (Web3 Login required)', 'slcl' );
        echo '</label>';
        echo '<p class="description" style="margin-top: 10px; font-size: 13px; line-height: 1.4;">';
        echo esc_html__( 'Checking this box locks the post content. Users must connect their wallet to view it.', 'slcl' );
        echo '</p></div>';
    }

    public function save_vip_meta_box_data( $post_id ) {
        if ( ! isset( $_POST['slcl_vip_meta_nonce'] ) ) return;
        
        $nonce = sanitize_text_field( wp_unslash( $_POST['slcl_vip_meta_nonce'] ) );
        if ( ! wp_verify_nonce( $nonce, 'slcl_save_vip_meta' ) ) return;
        
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        if ( isset( $_POST['slcl_is_vip'] ) ) {
            update_post_meta( $post_id, '_slcl_is_vip', 1 );
        } else {
            delete_post_meta( $post_id, '_slcl_is_vip' );
        }
    }

    private function is_user_vip() {
        return is_user_logged_in();
    }

    public function lock_entire_page_class( $classes ) {
        return $classes;
    }

    public function lock_grid_items_class( $classes, $class, $post_id ) {
        if ( ! is_singular() && get_post_meta( $post_id, '_slcl_is_vip', true ) && ! $this->is_user_vip() ) {
            $classes[] = 'slcl-locked-grid-item';
        }
        return $classes;
    }

    public function inject_nuclear_protection() {
        if ( $this->is_user_vip() ) return;

        $locked_msg = get_option( 'slcl_msg_error', 'Wallet not authorized.' );
        $login_button = do_shortcode( '[slcl_login]' );

        $locked_post_ids = get_posts( array(
            'post_type'      => 'any',
            'meta_key'       => '_slcl_is_vip',
            'meta_value'     => '1',
            'fields'         => 'ids',
            'posts_per_page' => -1
        ) );

        $locked_targets = array();
        if ( ! empty( $locked_post_ids ) ) {
            foreach ( $locked_post_ids as $pid ) {
                $locked_targets[] = wp_make_link_relative( get_permalink( $pid ) );
                $thumb_id = get_post_thumbnail_id( $pid );
                if ( $thumb_id ) {
                    $file = get_attached_file( $thumb_id );
                    if ( $file ) {
                        $filename = pathinfo( $file, PATHINFO_FILENAME ); 
                        if ( strlen( $filename ) > 2 ) $locked_targets[] = $filename;
                    }
                }
            }
        }
        $locked_targets = array_unique( array_filter( $locked_targets ) );

        ?>
        <style>
            body.slcl-full-page-lock article,
            body.slcl-full-page-lock .type-post,
            body.slcl-full-page-lock .type-page,
            body.slcl-full-page-lock .entry-content,
            body.slcl-full-page-lock .post-content,
            body.slcl-full-page-lock .elementor-location-single {
                filter: blur(20px) grayscale(40%) !important;
                pointer-events: none !important;
                user-select: none !important;
                overflow: hidden !important;
            }

            .slcl-force-blur {
                filter: blur(20px) grayscale(70%) !important;
                transition: filter 0.3s ease;
                pointer-events: none !important;
                user-select: none !important;
            }
            .slcl-locked-grid-item {
                position: relative !important;
                cursor: pointer !important;
                display: block;
                overflow: hidden;
            }
            .slcl-locked-grid-item::after {
                content: '\f160'; 
                font-family: 'dashicons'; 
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                font-size: clamp(30px, 8vw, 55px);
                line-height: 1;
                color: #ffffff;
                text-shadow: 0 4px 12px rgba(0,0,0,0.8);
                z-index: 99;
                pointer-events: none;
            }
            #slcl-premium-modal {
                position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(5px);
                z-index: 999999; display: flex; align-items: center; justify-content: center;
                opacity: 0; pointer-events: none; transition: opacity 0.3s ease;
            }
            #slcl-premium-modal.slcl-modal-active { opacity: 1; pointer-events: auto; }
            .slcl-modal-content {
                background: #111111; border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 16px; padding: 40px; max-width: 450px; width: 90%;
                text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.5);
                position: relative; transform: translateY(20px); transition: transform 0.3s ease;
            }
            #slcl-premium-modal.slcl-modal-active .slcl-modal-content { transform: translateY(0); }
            
            .slcl-modal-close {
                position: absolute; top: 15px; right: 20px; color: #888; font-size: 24px;
                cursor: pointer; transition: color 0.2s;
            }
            .slcl-modal-close:hover { color: #fff; }
            .slcl-modal-icon { font-size: 50px; color: #fff; margin-bottom: 20px; }
            .slcl-modal-title { color: #fff; font-size: 20px; font-weight: 700; margin-bottom: 25px; }
            .slcl-modal-actions { display: flex; flex-direction: column; gap: 15px; align-items: center; }
        </style>

        <div id="slcl-premium-modal">
            <div class="slcl-modal-content">
                <span class="slcl-modal-close" id="slcl-close-modal">&times;</span>
                <span class="dashicons dashicons-lock slcl-modal-icon" style="font-size: 50px; width: 50px; height: 50px; display: inline-block; color: #fff; margin-bottom: 20px;"></span>
                <h3 class="slcl-modal-title"><?php echo esc_html( $locked_msg ); ?></h3>
                <div class="slcl-modal-actions">
                    <div style="width: 100%; display: flex; justify-content: center;">
                        <?php echo wp_kses_post( $login_button ); ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modal = document.getElementById('slcl-premium-modal');
                const closeBtn = document.getElementById('slcl-close-modal');

                if (document.body.classList.contains('slcl-full-page-lock')) {
                    if (modal) modal.classList.add('slcl-modal-active');
                }

                if (closeBtn && modal) {
                    closeBtn.addEventListener('click', () => modal.classList.remove('slcl-modal-active'));
                    modal.addEventListener('click', (e) => {
                        if (e.target === modal) {
                            modal.classList.remove('slcl-modal-active');
                        }
                    });
                }

                const lockedTargets = <?php echo json_encode( array_values( $locked_targets ) ); ?>;
                if (lockedTargets.length > 0) {
                    document.querySelectorAll('a, img, div').forEach(el => {
                        
                        if (el.closest('header, nav, footer, .menu, .main-navigation, .nav-menu, .site-header, .site-footer, #site-navigation')) return;

                        let targetStr = el.href || el.src || '';
                        if (el.dataset) {
                            if (el.dataset.src) targetStr += ' ' + el.dataset.src;
                            if (el.dataset.full) targetStr += ' ' + el.dataset.full;
                        }
                        if (!targetStr && el.style && el.style.backgroundImage) {
                            targetStr = el.style.backgroundImage;
                        }
                        if (!targetStr) return;

                        lockedTargets.forEach(target => {
                            if (targetStr.includes(target)) {
                                let wrapper = el.tagName === 'A' ? el : (el.closest('a') || el.parentElement);
                                
                                if (wrapper === document.body || wrapper === document.documentElement) return;

                                wrapper.classList.add('slcl-locked-grid-item');
                                let imgToBlur = wrapper.querySelector('img') || el;
                                if(imgToBlur && imgToBlur.tagName === 'IMG') {
                                    imgToBlur.classList.add('slcl-force-blur');
                                }
                            }
                        });
                    });
                }

                document.body.addEventListener('click', function(e) {
                    const lockedItem = e.target.closest('.slcl-locked-grid-item');
                    if (lockedItem && modal) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        modal.classList.add('slcl-modal-active');
                    }
                }, true);
            });
        </script>
        <?php
    }
}