<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CPG_API {

    public static function get_photos( $user_id, $per_page = 12, $cache_time = 3600 ) {
        $user_id    = preg_replace('/[^0-9]/', '', (string) $user_id );
        $per_page   = max(1, min(50, (int)$per_page));
        $cache_time = max(60, (int)$cache_time);

        $cache_key = sprintf( 'cpg_photos_%s_%d', $user_id, $per_page );
        $cached = get_transient( $cache_key );
        if ( false !== $cached ) {
            return $cached;
        }

        $api_url = add_query_arg(
            array(
                'author'   => $user_id,
                'per_page' => $per_page,
                '_embed'   => 'wp:featuredmedia',
            ),
            'https://wordpress.org/photos/wp-json/wp/v2/photos/'
        );

        $response = wp_safe_remote_get( $api_url, array( 'timeout' => 15 ) );
        if ( is_wp_error( $response ) ) return $response;

        $body = wp_remote_retrieve_body( $response );
        $photos = json_decode( $body, true );

        if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $photos ) ) {
            return new WP_Error( 'json_error', __( 'Invalid response from API', 'contributor-photo-gallery' ) );
        }

        set_transient( $cache_key, $photos, $cache_time );
        return $photos;
    }
}
