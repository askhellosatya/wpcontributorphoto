<?php
class CPG_Cache {

    public static function clear() {
        global $wpdb;

        // Use esc_like to build the LIKE pattern safely for the current DB prefix.
        $like = $wpdb->esc_like('_transient_cpg_photos_') . '%';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $transient_names = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
                $like
            )
        );

        if (empty($transient_names)) {
            return;
        }

        foreach ($transient_names as $transient_name) {
            // stored names are like "_transient_cpg_photos_<hash>"
            $name = str_replace('_transient_', '', $transient_name);
            delete_transient($name);
        }
    }
}

/**
 * Backwards-compatible procedural wrapper.
 * Some older code or external integrations might call cpg_clear_photo_cache().
 */
if (!function_exists('cpg_clear_photo_cache')) {
    function cpg_clear_photo_cache() {
        CPG_Cache::clear();
    }
}
