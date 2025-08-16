<?php
class CPG_API {

    public static function get_photos($user_id, $per_page, $cache_time) {
        $cache_key = 'cpg_photos_' . md5($user_id . '_' . $per_page);
        $photos = get_transient($cache_key);
        if (false !== $photos) {
            return $photos;
        }
        $api_url = add_query_arg(array(
            'author' => $user_id,
            'per_page' => $per_page,
            '_embed' => 'wp:featuredmedia'
        ), 'https://wordpress.org/photos/wp-json/wp/v2/photos/');

        $response = wp_safe_remote_get($api_url, array('timeout' => 15));
        if (is_wp_error($response)) return $response;

        $body = wp_remote_retrieve_body($response);
        $photos = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', __('Invalid response from API', 'contributor-photo-gallery'));
        }

        set_transient($cache_key, $photos, $cache_time);
        return $photos;
    }
}
