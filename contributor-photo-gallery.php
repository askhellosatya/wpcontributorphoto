<?php
/**
 * Plugin Name: WP Contributor Photo Gallery
 * Plugin URI: https://github.com/askhellosatya/wpcontributorphoto/
 * Description: Showcase your contributions to WordPress.org/photos with elegant and responsive photo galleries.
 * Version: 2.0.3
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * Author: Satyam Vishwakarma
 * Author URI: https://satyamvishwakarma.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpcontributorphoto 
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPCONTRIBUTORPHOTO_VERSION', '2.0.3');
define('WPCONTRIBUTORPHOTO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPCONTRIBUTORPHOTO_PLUGIN_PATH', plugin_dir_path(__FILE__));

class WPContributorPhoto {
    
    private $options;
    
    /**
     * Constructor
     */
    public function __construct() {
        register_activation_hook(__FILE__, array($this, 'plugin_activate'));
        register_deactivation_hook(__FILE__, array($this, 'plugin_deactivate'));
        
        add_action('plugins_loaded', array($this, 'load_plugin_options'));
        add_action('init', array($this, 'init'));
        
        if (is_admin()) {
            add_action('admin_init', array($this, 'admin_init'));
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
            add_action('admin_notices', array($this, 'admin_notices'));
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_plugin_action_links'));
        }
    }

    /**
     * Load plugin options
     */
    public function load_plugin_options() {
        $this->options = get_option('wpcontributorphoto_options', $this->get_default_options());
    }

    /**
     * Get default options
     */
    private function get_default_options() {
        return array(
            'default_user_id' => '',
            'default_per_page' => 12,
            'default_columns' => 4,
            'cache_time' => 3600,
            'open_in_new_tab' => true,
            'show_photo_titles' => true,
            'enable_lazy_loading' => true
        );
    }

    /**
     * Initialize plugin
     */
    public function init() {
        add_shortcode('wpcontrib_photos', array($this, 'render_photos_grid'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
    }

    /**
     * Add plugin action links
     */
    public function add_plugin_action_links($links) {
        $settings_link = sprintf(
            '<a href="%s" style="color: #2c3338; font-weight: 600;">%s</a>',
            esc_url(admin_url('admin.php?page=wpcontributorphoto')),
            esc_html__('Settings', 'wpcontributorphoto')
        );
        
        $docs_link = sprintf(
            '<a href="%s" target="_blank" style="color: #646970;">%s</a>',
            'https://github.com/askhellosatya/wpcontributorphoto',
            esc_html__('Docs', 'wpcontributorphoto')
        );
        
        array_unshift($links, $settings_link, $docs_link);
        return $links;
    }

    /**
     * Admin notices
     */
    public function admin_notices() {
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->id, ['plugins', 'dashboard'])) {
            return;
        }
        
        if (empty($this->options['default_user_id'])) {
            printf(
                '<div class="notice notice-info is-dismissible" style="border-left-color: #2c3338;">
                    <p><strong style="color: #2c3338;">%s</strong> %s <a href="%s" style="color: #2c3338; text-decoration: none; font-weight: 600;">%s</a></p>
                </div>',
                esc_html__('WP Contributor Photo Gallery:', 'wpcontributorphoto'),
                esc_html__('Ready to showcase your photo contributions?', 'wpcontributorphoto'),
                esc_url(admin_url('admin.php?page=wpcontributorphoto')),
                esc_html__('Complete Setup →', 'wpcontributorphoto')
            );
        }
    }

    /**
     * Admin initialization
     */
    public function admin_init() {
        register_setting(
            'wpcontributorphoto_settings',
            'wpcontributorphoto_options',
            array('sanitize_callback' => array($this, 'validate_options'))
        );

        add_settings_section(
            'wpcontributorphoto_main',
            __('Essential Configuration', 'wpcontributorphoto'),
            array($this, 'settings_section_callback'),
            'wpcontributorphoto'
        );

        add_settings_section(
            'wpcontributorphoto_advanced',
            __('Display & Performance', 'wpcontributorphoto'),
            array($this, 'advanced_settings_section_callback'),
            'wpcontributorphoto'
        );

        $this->add_settings_fields();
    }

    /**
     * Add settings fields
     */
    private function add_settings_fields() {
        $fields = array(
            'default_user_id' => array(
                'title' => __('Contributor User ID', 'wpcontributorphoto'),
                'callback' => 'user_id_field_callback',
                'section' => 'wpcontributorphoto_main'
            ),
            'default_per_page' => array(
                'title' => __('Photos Per Gallery', 'wpcontributorphoto'),
                'callback' => 'per_page_field_callback',
                'section' => 'wpcontributorphoto_main'
            ),
            'default_columns' => array(
                'title' => __('Grid Layout', 'wpcontributorphoto'),
                'callback' => 'columns_field_callback',
                'section' => 'wpcontributorphoto_main'
            ),
            'cache_time' => array(
                'title' => __('Cache Duration', 'wpcontributorphoto'),
                'callback' => 'cache_time_field_callback',
                'section' => 'wpcontributorphoto_advanced'
            ),
            'open_in_new_tab' => array(
                'title' => __('Link Behavior', 'wpcontributorphoto'),
                'callback' => 'new_tab_field_callback',
                'section' => 'wpcontributorphoto_advanced'
            ),
            'show_photo_titles' => array(
                'title' => __('Photo Descriptions', 'wpcontributorphoto'),
                'callback' => 'photo_titles_field_callback',
                'section' => 'wpcontributorphoto_advanced'
            ),
            'enable_lazy_loading' => array(
                'title' => __('Performance', 'wpcontributorphoto'),
                'callback' => 'lazy_loading_field_callback',
                'section' => 'wpcontributorphoto_advanced'
            )
        );

        foreach ($fields as $id => $field) {
            add_settings_field(
                $id,
                $field['title'],
                array($this, $field['callback']),
                'wpcontributorphoto',
                $field['section']
            );
        }
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('WP Contributor Photo Gallery Settings', 'wpcontributorphoto'),
            __('WP Contributor Photo Gallery', 'wpcontributorphoto'),
            'manage_options',
            'wpcontributorphoto',
            array($this, 'admin_page_callback'),
            'dashicons-camera',
            58
        );

        add_options_page(
            __('WP Contributor Photo Gallery Settings', 'wpcontributorphoto'),
            __('WP Contributor Photo Gallery', 'wpcontributorphoto'),
            'manage_options',
            'wpcontributorphoto-settings',
            array($this, 'admin_page_callback')
        );
    }

    /**
     * Settings section callbacks
     */
    public function settings_section_callback() {
        echo '<div class="wpcontrib-section-intro">
            <p>' . esc_html__('Configure your contribution display settings.', 'wpcontributorphoto') . '</p>
        </div>';
    }

    public function advanced_settings_section_callback() {
        echo '<div class="wpcontrib-section-intro">
            <p>' . esc_html__('Fine-tune performance and display preferences.', 'wpcontributorphoto') . '</p>
        </div>';
    }

    /**
     * Field callbacks
     */
    public function user_id_field_callback() {
        $user_id = isset($this->options['default_user_id']) ? $this->options['default_user_id'] : '';
        ?>
        <div class="wpcontrib-field-container">
            <div class="wpcontrib-input-group">
                <input type="text" 
                       id="default_user_id" 
                       name="wpcontributorphoto_options[default_user_id]" 
                       value="<?php echo esc_attr($user_id); ?>" 
                       class="wpcontrib-input-field" 
                       placeholder="e.g., 21053005" />
                <button type="button" id="user-id-help-btn" class="wpcontrib-help-btn" title="How to find your User ID">?</button>
            </div>
            <div id="user-id-status" class="wpcontrib-field-status"></div>
            <p class="wpcontrib-field-desc">
                <?php esc_html_e('Your unique contributor numeric ID (not your username).', 'wpcontributorphoto'); ?>
                <a href="#" id="user-id-guide-toggle" class="wpcontrib-help-link"><?php esc_html_e('View guide →', 'wpcontributorphoto'); ?></a>
            </p>
        </div>
        <?php
    }

    public function per_page_field_callback() {
        $per_page = isset($this->options['default_per_page']) ? $this->options['default_per_page'] : 12;
        ?>
        <div class="wpcontrib-field-container">
            <div class="wpcontrib-range-container">
                <input type="range" 
                       id="per_page_range" 
                       min="1" 
                       max="50" 
                       value="<?php echo esc_attr($per_page); ?>" 
                       class="wpcontrib-range-slider" />
                <div class="wpcontrib-range-value">
                    <span id="per_page_display"><?php echo esc_html($per_page); ?></span> <?php esc_html_e('photos', 'wpcontributorphoto'); ?>
                </div>
            </div>
            <input type="hidden" 
                   id="default_per_page" 
                   name="wpcontributorphoto_options[default_per_page]" 
                   value="<?php echo esc_attr($per_page); ?>" />
            <p class="wpcontrib-field-desc">
                <?php esc_html_e('Recommended: 12 for portfolios, 20-24 for showcases', 'wpcontributorphoto'); ?>
            </p>
        </div>
        <?php
    }

    public function columns_field_callback() {
        $columns = isset($this->options['default_columns']) ? $this->options['default_columns'] : 4;
        
        $column_options = array(
            1 => array('label' => __('1 Column', 'wpcontributorphoto'), 'desc' => __('Single column', 'wpcontributorphoto')),
            2 => array('label' => __('2 Columns', 'wpcontributorphoto'), 'desc' => __('Compact layout', 'wpcontributorphoto')),
            3 => array('label' => __('3 Columns', 'wpcontributorphoto'), 'desc' => __('Portfolio style', 'wpcontributorphoto')),
            4 => array('label' => __('4 Columns', 'wpcontributorphoto'), 'desc' => __('Balanced grid', 'wpcontributorphoto')),
            5 => array('label' => __('5 Columns', 'wpcontributorphoto'), 'desc' => __('Dense gallery', 'wpcontributorphoto')),
            6 => array('label' => __('6 Columns', 'wpcontributorphoto'), 'desc' => __('Full width', 'wpcontributorphoto'))
        );
        ?>
        <div class="wpcontrib-field-container">
            <div class="wpcontrib-columns-grid">
                <?php foreach ($column_options as $value => $option): ?>
                    <label class="wpcontrib-column-option <?php echo $columns == $value ? 'selected' : ''; ?>">
                        <input type="radio" 
                               name="wpcontributorphoto_options[default_columns]" 
                               value="<?php echo esc_attr($value); ?>" 
                               <?php checked($columns, $value); ?> />
                        <div class="wpcontrib-column-card">
                            <div class="wpcontrib-column-label"><?php echo esc_html($option['label']); ?></div>
                            <div class="wpcontrib-column-desc"><?php echo esc_html($option['desc']); ?></div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
            <p class="wpcontrib-field-desc">
                <?php esc_html_e('Grid automatically adapts for mobile devices.', 'wpcontributorphoto'); ?>
            </p>
        </div>
        <?php
    }

    public function cache_time_field_callback() {
        $cache_time = isset($this->options['cache_time']) ? $this->options['cache_time'] : 3600;
        $options = array(
            300 => __('5 minutes', 'wpcontributorphoto'),
            900 => __('15 minutes', 'wpcontributorphoto'),
            1800 => __('30 minutes', 'wpcontributorphoto'),
            3600 => __('1 hour (recommended)', 'wpcontributorphoto'),
            7200 => __('2 hours', 'wpcontributorphoto'),
            21600 => __('6 hours', 'wpcontributorphoto'),
            86400 => __('24 hours', 'wpcontributorphoto')
        );
        ?>
        <div class="wpcontrib-field-container">
            <select id="cache_time" 
                    name="wpcontributorphoto_options[cache_time]" 
                    class="wpcontrib-select-field">
                <?php foreach ($options as $value => $label): ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php selected($cache_time, $value); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="wpcontrib-field-desc">
                <?php esc_html_e('Longer cache improves performance but delays updates.', 'wpcontributorphoto'); ?>
            </p>
        </div>
        <?php
    }

    public function new_tab_field_callback() {
        $checked = isset($this->options['open_in_new_tab']) ? $this->options['open_in_new_tab'] : true;
        ?>
        <div class="wpcontrib-field-container">
            <label class="wpcontrib-toggle-container">
                <input type="checkbox" 
                       id="open_in_new_tab" 
                       name="wpcontributorphoto_options[open_in_new_tab]" 
                       value="1" 
                       <?php checked($checked, 1); ?> />
                <span class="wpcontrib-toggle-slider"></span>
                <span class="wpcontrib-toggle-label"><?php esc_html_e('Open photo links in new tab', 'wpcontributorphoto'); ?></span>
            </label>
            <p class="wpcontrib-field-desc">
                <?php esc_html_e('Keeps visitors on your site while exploring photos.', 'wpcontributorphoto'); ?>
            </p>
        </div>
        <?php
    }

    public function photo_titles_field_callback() {
        $checked = isset($this->options['show_photo_titles']) ? $this->options['show_photo_titles'] : true;
        ?>
        <div class="wpcontrib-field-container">
            <label class="wpcontrib-toggle-container">
                <input type="checkbox" 
                       id="show_photo_titles" 
                       name="wpcontributorphoto_options[show_photo_titles]" 
                       value="1" 
                       <?php checked($checked, 1); ?> />
                <span class="wpcontrib-toggle-slider"></span>
                <span class="wpcontrib-toggle-label"><?php esc_html_e('Show photo descriptions', 'wpcontributorphoto'); ?></span>
            </label>
            <p class="wpcontrib-field-desc">
                <?php esc_html_e('Display captions for better context.', 'wpcontributorphoto'); ?>
            </p>
        </div>
        <?php
    }

    public function lazy_loading_field_callback() {
        $checked = isset($this->options['enable_lazy_loading']) ? $this->options['enable_lazy_loading'] : true;
        ?>
        <div class="wpcontrib-field-container">
            <label class="wpcontrib-toggle-container">
                <input type="checkbox" 
                       id="enable_lazy_loading" 
                       name="wpcontributorphoto_options[enable_lazy_loading]" 
                       value="1" 
                       <?php checked($checked, 1); ?> />
                <span class="wpcontrib-toggle-slider"></span>
                <span class="wpcontrib-toggle-label"><?php esc_html_e('Enable lazy loading', 'wpcontributorphoto'); ?></span>
            </label>
            <p class="wpcontrib-field-desc">
                <?php esc_html_e('Improves page speed by loading images on demand.', 'wpcontributorphoto'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Validate options
     */
    public function validate_options($input) {
        if (!is_array($input)) {
            return $this->get_default_options();
        }

        $validated = array();
        
        $validated['default_user_id'] = sanitize_text_field($input['default_user_id'] ?? '');
        $validated['default_per_page'] = max(1, min(50, absint($input['default_per_page'] ?? 12)));
        $validated['default_columns'] = max(1, min(6, absint($input['default_columns'] ?? 4)));
        $validated['cache_time'] = absint($input['cache_time'] ?? 3600);
        $validated['open_in_new_tab'] = !empty($input['open_in_new_tab']) ? 1 : 0;
        $validated['show_photo_titles'] = !empty($input['show_photo_titles']) ? 1 : 0;
        $validated['enable_lazy_loading'] = !empty($input['enable_lazy_loading']) ? 1 : 0;

        add_settings_error(
            'wpcontributorphoto_options',
            'settings_saved',
            __('Settings saved successfully!', 'wpcontributorphoto'),
            'updated'
        );

        return $validated;
    }

    /**
     * Admin page callback
     */
    public function admin_page_callback() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Handle cache clearing with proper nonce verification
        if (isset($_POST['action']) && $_POST['action'] === 'clear_cache') {
            $nonce = isset($_POST['wpcontrib_cache_nonce']) ? wp_unslash($_POST['wpcontrib_cache_nonce']) : '';
            if (!wp_verify_nonce($nonce, 'wpcontrib_clear_cache')) {
                wp_die(esc_html__('Security check failed.', 'wpcontributorphoto'));
            }
            
            $this->clear_cache();
            echo '<div class="notice notice-success is-dismissible" style="margin: 5px 0 15px 0; padding: 12px; background: #d1e7dd; border: 1px solid #badbcc; border-left: 4px solid #0f5132; border-radius: 4px;">
    <p style="margin: 0; color: #0f5132; font-size: 14px;">
        <strong style="font-weight: 600;">' . esc_html__('✅ Cache Cleared!', 'wpcontributorphoto') . '</strong><br>
        <span style="color: ##0a0a0a; font-size: 13px; opacity: 0.9;">' . esc_html__('Your photo gallery will refresh with the latest data on the next page load.', 'wpcontributorphoto') . '</span>
            </div>';
        }               
        ?>
        <div class="wrap wpcontrib-admin-container">
            <div class="wpcontrib-header">
                <div class="wpcontrib-header-content">
                    <div class="wpcontrib-header-text">
                        <h1><?php esc_html_e('WP Contributor Photo Gallery', 'wpcontributorphoto'); ?></h1>
                        <p><?php esc_html_e('Showcase your photo contributions with elegance', 'wpcontributorphoto'); ?></p>
                    </div>
                    <div class="wpcontrib-header-actions">
                        <a href="https://wordpress.org/photos/" target="_blank" class="wpcontrib-btn-primary">
                            <?php esc_html_e('Contribute Photos', 'wpcontributorphoto'); ?>
                        </a>
                        <a href="https://github.com/askhellosatya/wpcontributorphoto" target="_blank" class="wpcontrib-btn-secondary">
                            <?php esc_html_e('Documentation', 'wpcontributorphoto'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="wpcontrib-main-content">
                <div class="wpcontrib-settings-panel">
                    <form action="options.php" method="post" class="wpcontrib-form">
                        <?php settings_fields('wpcontributorphoto_settings'); ?>
                        
                        <div class="wpcontrib-settings-sections">
                            <?php do_settings_sections('wpcontributorphoto'); ?>
                        </div>
                        
                        <div class="wpcontrib-form-footer">
                            <?php submit_button(__('Save Settings', 'wpcontributorphoto'), 'primary wpcontrib-btn-save', 'submit', false); ?>
                        </div>
                    </form>
                </div>

                <div class="wpcontrib-sidebar">
                    <div class="wpcontrib-card wpcontrib-usage-examples">
                        <div class="wpcontrib-card-header">
                            <h3><?php esc_html_e('Usage Examples', 'wpcontributorphoto'); ?></h3>
                            <p><?php esc_html_e('Copy shortcodes for different use cases', 'wpcontributorphoto'); ?></p>
                        </div>
                        <div class="wpcontrib-card-content">
                            <div class="wpcontrib-example-item">
                                <div class="wpcontrib-example-header">
                                    <div>
                                        <strong><?php esc_html_e('Portfolio Showcase', 'wpcontributorphoto'); ?></strong>
                                        <small><?php esc_html_e('Professional portfolios', 'wpcontributorphoto'); ?></small>
                                    </div>
                                </div>
                                <div class="wpcontrib-code-block">
                                    <code>[wpcontrib_photos per_page="20" columns="4"]</code>
                                    <button class="wpcontrib-copy-btn" data-code='[wpcontrib_photos per_page="20" columns="4"]'><?php esc_html_e('Copy', 'wpcontributorphoto'); ?></button>
                                </div>
                            </div>

                            <div class="wpcontrib-example-item">
                                <div class="wpcontrib-example-header">
                                    <div>
                                        <strong><?php esc_html_e('About Page Integration', 'wpcontributorphoto'); ?></strong>
                                        <small><?php esc_html_e('Personal branding', 'wpcontributorphoto'); ?></small>
                                    </div>
                                </div>
                                <div class="wpcontrib-code-block">
                                    <code>[wpcontrib_photos per_page="12" columns="3"]</code>
                                    <button class="wpcontrib-copy-btn" data-code='[wpcontrib_photos per_page="12" columns="3"]'><?php esc_html_e('Copy', 'wpcontributorphoto'); ?></button>
                                </div>
                            </div>

                            <div class="wpcontrib-example-item">
                                <div class="wpcontrib-example-header">
                                    <div>
                                        <strong><?php esc_html_e('Blog Enhancement', 'wpcontributorphoto'); ?></strong>
                                        <small><?php esc_html_e('Content creation', 'wpcontributorphoto'); ?></small>
                                    </div>
                                </div>
                                <div class="wpcontrib-code-block">
                                    <code>[wpcontrib_photos per_page="6" columns="2"]</code>
                                    <button class="wpcontrib-copy-btn" data-code='[wpcontrib_photos per_page="6" columns="2"]'><?php esc_html_e('Copy', 'wpcontributorphoto'); ?></button>
                                </div>
                            </div>

                            <div class="wpcontrib-example-item">
                                <div class="wpcontrib-example-header">
                                    <div>
                                        <strong><?php esc_html_e('Sidebar Widget', 'wpcontributorphoto'); ?></strong>
                                        <small><?php esc_html_e('Ongoing showcase', 'wpcontributorphoto'); ?></small>
                                    </div>
                                </div>
                                <div class="wpcontrib-code-block">
                                    <code>[wpcontrib_photos per_page="4" columns="1"]</code>
                                    <button class="wpcontrib-copy-btn" data-code='[wpcontrib_photos per_page="4" columns="1"]'><?php esc_html_e('Copy', 'wpcontributorphoto'); ?></button>
                                </div>
                            </div>

                            <div class="wpcontrib-example-item">
                                <div class="wpcontrib-example-header">
                                    <div>
                                        <strong><?php esc_html_e('Default Settings', 'wpcontributorphoto'); ?></strong>
                                        <small><?php esc_html_e('Uses configured settings', 'wpcontributorphoto'); ?></small>
                                    </div>
                                </div>
                                <div class="wpcontrib-code-block">
                                    <code>[wpcontrib_photos]</code>
                                    <button class="wpcontrib-copy-btn" data-code='[wpcontrib_photos]'><?php esc_html_e('Copy', 'wpcontributorphoto'); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($this->options['default_user_id'])): ?>
                    <div class="wpcontrib-card wpcontrib-preview-card">
                        <div class="wpcontrib-card-header">
                            <h3><?php esc_html_e('Gallery Preview', 'wpcontributorphoto'); ?></h3>
                            <p><?php esc_html_e('Sample with current settings', 'wpcontributorphoto'); ?></p>
                        </div>
                        <div class="wpcontrib-card-content">
                            <div class="wpcontrib-preview-wrapper">
                                <?php echo do_shortcode('[wpcontrib_photos per_page="2"]'); ?>
                            </div>
                            <div class="wpcontrib-preview-note">
                                <strong><?php esc_html_e('Tip:', 'wpcontributorphoto'); ?></strong> 
                                <?php esc_html_e('Use', 'wpcontributorphoto'); ?> <code>[wpcontrib_photos]</code> 
                                <?php esc_html_e('in posts, pages, or widgets', 'wpcontributorphoto'); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="wpcontrib-card">
                        <div class="wpcontrib-card-header">
                            <h3><?php esc_html_e('Tools & Resources', 'wpcontributorphoto'); ?></h3>
                        </div>
                        <div class="wpcontrib-card-content">
                            <div class="wpcontrib-tool-item">
                                <div class="wpcontrib-tool-content">
                                    <strong><?php esc_html_e('Clear Cache', 'wpcontributorphoto'); ?></strong>
                                    <p><?php esc_html_e('Refresh photo data', 'wpcontributorphoto'); ?></p>
                                </div>
                                <form method="post" action="" style="margin: 0;">
                                    <?php wp_nonce_field('wpcontrib_clear_cache', 'wpcontrib_cache_nonce'); ?>
                                    <input type="hidden" name="action" value="clear_cache">
                                    <button type="submit" class="wpcontrib-btn-tool"><?php esc_html_e('Clear', 'wpcontributorphoto'); ?></button>
                                </form>
                            </div>

                            <div class="wpcontrib-tool-links">
                                <a href="https://wordpress.org/photos/" target="_blank" class="wpcontrib-tool-link">
                                    <?php esc_html_e('WordPress.org Photos Directory', 'wpcontributorphoto'); ?>
                                </a>
                                <a href="https://github.com/askhellosatya/wpcontributorphoto" target="_blank" class="wpcontrib-tool-link">
                                    <?php esc_html_e('Plugin Documentation', 'wpcontributorphoto'); ?>
                                </a>
                                <a href="https://github.com/askhellosatya/wpcontributorphoto/" target="_blank" class="wpcontrib-tool-link">
                                    <?php esc_html_e('Get Support', 'wpcontributorphoto'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User ID Help Modal -->
            <div id="user-id-help-modal" class="wpcontrib-modal">
                <div class="wpcontrib-modal-backdrop"></div>
                <div class="wpcontrib-modal-container">
                    <div class="wpcontrib-modal-header">
                        <h3><?php esc_html_e('How to Find Your Contributor User ID', 'wpcontributorphoto'); ?></h3>
                        <button id="close-help-modal" class="wpcontrib-modal-close">&times;</button>
                    </div>
                    <div class="wpcontrib-modal-content">
                        <div class="wpcontrib-help-note">
                            <strong><?php esc_html_e('Important:', 'wpcontributorphoto'); ?></strong> 
                            <?php esc_html_e('Your User ID is a unique number (like', 'wpcontributorphoto'); ?> <code>21053005</code><?php esc_html_e('), not your username.', 'wpcontributorphoto'); ?>
                        </div>
                        
                        <div class="wpcontrib-help-steps">
                            <div class="wpcontrib-help-step">
                                <div class="wpcontrib-help-step-number">1</div>
                                <div class="wpcontrib-help-step-content">
                                    <strong><?php esc_html_e('Visit your author page:', 'wpcontributorphoto'); ?></strong><br>
                                    <code>https://wordpress.org/photos/author/YOUR-USERNAME/</code>
                                </div>
                            </div>
                            
                            <div class="wpcontrib-help-step">
                                <div class="wpcontrib-help-step-number">2</div>
                                <div class="wpcontrib-help-step-content">
                                    <strong><?php esc_html_e('View page source:', 'wpcontributorphoto'); ?></strong><br>
                                    <?php esc_html_e('Right-click → "View page source" or press', 'wpcontributorphoto'); ?> <kbd>Ctrl+U</kbd>
                                </div>
                            </div>
                            
                            <div class="wpcontrib-help-step">
                                <div class="wpcontrib-help-step-number">3</div>
                                <div class="wpcontrib-help-step-content">
                                    <strong><?php esc_html_e('Search for User ID:', 'wpcontributorphoto'); ?></strong><br>
                                    <?php esc_html_e('Press', 'wpcontributorphoto'); ?> <kbd>Ctrl+F</kbd><?php esc_html_e(', search for', 'wpcontributorphoto'); ?> <code>users/</code><br>
                                    <?php esc_html_e('Find:', 'wpcontributorphoto'); ?> <code>wp-json/wp/v2/users/12345678</code><br>
                                    <?php esc_html_e('The number at the end is your User ID', 'wpcontributorphoto'); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="wpcontrib-help-example">
                            <h4><?php esc_html_e('Example:', 'wpcontributorphoto'); ?></h4>
                            <div>
                                <div><strong><?php esc_html_e('Username:', 'wpcontributorphoto'); ?></strong> hellosatya</div>
                                <div><strong><?php esc_html_e('User ID:', 'wpcontributorphoto'); ?></strong> 21053005</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced CSS with Photography-Focused Design -->
        <style>
        .wpcontrib-admin-container {
            max-width: none;
            margin: 0 -20px;
            background: #fafafa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #2c3338;
        }

        .wpcontrib-header {
            background: linear-gradient(135deg, #2c3338 0%, #1e252b 100%);
            color: #ffffff;
            padding: 50px 30px;
            margin-bottom: 40px;
            position: relative;
        }

        .wpcontrib-header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .wpcontrib-header h1 {
            font-size: 28px;
            margin: 0 0 8px 0;
            font-weight: 300;
            letter-spacing: -0.5px;
            color: #ffffff;
        }

        .wpcontrib-header p {
            font-size: 16px;
            margin: 0;
            opacity: 0.85;
            font-weight: 300;
            color:rgb(219, 219, 219);
        }

        .wpcontrib-header-actions {
            display: flex;
            gap: 15px;
        }

        .wpcontrib-btn-primary, .wpcontrib-btn-secondary {
            padding: 12px 24px;
            border-radius: 3px;
            text-decoration: none;
            font-weight: 400;
            font-size: 14px;
            transition: all 0.2s ease;
            letter-spacing: 0.3px;
        }

        .wpcontrib-btn-primary {
            background: #ffffff;
            color: #2c3338;
            border: 1px solid transparent;
        }

        .wpcontrib-btn-secondary {
            background: transparent;
            color: #ffffff;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .wpcontrib-btn-primary:hover {
            background: #f6f7f7;
            color: #2c3338;
            transform: translateY(-1px);
        }

        .wpcontrib-btn-secondary:hover {
            background: rgba(255,255,255,0.1);
            border-color: rgba(255,255,255,0.5);
            color: #ffffff;
        }

        .wpcontrib-main-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
            gap: 30px;
            padding: 0 30px 60px;
            box-sizing: border-box;
        }

        .wpcontrib-settings-panel {
            background: #ffffff;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e5e5e5;
            min-width: 0;
            overflow: hidden;
        }

        .wpcontrib-form {
            padding: 0;
        }

        .wpcontrib-settings-sections .form-table {
            margin: 0;
            background: transparent;
            width: 100%;
            table-layout: fixed;
        }

        .wpcontrib-settings-sections h2 {
            background: #f9f9f9;
            margin: 0;
            padding: 20px 30px;
            font-size: 16px;
            font-weight: 500;
            color: #2c3338;
            border-bottom: 1px solid #e5e5e5;
            word-wrap: break-word;
        }

        .wpcontrib-section-intro {
            padding: 15px 30px 0;
            background: #f9f9f9;
            margin-bottom: 0;
        }

        .wpcontrib-section-intro p {
            margin: 0 0 15px 0;
            color: #646970;
            font-size: 14px;
        }

        .wpcontrib-settings-sections .form-table th {
            background: transparent;
            font-weight: 500;
            color: #2c3338;
            padding: 20px 25px;
            border-bottom: 1px solid #f0f0f1;
            width: 35%;
            font-size: 14px;
            word-wrap: break-word;
        }

        .wpcontrib-settings-sections .form-table td {
            padding: 20px 25px;
            border-bottom: 1px solid #f0f0f1;
            width: 65%;
            word-wrap: break-word;
        }

        .wpcontrib-field-container {
            max-width: 100%;
            min-width: 0;
        }

        .wpcontrib-input-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            max-width: 100%;
        }

        .wpcontrib-input-field, .wpcontrib-select-field {
            flex: 1;
            min-width: 0;
            padding: 10px 12px;
            border: 1px solid #dcdcde;
            border-radius: 3px;
            font-size: 14px;
            background: #ffffff;
            transition: border-color 0.2s ease;
            box-sizing: border-box;
        }

        .wpcontrib-input-field:focus, .wpcontrib-select-field:focus {
            border-color: #2c3338;
            outline: none;
            box-shadow: 0 0 0 1px #2c3338;
        }

        .wpcontrib-help-btn {
            background: #2c3338;
            color: white;
            border: none;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            flex-shrink: 0;
        }

        .wpcontrib-field-status {
            font-size: 13px;
            margin: 8px 0;
            min-height: 18px;
            word-wrap: break-word;
        }

        .wpcontrib-field-status.success { color: #00a32a; }
        .wpcontrib-field-status.error { color: #d63638; }

        .wpcontrib-field-desc {
            margin: 8px 0 0 0 !important;
            font-size: 13px;
            color: #646970;
            line-height: 1.5;
            word-wrap: break-word;
        }
			/* Enhanced Admin Notice Styling */
.wpcontrib-admin-container .notice {
    margin: 15px 0 20px 0 !important;
    padding: 15px 20px !important;
    border-radius: 6px !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
    border-left-width: 5px !important;
    font-family: inherit !important;
    position: relative;
    overflow: hidden;
}

.wpcontrib-admin-container .notice.notice-success {
    background: linear-gradient(135deg, #d1e7dd 0%, #c3e6cb 100%) !important;
    border-left-color: #28a745 !important;
    color: #155724 !important;
}

.wpcontrib-admin-container .notice p {
    margin: 0 !important;
    font-size: 14px !important;
    line-height: 1.5 !important;
}

.wpcontrib-admin-container .notice strong {
    font-weight: 600 !important;
    color: #080808 !important;
}

.wpcontrib-admin-container .notice::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, rgba(40, 167, 69, 0.3), transparent);
}

        .wpcontrib-help-link {
            color: #2c3338;
            text-decoration: none;
            font-weight: 400;
        }

        .wpcontrib-range-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            max-width: 100%;
        }

        .wpcontrib-range-slider {
            flex: 1;
            min-width: 0;
            height: 3px;
            background: #dcdcde;
            border-radius: 3px;
            outline: none;
            -webkit-appearance: none;
        }

        .wpcontrib-range-slider::-webkit-slider-thumb {
            appearance: none;
            width: 18px;
            height: 18px;
            background: #2c3338;
            border-radius: 50%;
            cursor: pointer;
        }

        .wpcontrib-range-value {
            background: #2c3338;
            color: white;
            padding: 6px 12px;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 500;
            min-width: 70px;
            text-align: center;
            flex-shrink: 0;
        }

        .wpcontrib-columns-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }

        .wpcontrib-column-option {
            cursor: pointer;
        }

        .wpcontrib-column-option input {
            display: none;
        }

        .wpcontrib-column-card {
            padding: 12px;
            border: 1px solid #dcdcde;
            border-radius: 3px;
            text-align: center;
            transition: all 0.2s ease;
            background: white;
            min-width: 0;
        }

        .wpcontrib-column-option.selected .wpcontrib-column-card,
        .wpcontrib-column-option input:checked + .wpcontrib-column-card {
            border-color: #2c3338;
            background: #f9f9f9;
        }

        .wpcontrib-column-label {
            font-weight: 500;
            color: #2c3338;
            margin-bottom: 4px;
            font-size: 13px;
        }

        .wpcontrib-column-desc {
            font-size: 11px;
            color: #646970;
        }

        .wpcontrib-toggle-container {
            display: flex;
            align-items: center;
            gap: 15px;
            cursor: pointer;
            padding: 0;
            max-width: 100%;
        }

        .wpcontrib-toggle-container input {
            display: none;
        }

        .wpcontrib-toggle-slider {
            position: relative;
            width: 44px;
            height: 24px;
            background: #dcdcde;
            border-radius: 24px;
            transition: background 0.2s;
            flex-shrink: 0;
        }

        .wpcontrib-toggle-slider:before {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: white;
            top: 2px;
            left: 2px;
            transition: transform 0.2s;
        }

        .wpcontrib-toggle-container input:checked + .wpcontrib-toggle-slider {
            background: #2c3338;
        }

        .wpcontrib-toggle-container input:checked + .wpcontrib-toggle-slider:before {
            transform: translateX(20px);
        }

        .wpcontrib-toggle-label {
            font-size: 14px;
            color: #2c3338;
            font-weight: 500;
            word-wrap: break-word;
            min-width: 0;
        }

        .wpcontrib-form-footer {
            padding: 25px;
            background: #f9f9f9;
            text-align: center;
            border-top: 1px solid #e5e5e5;
        }

        .wpcontrib-btn-save {
            background: #2c3338 !important;
            border: none !important;
            padding: 12px 30px !important;
            font-size: 14px !important;
            font-weight: 400 !important;
            border-radius: 3px !important;
            letter-spacing: 0.3px !important;
            transition: all 0.2s ease !important;
        }

        .wpcontrib-btn-save:hover {
            background: #1e252b !important;
            transform: translateY(-1px) !important;
        }

        .wpcontrib-sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
            min-width: 0;
        }

        .wpcontrib-card {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e5e5e5;
            overflow: hidden;
            min-width: 0;
        }

        .wpcontrib-card-header {
            padding: 18px 20px;
            background: #f9f9f9;
            border-bottom: 1px solid #e5e5e5;
        }

        .wpcontrib-card-header h3 {
            margin: 0 0 5px 0;
            font-size: 15px;
            font-weight: 500;
            color: #2c3338;
            word-wrap: break-word;
        }

        .wpcontrib-card-header p {
            margin: 0;
            font-size: 12px;
            color: #646970;
        }

        .wpcontrib-card-content {
            padding: 20px;
        }

        .wpcontrib-example-item {
            margin-bottom: 16px;
            padding: 12px;
            background: #f9f9f9;
            border-radius: 3px;
            border-left: 3px solid #2c3338;
        }

        .wpcontrib-example-item:last-child {
            margin-bottom: 0;
        }

        .wpcontrib-example-header {
            margin-bottom: 8px;
        }

        .wpcontrib-example-header strong {
            display: block;
            color: #2c3338;
            font-size: 13px;
            margin-bottom: 2px;
        }

        .wpcontrib-example-header small {
            color: #646970;
            font-size: 11px;
        }

        .wpcontrib-code-block {
            display: flex;
            align-items: center;
            gap: 8px;
            background: white;
            padding: 8px 10px;
            border-radius: 3px;
            border: 1px solid #e5e5e5;
            min-width: 0;
        }

        .wpcontrib-code-block code {
            flex: 1;
            min-width: 0;
            background: none;
            font-family: Consolas, Monaco, monospace;
            font-size: 11px;
            color: #2c3338;
            word-break: break-all;
        }

        .wpcontrib-copy-btn {
            background: #2c3338;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 10px;
            font-weight: 500;
            letter-spacing: 0.3px;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .wpcontrib-copy-btn:hover {
            background: #1e252b;
        }

        .wpcontrib-preview-wrapper {
            background: #f9f9f9;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #e5e5e5;
            margin-bottom: 12px;
            max-width: 250px;
            margin-left: auto;
            margin-right: auto;
        }

        .wpcontrib-preview-wrapper .wpcontrib-photos-grid {
            padding: 0;
            gap: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .wpcontrib-preview-wrapper .wpcontrib-photo-card {
            max-width: 100px;
            min-width: 100px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            border-radius: 8px;
            overflow: hidden;
        }

        .wpcontrib-preview-wrapper .wpcontrib-photo-image {
            aspect-ratio: 1;
            overflow: hidden;
        }

        .wpcontrib-preview-wrapper .wpcontrib-photo-image img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .wpcontrib-preview-wrapper .wpcontrib-photo-card:hover .wpcontrib-photo-image img {
            transform: scale(1.05);
        }

        .wpcontrib-preview-wrapper .wpcontrib-photo-content {
            padding: 6px;
            text-align: center;
        }

        .wpcontrib-preview-wrapper .wpcontrib-photo-content p {
            font-size: 10px;
            line-height: 1.3;
            margin: 0;
            color: #2c3338;
            font-weight: 400;
        }

        .wpcontrib-preview-note {
            font-size: 12px;
            color: #646970;
            text-align: center;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 6px;
            border: 1px solid #e5e5e5;
        }

        .wpcontrib-preview-note code {
            background: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            border: 1px solid #ddd;
            font-family: 'Monaco', 'Consolas', monospace;
            color: #2c3338;
        }

        .wpcontrib-tool-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .wpcontrib-tool-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        }

        .wpcontrib-tool-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .wpcontrib-tool-content::before {
            content: "🔄";
            font-size: 18px;
            display: inline-block;
        }

        .wpcontrib-tool-content strong {
            display: block;
            color: #2c3338;
            font-size: 14px;
            margin-bottom: 2px;
            font-weight: 600;
        }

        .wpcontrib-tool-content p {
            margin: 0;
            color: #646970;
            font-size: 12px;
        }

        .wpcontrib-btn-tool {
            background: linear-gradient(135deg, #2c3338 0%, #1e252b 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(44, 51, 56, 0.2);
        }

        .wpcontrib-btn-tool:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(44, 51, 56, 0.3);
            background: linear-gradient(135deg, #1e252b 0%, #0f1419 100%);
        }

        .wpcontrib-tool-links {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .wpcontrib-tool-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 8px;
            text-decoration: none;
            color: #2c3338;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }

        .wpcontrib-tool-link::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(44, 51, 56, 0.1), transparent);
            transition: left 0.5s;
        }

        .wpcontrib-tool-link:hover::before {
            left: 100%;
        }

        .wpcontrib-tool-link:hover {
            background: linear-gradient(135deg, #2c3338 0%, #1e252b 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(44, 51, 56, 0.2);
            border-color: #2c3338;
        }

        .wpcontrib-tool-link:nth-child(1)::after {
            content: "📸";
            font-size: 16px;
            margin-left: auto;
        }

        .wpcontrib-tool-link:nth-child(2)::after {
            content: "📖";
            font-size: 16px;
            margin-left: auto;
        }

        .wpcontrib-tool-link:nth-child(3)::after {
            content: "💬";
            font-size: 16px;
            margin-left: auto;
        }

        .wpcontrib-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10000;
        }

        .wpcontrib-modal-backdrop {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }

        .wpcontrib-modal-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 3px;
            max-width: 500px;
            width: 90%;
            max-height: 80%;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .wpcontrib-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            background: #f9f9f9;
            border-bottom: 1px solid #e5e5e5;
        }

        .wpcontrib-modal-header h3 {
            margin: 0;
            color: #2c3338;
            font-size: 16px;
            font-weight: 500;
        }

        .wpcontrib-modal-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #646970;
            width: 28px;
            height: 28px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .wpcontrib-modal-close:hover {
            background: #e5e5e5;
            color: #2c3338;
        }

        .wpcontrib-modal-content {
            padding: 25px;
        }

        .wpcontrib-help-note {
            background: #fff3cd;
            padding: 15px;
            border-radius: 3px;
            margin-bottom: 20px;
            border-left: 3px solid #f0ad4e;
            font-size: 14px;
        }

        .wpcontrib-help-steps {
            margin-bottom: 20px;
        }

        .wpcontrib-help-step {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .wpcontrib-help-step-number {
            width: 28px;
            height: 28px;
            background: #2c3338;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            flex-shrink: 0;
            font-size: 14px;
        }

        .wpcontrib-help-step-content {
            flex: 1;
            font-size: 14px;
        }

        .wpcontrib-help-step-content strong {
            display: block;
            margin-bottom: 4px;
            color: #2c3338;
        }

        .wpcontrib-help-step-content code {
            background: #f9f9f9;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 13px;
            border: 1px solid #e5e5e5;
        }

        .wpcontrib-help-step-content kbd {
            background: #2c3338;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 12px;
        }

        .wpcontrib-help-example {
            background: #d4edda;
            padding: 15px;
            border-radius: 3px;
            border-left: 3px solid #28a745;
        }

        .wpcontrib-help-example h4 {
            margin: 0 0 10px 0;
            color: #155724;
            font-size: 14px;
        }

        .wpcontrib-help-example div {
            font-size: 13px;
            margin-bottom: 5px;
        }

        .wpcontrib-help-example strong {
            color: #155724;
        }

        .wpcontrib-help-example code {
            background: rgba(21, 87, 36, 0.1);
        }

        @media (max-width: 1200px) {
            .wpcontrib-main-content {
                max-width: 100%;
                padding: 0 20px 60px;
            }
        }

        @media (max-width: 1024px) {
            .wpcontrib-main-content {
                grid-template-columns: 1fr;
                gap: 25px;
                padding: 0 20px 40px;
            }
            
            .wpcontrib-header-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .wpcontrib-columns-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .wpcontrib-preview-wrapper {
                max-width: 100%;
            }
            
            .wpcontrib-settings-sections .form-table th,
            .wpcontrib-settings-sections .form-table td {
                display: block;
                width: 100%;
                padding: 15px 25px;
            }
            
            .wpcontrib-settings-sections .form-table th {
                border-bottom: none;
                padding-bottom: 10px;
            }
            
            .wpcontrib-settings-sections .form-table td {
                padding-top: 0;
            }
        }

        @media (max-width: 640px) {
            .wpcontrib-main-content {
                padding: 0 15px 30px;
            }
            
            .wpcontrib-header {
                padding: 25px 15px;
            }
            
            .wpcontrib-columns-grid {
                grid-template-columns: 1fr;
            }
            
            .wpcontrib-card-content {
                padding: 15px;
            }
            
            .wpcontrib-preview-wrapper .wpcontrib-photos-grid {
                flex-direction: column;
                gap: 6px;
            }
            
            .wpcontrib-preview-wrapper .wpcontrib-photo-card {
                max-width: 80px;
                min-width: 80px;
            }
            
            .wpcontrib-preview-wrapper .wpcontrib-photo-image img {
                width: 80px;
                height: 80px;
            }
            
            .wpcontrib-code-block {
                flex-direction: column;
                align-items: stretch;
                gap: 6px;
            }
            
            .wpcontrib-copy-btn {
                align-self: flex-end;
                width: auto;
            }
        }
        </style>
        <?php
    }

    /**
     * Admin enqueue scripts
     */
    public function admin_enqueue_scripts($hook) {
        if ($hook !== 'toplevel_page_wpcontributorphoto') {
            return;
        }
        wp_enqueue_script('jquery');
        add_action('admin_footer', array($this, 'admin_footer_script'));
    }

    /**
     * Admin footer script
     */
    public function admin_footer_script() {
        ?>
        <script>
        jQuery(document).ready(function($) {
            // User ID validation
            $('#default_user_id').on('input', function() {
                const value = $(this).val().trim();
                const statusDiv = $('#user-id-status');
                
                if (!value) {
                    statusDiv.html('').removeClass('success error');
                    return;
                }
                
                if (!/^\d+$/.test(value)) {
                    statusDiv.html('User ID should be numbers only').removeClass('success').addClass('error');
                } else if (value.length < 6) {
                    statusDiv.html('User ID seems too short').removeClass('success').addClass('error');
                } else {
                    statusDiv.html('Valid User ID format').removeClass('error').addClass('success');
                }
            });
            
            // Range slider sync
            $('#per_page_range').on('input', function() {
                const value = $(this).val();
                $('#default_per_page').val(value);
                $('#per_page_display').text(value);
            });
            
            // Column selection
            $('.wpcontrib-column-option input').on('change', function() {
                $('.wpcontrib-column-option').removeClass('selected');
                $(this).closest('.wpcontrib-column-option').addClass('selected');
            });
            
            // Copy functionality
            $('.wpcontrib-copy-btn').on('click', function() {
                const code = $(this).data('code');
                const button = $(this);
                const originalText = button.text();
                
                const textarea = $('<textarea>').val(code).css({
                    position: 'fixed',
                    opacity: 0,
                    top: 0,
                    left: 0
                }).appendTo('body');
                
                textarea[0].select();
                
                try {
                    document.execCommand('copy');
                    button.text('Copied!').css('background', '#28a745');
                    setTimeout(function() {
                        button.text(originalText).css('background', '#2c3338');
                    }, 1500);
                } catch(err) {
                    button.text('Failed').css('background', '#dc3545');
                    setTimeout(function() {
                        button.text(originalText).css('background', '#2c3338');
                    }, 1500);
                }
                
                textarea.remove();
            });
            
            // Modal functionality
            $('#user-id-help-btn, #user-id-guide-toggle').on('click', function(e) {
                e.preventDefault();
                $('#user-id-help-modal').fadeIn(200);
            });
            
            $('#close-help-modal, .wpcontrib-modal-backdrop').on('click', function() {
                $('#user-id-help-modal').fadeOut(200);
            });
            
            $('.wpcontrib-modal-container').on('click', function(e) {
                e.stopPropagation();
            });
            
            // Auto-focus if User ID is empty
            if (!$('#default_user_id').val().trim()) {
                setTimeout(function() {
                    $('#default_user_id').focus();
                }, 300);
            }
        });
        </script>
        <?php
    }

    /**
     * Clear cache using transient functions
     */
    private function clear_cache() {
        global $wpdb;
        
        // Get transient names using prepared statement
        $transient_names = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_wpcontrib_photos_%'
            )
        );
        
        // Delete each transient using WordPress functions
        foreach ($transient_names as $transient_name) {
            $name = str_replace('_transient_', '', $transient_name);
            delete_transient($name);
        }
    }    
    /**
     * Enqueue styles
     */
    public function enqueue_styles() {
        $css = '
        .wpcontrib-photos-grid {
            display: grid;
            gap: 1.5rem;
            padding: 1rem 0;
        }
        
        .wpcontrib-photos-grid.columns-1 { grid-template-columns: 1fr; }
        .wpcontrib-photos-grid.columns-2 { grid-template-columns: repeat(2, 1fr); }
        .wpcontrib-photos-grid.columns-3 { grid-template-columns: repeat(3, 1fr); }
        .wpcontrib-photos-grid.columns-4 { grid-template-columns: repeat(4, 1fr); }
        .wpcontrib-photos-grid.columns-5 { grid-template-columns: repeat(5, 1fr); }
        .wpcontrib-photos-grid.columns-6 { grid-template-columns: repeat(6, 1fr); }
        
        @media (max-width: 768px) {
            .wpcontrib-photos-grid {
                grid-template-columns: 1fr !important;
            }
        }
        
        .wpcontrib-photo-card {
            background: #fff;
            border-radius: 3px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
            border: 1px solid #e5e5e5;
        }
        
        .wpcontrib-photo-card:hover {
            transform: translateY(-2px);
        }
        
        .wpcontrib-photo-card a {
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .wpcontrib-photo-image {
            aspect-ratio: 1;
            overflow: hidden;
        }
        
        .wpcontrib-photo-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .wpcontrib-photo-content {
            padding: 1rem;
        }
        
        .wpcontrib-photo-content p {
            margin: 0;
            font-size: 14px;
            color: #2c3338;
        }
        
        .wpcontrib-photos-error {
            padding: 1rem;
            background: #fff3cd;
            border: 1px solid #f0ad4e;
            border-radius: 3px;
            color: #856404;
        }';

        wp_add_inline_style('wp-block-library', $css);
    }

    /**
     * Render shortcode
     */
    public function render_photos_grid($atts) {
        $atts = shortcode_atts(array(
            'user_id' => $this->options['default_user_id'] ?? '',
            'per_page' => $this->options['default_per_page'] ?? 12,
            'columns' => $this->options['default_columns'] ?? 4,
        ), $atts, 'wpcontrib_photos');

        if (empty($atts['user_id'])) {
            return '<div class="wpcontrib-photos-error">' . esc_html__('Please set your User ID in plugin settings.', 'wpcontributorphoto') . '</div>';
        }

        $user_id = sanitize_text_field($atts['user_id']);
        $per_page = max(1, min(50, absint($atts['per_page'])));
        $columns = max(1, min(6, absint($atts['columns'])));

        $photos = $this->get_photos($user_id, $per_page);

        if (is_wp_error($photos)) {
            return '<div class="wpcontrib-photos-error">' . esc_html($photos->get_error_message()) . '</div>';
        }

        if (empty($photos)) {
            return '<div class="wpcontrib-photos-error">' . esc_html__('No photos found.', 'wpcontributorphoto') . '</div>';
        }

        return $this->render_grid($photos, $columns);
    }

    /**
     * Get photos from API
     */
    private function get_photos($user_id, $per_page) {
        $cache_key = 'wpcontrib_photos_' . md5($user_id . '_' . $per_page);
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

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $photos = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', __('Invalid response from API', 'wpcontributorphoto'));
        }

        set_transient($cache_key, $photos, $this->options['cache_time'] ?? 3600);
        return $photos;
    }

    /**
     * Render grid
     */
    private function render_grid($photos, $columns) {
        $output = sprintf('<div class="wpcontrib-photos-grid columns-%d">', esc_attr($columns));

        foreach ($photos as $photo) {
            $image_url = '';
            $title = '';
            $link = $photo['link'] ?? '';

            if (isset($photo['_embedded']['wp:featuredmedia'][0]['media_details']['sizes']['large']['source_url'])) {
                $image_url = $photo['_embedded']['wp:featuredmedia'][0]['media_details']['sizes']['large']['source_url'];
            }

            if (isset($photo['content']['rendered'])) {
                $title = wp_strip_all_tags($photo['content']['rendered']);
                $title = mb_substr($title, 0, 30) . (mb_strlen($title) > 30 ? '...' : ''); // 30 character limit
            }

            if ($image_url && $link) {
                $target = ($this->options['open_in_new_tab'] ?? true) ? ' target="_blank" rel="noopener"' : '';
                $show_title = ($this->options['show_photo_titles'] ?? true) && !empty($title);
                
                $output .= sprintf('
                <div class="wpcontrib-photo-card">
                    <a href="%s"%s>
                        <div class="wpcontrib-photo-image">
                            <img src="%s" alt="%s" loading="lazy">
                        </div>
                        %s
                    </a>
                </div>',
                    esc_url($link),
                    $target,
                    esc_url($image_url),
                    esc_attr($title),
                    $show_title ? '<div class="wpcontrib-photo-content"><p>' . esc_html($title) . '</p></div>' : ''
                );
            }
        }

        $output .= '</div>';
        return $output;
    }

    /**
     * Plugin activation
     */
    public function plugin_activate() {
        add_option('wpcontributorphoto_options', $this->get_default_options());
        
        // Create languages directory if it doesn't exist
        $languages_dir = WPCONTRIBUTORPHOTO_PLUGIN_PATH . 'languages';
        if (!file_exists($languages_dir)) {
            wp_mkdir_p($languages_dir);
        }
    }

    /**
     * Plugin deactivation
     */
    public function plugin_deactivate() {
        $this->clear_cache();
    }
}

// Initialize the plugin
new WPContributorPhoto();
