<?php
/**
 * SLCL - Authentication Class
 * Handles AJAX requests, signature verification, and user authentication.
 *
 * @package    SLCL
 */

if ( ! defined( 'WPINC' ) ) {
    die( 'Access denied.' );
}

class SLCL_Auth {

    public function verify_wallet_signature() {
        if ( ! check_ajax_referer( 'slcl_auth_nonce', 'security', false ) ) {
            wp_send_json_error( 'Invalid security token. Please refresh the page and try again.' );
        }

        $wallet_pubkey = isset( $_POST['wallet'] ) ? sanitize_text_field( wp_unslash( $_POST['wallet'] ) ) : '';
        $signature_b64 = isset( $_POST['signature'] ) ? sanitize_text_field( wp_unslash( $_POST['signature'] ) ) : '';
        $nonce         = isset( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';

        if ( empty( $wallet_pubkey ) || empty( $signature_b64 ) ) {
            wp_send_json_error( 'Missing wallet or signature data.' );
        }

        // MUST perfectly match the message defined in slcl-frontend.js
        $message = "Solana Locker Auth.\nNonce: " . $nonce;

        $signature = base64_decode( $signature_b64 );
        $public_key = $this->decode_base58( $wallet_pubkey );

        if ( ! $signature || ! $public_key ) {
            wp_send_json_error( 'Data decoding failed.' );
        }

        if ( ! function_exists( 'sodium_crypto_sign_verify_detached' ) ) {
            wp_send_json_error( 'Server configuration error: libsodium extension is missing.' );
        }

        $is_valid = sodium_crypto_sign_verify_detached( $signature, $message, $public_key );

        if ( ! $is_valid ) {
            wp_send_json_error( 'Invalid cryptographic signature.' );
        }

        $user = get_user_by( 'login', $wallet_pubkey );

        if ( ! $user ) {
            $user_id = wp_insert_user( array(
                'user_login' => $wallet_pubkey,
                'user_pass'  => wp_generate_password( 24, false ),
                'role'       => 'subscriber' 
            ) );

            if ( is_wp_error( $user_id ) ) {
                wp_send_json_error( 'Error creating account.' );
            }
            
            $user = get_user_by( 'id', $user_id );
        }

        wp_set_current_user( $user->ID );
        wp_set_auth_cookie( $user->ID, true ); 
        
        do_action( 'wp_login', $user->user_login, $user ); 

        wp_send_json_success( 'Authentication successful.' );
    }

    private function decode_base58( $base58 ) {
        $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $base = strlen( $alphabet );
        
        if ( is_string( $base58 ) === false || strlen( $base58 ) === 0 ) {
            return false;
        }
        
        $indexes = array_flip( str_split( $alphabet ) );
        $chars = str_split( $base58 );
        
        foreach ( $chars as $char ) {
            if ( ! isset( $indexes[ $char ] ) ) {
                return false;
            }
        }
        
        $decoded = array( 0 );
        foreach ( $chars as $char ) {
            $carry = $indexes[ $char ];
            for ( $i = 0; $i < count( $decoded ); $i++ ) {
                $carry += $decoded[ $i ] * $base;
                $decoded[ $i ] = $carry & 0xff;
                $carry >>= 8;
            }
            while ( $carry > 0 ) {
                $decoded[] = $carry & 0xff;
                $carry >>= 8;
            }
        }
        
        for ( $i = 0; $i < strlen( $base58 ) && $base58[ $i ] === '1'; $i++ ) {
            $decoded[] = 0;
        }
        
        return implode( '', array_map( 'chr', array_reverse( $decoded ) ) );
    }
}