<?php
/**
 * Plugin Name: WordPress.org Contributor Photo Gallery
 * Plugin URI: https://github.com/askhellosatya/wpphotogallery
 * Description: Showcase your photo contributions to WordPress.org/photos directory in beautiful responsive grids on your website.
 * Version: 2.0.0
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * Author: Satyam Vishwakarma (Satya)
 * Author URI: https://satyamvishwakarma.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-photo-contributor-gallery
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPORG_PHOTOS_GRID_VERSION', '2.0.0');
define('WPORG_PHOTOS_GRID_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPORG_PHOTOS_GRID_PLUGIN_PATH', plugin_dir_path(__FILE__));

class WPOrgPhotosGrid {
    
    private $options;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize hooks in proper order
        register_activation_hook(__FILE__, array($this, 'plugin_activate'));
        register_deactivation_hook(__FILE__, array($this, 'plugin_deactivate'));
        
        // Load plugin options early
        add_action('plugins_loaded', array($this, 'load_plugin_options'));
        
        // Initialize plugin
        add_action('init', array($this, 'init'));
        
        // Admin hooks
        if (is_admin()) {
            add_action('admin_init', array($this, 'admin_init'));
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
            
            // Add settings link to plugins page
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_plugin_action_links'));
            
            // Add admin notices for better user guidance
            add_action('admin_notices', array($this, 'admin_notices'));
        }
    }

    /**
     * Load plugin options
     */
    public function load_plugin_options() {
        $this->options = get_option('wporg_photos_grid_options', $this->get_default_options());
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
        // Load text domain
        load_plugin_textdomain('wp-photo-contributor-gallery', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Register shortcode
        add_shortcode('wporg_photos', array($this, 'render_photos_grid'));
        
        // Enqueue frontend styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
    }

    /**
     * Add plugin action links (Settings link on plugins page)
     */
    public function add_plugin_action_links($links) {
        $settings_link = sprintf(
            '<a href="%s" style="color: #2c3338; font-weight: 600;">%s</a>',
            admin_url('admin.php?page=wporg-photos-grid'),
            __('Settings', 'wp-photo-contributor-gallery')
        );
        
        $docs_link = sprintf(
            '<a href="%s" target="_blank" style="color: #646970;">%s</a>',
            'https://github.com/askhellosatya/wpphotogallery',
            __('Docs', 'wp-photo-contributor-gallery')
        );
        
        array_unshift($links, $settings_link, $docs_link);
        return $links;
    }

    /**
     * Admin notices for better user guidance
     */
    public function admin_notices() {
        // Only show on relevant pages
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->id, ['plugins', 'dashboard'])) {
            return;
        }
        
        // Show setup notice if User ID is not configured
        if (empty($this->options['default_user_id'])) {
            printf(
                '<div class="notice notice-info is-dismissible" style="border-left-color: #2c3338;">
                    <p><strong style="color: #2c3338;">W.Org Photo Gallery:</strong> %s <a href="%s" style="color: #2c3338; text-decoration: none; font-weight: 600;">%s</a></p>
                </div>',
                __('Ready to showcase your WordPress.org photo contributions?', 'wp-photo-contributor-gallery'),
                admin_url('admin.php?page=wporg-photos-grid'),
                __('Complete Setup →', 'wp-photo-contributor-gallery')
            );
        }
    }

    /**
     * Admin initialization
     */
    public function admin_init() {
        // Register settings
        register_setting(
            'wporg_photos_grid_settings',
            'wporg_photos_grid_options',
            array(
                'sanitize_callback' => array($this, 'validate_options')
            )
        );

        // Add settings sections
        add_settings_section(
            'wporg_photos_grid_main',
            __('Essential Configuration', 'wp-photo-contributor-gallery'),
            array($this, 'settings_section_callback'),
            'wporg-photos-grid'
        );

        add_settings_section(
            'wporg_photos_grid_advanced',
            __('Display & Performance', 'wp-photo-contributor-gallery'),
            array($this, 'advanced_settings_section_callback'),
            'wporg-photos-grid'
        );

        // Add settings fields
        $this->add_settings_fields();
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        if ($hook !== 'toplevel_page_wporg-photos-grid') {
            return;
        }
        wp_enqueue_script('jquery');
        
        // Add inline script
        add_action('admin_footer', array($this, 'admin_footer_script'));
    }

    /**
     * Add settings fields
     */
    private function add_settings_fields() {
        $fields = array(
            'default_user_id' => array(
                'title' => __('WordPress.org User ID', 'wp-photo-contributor-gallery'),
                'callback' => 'user_id_field_callback',
                'section' => 'wporg_photos_grid_main'
            ),
            'default_per_page' => array(
                'title' => __('Photos Per Gallery', 'wp-photo-contributor-gallery'),
                'callback' => 'per_page_field_callback',
                'section' => 'wporg_photos_grid_main'
            ),
            'default_columns' => array(
                'title' => __('Grid Layout', 'wp-photo-contributor-gallery'),
                'callback' => 'columns_field_callback',
                'section' => 'wporg_photos_grid_main'
            ),
            'cache_time' => array(
                'title' => __('Cache Duration', 'wp-photo-contributor-gallery'),
                'callback' => 'cache_time_field_callback',
                'section' => 'wporg_photos_grid_advanced'
            ),
            'open_in_new_tab' => array(
                'title' => __('Link Behavior', 'wp-photo-contributor-gallery'),
                'callback' => 'new_tab_field_callback',
                'section' => 'wporg_photos_grid_advanced'
            ),
            'show_photo_titles' => array(
                'title' => __('Photo Descriptions', 'wp-photo-contributor-gallery'),
                'callback' => 'photo_titles_field_callback',
                'section' => 'wporg_photos_grid_advanced'
            ),
            'enable_lazy_loading' => array(
                'title' => __('Performance', 'wp-photo-contributor-gallery'),
                'callback' => 'lazy_loading_field_callback',
                'section' => 'wporg_photos_grid_advanced'
            )
        );

        foreach ($fields as $id => $field) {
            add_settings_field(
                $id,
                $field['title'],
                array($this, $field['callback']),
                'wporg-photos-grid',
                $field['section']
            );
        }
    }

    /**
     * Add admin menu with updated naming
     */
    public function add_admin_menu() {
		$icon = 'dashicons-camera';
        add_menu_page(
            __('W.Org Photo Gallery Settings', 'wp-photo-contributor-gallery'),
            __('WP Photo Gallery', 'wp-photo-contributor-gallery'),
            'manage_options',
            'wporg-photos-grid',
            array($this, 'admin_page_callback'),
            $icon,
            58
        );

        // Also add under Settings for traditional access - Updated name
        add_options_page(
            __('W.Org Photo Gallery Settings', 'wp-photo-contributor-gallery'),
            __('W.Org Photo Gallery', 'wp-photo-contributor-gallery'),
            'manage_options',
            'wporg-photos-grid-settings',
            array($this, 'admin_page_callback')
        );
    }

    /**
     * Settings section callbacks
     */
    public function settings_section_callback() {
        echo '<div class="wporg-section-intro">
            <p>' . esc_html__('Configure the core settings to display your WordPress.org photo contributions.', 'wp-photo-contributor-gallery') . '</p>
        </div>';
    }

    public function advanced_settings_section_callback() {
        echo '<div class="wporg-section-intro">
            <p>' . esc_html__('Fine-tune performance, behavior, and display preferences for optimal results.', 'wp-photo-contributor-gallery') . '</p>
        </div>';
    }

    /**
     * Enhanced field callbacks with refined styling
     */
    public function user_id_field_callback() {
        $user_id = isset($this->options['default_user_id']) ? $this->options['default_user_id'] : '';
        ?>
        <div class="wporg-field-container">
            <div class="wporg-input-group">
                <input type="text" 
                       id="default_user_id" 
                       name="wporg_photos_grid_options[default_user_id]" 
                       value="<?php echo esc_attr($user_id); ?>" 
                       class="wporg-input-field" 
                       placeholder="e.g., 21053005" />
                <button type="button" id="user-id-help-btn" class="wporg-help-btn" title="How to find your User ID">
                    ?
                </button>
            </div>
            <div id="user-id-status" class="wporg-field-status"></div>
            <p class="wporg-field-desc">
                <?php esc_html_e('Your unique WordPress.org numeric ID (not your username).', 'wp-photo-contributor-gallery'); ?>
                <a href="#" id="user-id-guide-toggle" class="wporg-help-link">View guide →</a>
            </p>
        </div>
        <?php
    }

    public function per_page_field_callback() {
        $per_page = isset($this->options['default_per_page']) ? $this->options['default_per_page'] : 12;
        ?>
        <div class="wporg-field-container">
            <div class="wporg-range-container">
                <input type="range" 
                       id="per_page_range" 
                       min="1" 
                       max="50" 
                       value="<?php echo esc_attr($per_page); ?>" 
                       class="wporg-range-slider" />
                <div class="wporg-range-value">
                    <span id="per_page_display"><?php echo esc_html($per_page); ?></span> photos
                </div>
            </div>
            <input type="hidden" 
                   id="default_per_page" 
                   name="wporg_photos_grid_options[default_per_page]" 
                   value="<?php echo esc_attr($per_page); ?>" />
            <p class="wporg-field-desc">
                <?php esc_html_e('Recommended: 12 for portfolios, 20-24 for showcases', 'wp-photo-contributor-gallery'); ?>
            </p>
        </div>
        <?php
    }

    public function columns_field_callback() {
        $columns = isset($this->options['default_columns']) ? $this->options['default_columns'] : 4;
        
        $column_options = array(
            1 => array('label' => '1 Column', 'desc' => 'Single column'),
            2 => array('label' => '2 Columns', 'desc' => 'Compact layout'),
            3 => array('label' => '3 Columns', 'desc' => 'Portfolio style'),
            4 => array('label' => '4 Columns', 'desc' => 'Balanced grid'),
            5 => array('label' => '5 Columns', 'desc' => 'Dense gallery'),
            6 => array('label' => '6 Columns', 'desc' => 'Full width')
        );
        ?>
        <div class="wporg-field-container">
            <div class="wporg-columns-grid">
                <?php foreach ($column_options as $value => $option): ?>
                    <label class="wporg-column-option <?php echo $columns == $value ? 'selected' : ''; ?>">
                        <input type="radio" 
                               name="wporg_photos_grid_options[default_columns]" 
                               value="<?php echo $value; ?>" 
                               <?php checked($columns, $value); ?> />
                        <div class="wporg-column-card">
                            <div class="wporg-column-label"><?php echo esc_html($option['label']); ?></div>
                            <div class="wporg-column-desc"><?php echo esc_html($option['desc']); ?></div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
            <p class="wporg-field-desc">
                <?php esc_html_e('Grid automatically adapts for mobile devices.', 'wp-photo-contributor-gallery'); ?>
            </p>
        </div>
        <?php
    }

    public function cache_time_field_callback() {
        $cache_time = isset($this->options['cache_time']) ? $this->options['cache_time'] : 3600;
        $options = array(
            300 => __('5 minutes', 'wp-photo-contributor-gallery'),
            900 => __('15 minutes', 'wp-photo-contributor-gallery'),
            1800 => __('30 minutes', 'wp-photo-contributor-gallery'),
            3600 => __('1 hour (recommended)', 'wp-photo-contributor-gallery'),
            7200 => __('2 hours', 'wp-photo-contributor-gallery'),
            21600 => __('6 hours', 'wp-photo-contributor-gallery'),
            86400 => __('24 hours', 'wp-photo-contributor-gallery')
        );
        ?>
        <div class="wporg-field-container">
            <select id="cache_time" 
                    name="wporg_photos_grid_options[cache_time]" 
                    class="wporg-select-field">
                <?php foreach ($options as $value => $label): ?>
                    <option value="<?php echo $value; ?>" <?php selected($cache_time, $value); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="wporg-field-desc">
                <?php esc_html_e('Longer cache improves performance but delays updates.', 'wp-photo-contributor-gallery'); ?>
            </p>
        </div>
        <?php
    }

    public function new_tab_field_callback() {
        $checked = isset($this->options['open_in_new_tab']) ? $this->options['open_in_new_tab'] : true;
        ?>
        <div class="wporg-field-container">
            <label class="wporg-toggle-container">
                <input type="checkbox" 
                       id="open_in_new_tab" 
                       name="wporg_photos_grid_options[open_in_new_tab]" 
                       value="1" 
                       <?php checked($checked, 1); ?> />
                <span class="wporg-toggle-slider"></span>
                <span class="wporg-toggle-label"><?php esc_html_e('Open photo links in new tab', 'wp-photo-contributor-gallery'); ?></span>
            </label>
            <p class="wporg-field-desc">
                <?php esc_html_e('Keeps visitors on your site while exploring WordPress.org photos.', 'wp-photo-contributor-gallery'); ?>
            </p>
        </div>
        <?php
    }

    public function photo_titles_field_callback() {
        $checked = isset($this->options['show_photo_titles']) ? $this->options['show_photo_titles'] : true;
        ?>
        <div class="wporg-field-container">
            <label class="wporg-toggle-container">
                <input type="checkbox" 
                       id="show_photo_titles" 
                       name="wporg_photos_grid_options[show_photo_titles]" 
                       value="1" 
                       <?php checked($checked, 1); ?> />
                <span class="wporg-toggle-slider"></span>
                <span class="wporg-toggle-label"><?php esc_html_e('Show photo descriptions', 'wp-photo-contributor-gallery'); ?></span>
            </label>
            <p class="wporg-field-desc">
                <?php esc_html_e('Display captions for better context and engagement.', 'wp-photo-contributor-gallery'); ?>
            </p>
        </div>
        <?php
    }

    public function lazy_loading_field_callback() {
        $checked = isset($this->options['enable_lazy_loading']) ? $this->options['enable_lazy_loading'] : true;
        ?>
        <div class="wporg-field-container">
            <label class="wporg-toggle-container">
                <input type="checkbox" 
                       id="enable_lazy_loading" 
                       name="wporg_photos_grid_options[enable_lazy_loading]" 
                       value="1" 
                       <?php checked($checked, 1); ?> />
                <span class="wporg-toggle-slider"></span>
                <span class="wporg-toggle-label"><?php esc_html_e('Enable lazy loading', 'wp-photo-contributor-gallery'); ?></span>
            </label>
            <p class="wporg-field-desc">
                <?php esc_html_e('Improves page speed by loading images on demand.', 'wp-photo-contributor-gallery'); ?>
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

        // Show success message
        add_settings_error(
            'wporg_photos_grid_options',
            'settings_saved',
            __('Settings saved successfully! Your photo gallery is ready.', 'wp-photo-contributor-gallery'),
            'updated'
        );

        return $validated;
    }

    /**
     * Refined admin page with photography-focused design
     */
    public function admin_page_callback() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Handle cache clearing
        if (isset($_POST['action']) && $_POST['action'] === 'clear_cache' && 
            wp_verify_nonce($_POST['wporg_cache_nonce'] ?? '', 'wporg_clear_cache')) {
            $this->clear_cache();
            echo '<div class="notice notice-success is-dismissible">
                <p><strong>Cache cleared.</strong> ' . esc_html__('Your photos will refresh on the next load.', 'wp-photo-contributor-gallery') . '</p>
            </div>';
        }
        ?>
        <div class="wrap wporg-admin-container">
            <!-- Refined Header -->
            <div class="wporg-header">
                <div class="wporg-header-content">
                    <div class="wporg-header-text">
                        <h1>WordPress.org Photo Gallery</h1>
                        <p>Showcase your photo contributions with elegance and simplicity</p>
                    </div>
                    <div class="wporg-header-actions">
                        <a href="https://wordpress.org/photos/" target="_blank" class="wporg-btn-primary">
                            Contribute Photos
                        </a>
                        <a href="https://github.com/askhellosatya/wpphotogallery" target="_blank" class="wporg-btn-secondary">
                            Documentation
                        </a>
                    </div>
                </div>
            </div>

            <div class="wporg-main-content">
                <!-- Settings Panel -->
                <div class="wporg-settings-panel">
                    <form action="options.php" method="post" class="wporg-form">
                        <?php settings_fields('wporg_photos_grid_settings'); ?>
                        
                        <div class="wporg-settings-sections">
                            <?php do_settings_sections('wporg-photos-grid'); ?>
                        </div>
                        
                        <div class="wporg-form-footer">
                            <?php submit_button(__('Save Settings', 'wp-photo-contributor-gallery'), 'primary wporg-btn-save', 'submit', false); ?>
                        </div>
                    </form>
                </div>

                <!-- Sidebar with Usage Examples -->
                <div class="wporg-sidebar">
                    <!-- Usage Examples -->
                    <div class="wporg-card wporg-usage-examples">
                        <div class="wporg-card-header">
                            <h3>Usage Examples</h3>
                            <p>Copy shortcodes for different use cases</p>
                        </div>
                        <div class="wporg-card-content">
                            <div class="wporg-example-item">
                                <div class="wporg-example-header">
                                    <div>
                                        <strong>Portfolio Showcase</strong>
                                        <small>Professional portfolios</small>
                                    </div>
                                </div>
                                <div class="wporg-code-block">
                                    <code>[wporg_photos per_page="20" columns="4"]</code>
                                    <button class="wporg-copy-btn" data-code='[wporg_photos per_page="20" columns="4"]'>Copy</button>
                                </div>
                            </div>

                            <div class="wporg-example-item">
                                <div class="wporg-example-header">
                                    <div>
                                        <strong>About Page Integration</strong>
                                        <small>Personal branding</small>
                                    </div>
                                </div>
                                <div class="wporg-code-block">
                                    <code>[wporg_photos per_page="12" columns="3"]</code>
                                    <button class="wporg-copy-btn" data-code='[wporg_photos per_page="12" columns="3"]'>Copy</button>
                                </div>
                            </div>

                            <div class="wporg-example-item">
                                <div class="wporg-example-header">
                                    <div>
                                        <strong>Blog Post Enhancement</strong>
                                        <small>Content creation</small>
                                    </div>
                                </div>
                                <div class="wporg-code-block">
                                    <code>[wporg_photos per_page="6" columns="2"]</code>
                                    <button class="wporg-copy-btn" data-code='[wporg_photos per_page="6" columns="2"]'>Copy</button>
                                </div>
                            </div>

                            <div class="wporg-example-item">
                                <div class="wporg-example-header">
                                    <div>
                                        <strong>Sidebar Widget</strong>
                                        <small>Ongoing showcase</small>
                                    </div>
                                </div>
                                <div class="wporg-code-block">
                                    <code>[wporg_photos per_page="4" columns="1"]</code>
                                    <button class="wporg-copy-btn" data-code='[wporg_photos per_page="4" columns="1"]'>Copy</button>
                                </div>
                            </div>

                            <div class="wporg-example-item">
                                <div class="wporg-example-header">
                                    <div>
                                        <strong>Default Settings</strong>
                                        <small>Uses configured settings</small>
                                    </div>
                                </div>
                                <div class="wporg-code-block">
                                    <code>[wporg_photos]</code>
                                    <button class="wporg-copy-btn" data-code='[wporg_photos]'>Copy</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Section - Refined -->
                    <?php if (!empty($this->options['default_user_id'])): ?>
                    <div class="wporg-card wporg-preview-card">
                        <div class="wporg-card-header">
                            <h3>Gallery Preview</h3>
                            <p>Sample with current settings</p>
                        </div>
                        <div class="wporg-card-content">
                            <div class="wporg-preview-wrapper">
                                <?php echo do_shortcode('[wporg_photos per_page="2"]'); ?>
                            </div>
                            <div class="wporg-preview-note">
                                <strong>Tip:</strong> Use <code>[wporg_photos]</code> in posts, pages, or widgets
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Tools -->
                    <div class="wporg-card">
                        <div class="wporg-card-header">
                            <h3>Tools & Resources</h3>
                        </div>
                        <div class="wporg-card-content">
                            <!-- Cache Management -->
                            <div class="wporg-tool-item">
                                <div class="wporg-tool-content">
                                    <strong>Clear Cache</strong>
                                    <p>Refresh photo data</p>
                                </div>
                                <form method="post" action="" style="margin: 0;">
                                    <?php wp_nonce_field('wporg_clear_cache', 'wporg_cache_nonce'); ?>
                                    <input type="hidden" name="action" value="clear_cache">
                                    <button type="submit" class="wporg-btn-tool">Clear</button>
                                </form>
                            </div>

                            <!-- Quick Links -->
                            <div class="wporg-tool-links">
                                <a href="https://wordpress.org/photos/" target="_blank" class="wporg-tool-link">
                                    WordPress.org Photos Directory
                                </a>
                                <a href="https://github.com/askhellosatya/wpphotogallery" target="_blank" class="wporg-tool-link">
                                    Plugin Documentation
                                </a>
                                <a href="https://satyamvishwakarma.com/contact" target="_blank" class="wporg-tool-link">
                                    Get Support
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User ID Help Modal -->
            <div id="user-id-help-modal" class="wporg-modal">
                <div class="wporg-modal-backdrop"></div>
                <div class="wporg-modal-container">
                    <div class="wporg-modal-header">
                        <h3>How to Find Your WordPress.org User ID</h3>
                        <button id="close-help-modal" class="wporg-modal-close">&times;</button>
                    </div>
                    <div class="wporg-modal-content">
                        <div class="wporg-help-note">
                            <strong>Important:</strong> Your User ID is a unique number (like <code>21053005</code>), not your username.
                        </div>
                        
                        <div class="wporg-help-steps">
                            <div class="wporg-help-step">
                                <div class="wporg-help-step-number">1</div>
                                <div class="wporg-help-step-content">
                                    <strong>Visit your author page:</strong><br>
                                    <code>https://wordpress.org/photos/author/YOUR-USERNAME/</code>
                                </div>
                            </div>
                            
                            <div class="wporg-help-step">
                                <div class="wporg-help-step-number">2</div>
                                <div class="wporg-help-step-content">
                                    <strong>View page source:</strong><br>
                                    Right-click → "View page source" or press <kbd>Ctrl+U</kbd>
                                </div>
                            </div>
                            
                            <div class="wporg-help-step">
                                <div class="wporg-help-step-number">3</div>
                                <div class="wporg-help-step-content">
                                    <strong>Search for User ID:</strong><br>
                                    Press <kbd>Ctrl+F</kbd>, search for <code>users/</code><br>
                                    Find: <code>wp-json/wp/v2/users/12345678</code><br>
                                    The number at the end is your User ID
                                </div>
                            </div>
                        </div>
                        
                        <div class="wporg-help-example">
                            <h4>Example:</h4>
                            <div>
                                <div><strong>Username:</strong> hellosatya</div>
                                <div><strong>User ID:</strong> 21053005</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Refined Photography-Focused CSS with Layout Fixes -->
        <style>
/* Global Styles - Clean and Minimal */
.wporg-admin-container {
    max-width: none;
    margin: 0 -20px;
    background: #fafafa;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    color: #2c3338;
}

/* Header - Elegant and Understated with white title */
.wporg-header {
    background: linear-gradient(135deg, #2c3338 0%, #1e252b 100%);
    color: #ffffff;
    padding: 50px 30px;
    margin-bottom: 40px;
    position: relative;
}

.wporg-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="20" cy="20" r="1" fill="%23ffffff" opacity="0.03"/><circle cx="80" cy="80" r="1" fill="%23ffffff" opacity="0.03"/><circle cx="40" cy="60" r="1" fill="%23ffffff" opacity="0.02"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.wporg-header-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    z-index: 1;
}

.wporg-header h1 {
    font-size: 28px;
    margin: 0 0 8px 0;
    font-weight: 300;
    letter-spacing: -0.5px;
    color: #ffffff;
}

.wporg-header p {
    font-size: 16px;
    margin: 0;
    opacity: 0.85;
    font-weight: 300;
    color: #ffffff;
}

.wporg-header-actions {
    display: flex;
    gap: 15px;
}

.wporg-btn-primary, .wporg-btn-secondary {
    padding: 12px 24px;
    border-radius: 3px;
    text-decoration: none;
    font-weight: 400;
    font-size: 14px;
    transition: all 0.2s ease;
    letter-spacing: 0.3px;
}

.wporg-btn-primary {
    background: #ffffff;
    color: #2c3338;
    border: 1px solid transparent;
}

.wporg-btn-secondary {
    background: transparent;
    color: #ffffff;
    border: 1px solid rgba(255,255,255,0.3);
}

.wporg-btn-primary:hover {
    background: #f6f7f7;
    color: #2c3338;
    transform: translateY(-1px);
}

.wporg-btn-secondary:hover {
    background: rgba(255,255,255,0.1);
    border-color: rgba(255,255,255,0.5);
    color: #ffffff;
}

/* Main Content Layout - Fixed overflow issue with proper minmax */
.wporg-main-content {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); /* Fixed: Use minmax to prevent overflow */
    gap: 30px; /* Reduced gap slightly */
    padding: 0 30px 60px;
    box-sizing: border-box;
}

/* Settings Panel - Clean Form Design */
.wporg-settings-panel {
    background: #ffffff;
    border-radius: 3px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: 1px solid #e5e5e5;
    min-width: 0; /* Prevent flex item from overflowing */
    overflow: hidden; /* Ensure content doesn't overflow */
}

.wporg-form {
    padding: 0;
}

.wporg-settings-sections .form-table {
    margin: 0;
    background: transparent;
    width: 100%;
    table-layout: fixed; /* Fix table layout to prevent overflow */
}

.wporg-settings-sections h2 {
    background: #f9f9f9;
    margin: 0;
    padding: 20px 30px;
    font-size: 16px;
    font-weight: 500;
    color: #2c3338;
    border-bottom: 1px solid #e5e5e5;
    word-wrap: break-word; /* Prevent text overflow */
}

.wporg-section-intro {
    padding: 15px 30px 0;
    background: #f9f9f9;
    margin-bottom: 0;
}

.wporg-section-intro p {
    margin: 0 0 15px 0;
    color: #646970;
    font-size: 14px;
}

.wporg-settings-sections .form-table th {
    background: transparent;
    font-weight: 500;
    color: #2c3338;
    padding: 20px 25px; /* Reduced padding slightly */
    border-bottom: 1px solid #f0f0f1;
    width: 35%; /* Set fixed width percentage */
    font-size: 14px;
    word-wrap: break-word;
}

.wporg-settings-sections .form-table td {
    padding: 20px 25px; /* Reduced padding slightly */
    border-bottom: 1px solid #f0f0f1;
    width: 65%; /* Set fixed width percentage */
    word-wrap: break-word;
}

/* Form Fields - Minimal and Functional */
.wporg-field-container {
    max-width: 100%; /* Ensure it doesn't overflow */
    min-width: 0;
}

.wporg-input-group {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
    max-width: 100%;
}

.wporg-input-field, .wporg-select-field {
    flex: 1;
    min-width: 0; /* Allow shrinking */
    padding: 10px 12px;
    border: 1px solid #dcdcde;
    border-radius: 3px;
    font-size: 14px;
    background: #ffffff;
    transition: border-color 0.2s ease;
    box-sizing: border-box;
}

.wporg-input-field:focus, .wporg-select-field:focus {
    border-color: #2c3338;
    outline: none;
    box-shadow: 0 0 0 1px #2c3338;
}

.wporg-help-btn {
    background: #2c3338;
    color: white;
    border: none;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    flex-shrink: 0; /* Prevent shrinking */
}

.wporg-field-status {
    font-size: 13px;
    margin: 8px 0;
    min-height: 18px;
    word-wrap: break-word;
}

.wporg-field-status.success { color: #00a32a; }
.wporg-field-status.error { color: #d63638; }

.wporg-field-desc {
    margin: 8px 0 0 0 !important;
    font-size: 13px;
    color: #646970;
    line-height: 1.5;
    word-wrap: break-word;
}

.wporg-help-link {
    color: #2c3338;
    text-decoration: none;
    font-weight: 400;
}

/* Range Slider */
.wporg-range-container {
    display: flex;
    align-items: center;
    gap: 10px; /* Reduced gap */
    margin-bottom: 10px;
    max-width: 100%;
}

.wporg-range-slider {
    flex: 1;
    min-width: 0;
    height: 3px;
    background: #dcdcde;
    border-radius: 3px;
    outline: none;
    -webkit-appearance: none;
}

.wporg-range-slider::-webkit-slider-thumb {
    appearance: none;
    width: 18px;
    height: 18px;
    background: #2c3338;
    border-radius: 50%;
    cursor: pointer;
}

.wporg-range-value {
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

/* Column Grid */
.wporg-columns-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr); /* Changed to 2 columns to prevent overflow */
    gap: 10px; /* Reduced gap */
    margin-bottom: 15px;
}

.wporg-column-option {
    cursor: pointer;
}

.wporg-column-option input {
    display: none;
}

.wporg-column-card {
    padding: 12px; /* Reduced padding */
    border: 1px solid #dcdcde;
    border-radius: 3px;
    text-align: center;
    transition: all 0.2s ease;
    background: white;
    min-width: 0;
}

.wporg-column-option.selected .wporg-column-card,
.wporg-column-option input:checked + .wporg-column-card {
    border-color: #2c3338;
    background: #f9f9f9;
}

.wporg-column-label {
    font-weight: 500;
    color: #2c3338;
    margin-bottom: 4px;
    font-size: 13px; /* Reduced font size */
}

.wporg-column-desc {
    font-size: 11px; /* Reduced font size */
    color: #646970;
}

/* Toggle Switches */
.wporg-toggle-container {
    display: flex;
    align-items: center;
    gap: 15px;
    cursor: pointer;
    padding: 0;
    max-width: 100%;
}

.wporg-toggle-container input {
    display: none;
}

.wporg-toggle-slider {
    position: relative;
    width: 44px;
    height: 24px;
    background: #dcdcde;
    border-radius: 24px;
    transition: background 0.2s;
    flex-shrink: 0;
}

.wporg-toggle-slider:before {
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

.wporg-toggle-container input:checked + .wporg-toggle-slider {
    background: #2c3338;
}

.wporg-toggle-container input:checked + .wporg-toggle-slider:before {
    transform: translateX(20px);
}

.wporg-toggle-label {
    font-size: 14px;
    color: #2c3338;
    font-weight: 500;
    word-wrap: break-word;
    min-width: 0;
}

/* Form Footer */
.wporg-form-footer {
    padding: 25px; /* Reduced padding */
    background: #f9f9f9;
    text-align: center;
    border-top: 1px solid #e5e5e5;
}

.wporg-btn-save {
    background: #2c3338 !important;
    border: none !important;
    padding: 12px 30px !important;
    font-size: 14px !important;
    font-weight: 400 !important;
    border-radius: 3px !important;
    letter-spacing: 0.3px !important;
    transition: all 0.2s ease !important;
}

.wporg-btn-save:hover {
    background: #1e252b !important;
    transform: translateY(-1px) !important;
}

/* Sidebar - Fixed overflow */
.wporg-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px; /* Reduced gap */
    min-width: 0; /* Prevent overflow */
}

.wporg-card {
    background: white;
    border-radius: 3px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: 1px solid #e5e5e5;
    overflow: hidden;
    min-width: 0;
}

.wporg-card-header {
    padding: 18px 20px; /* Reduced padding */
    background: #f9f9f9;
    border-bottom: 1px solid #e5e5e5;
}

.wporg-card-header h3 {
    margin: 0 0 5px 0;
    font-size: 15px; /* Slightly reduced */
    font-weight: 500;
    color: #2c3338;
    word-wrap: break-word;
}

.wporg-card-header p {
    margin: 0;
    font-size: 12px; /* Reduced font size */
    color: #646970;
}

.wporg-card-content {
    padding: 20px; /* Reduced padding */
}

/* Usage Examples - Better spacing */
.wporg-example-item {
    margin-bottom: 16px; /* Reduced margin */
    padding: 12px; /* Reduced padding */
    background: #f9f9f9;
    border-radius: 3px;
    border-left: 3px solid #2c3338;
}

.wporg-example-item:last-child {
    margin-bottom: 0;
}

.wporg-example-header {
    margin-bottom: 8px; /* Reduced margin */
}

.wporg-example-header strong {
    display: block;
    color: #2c3338;
    font-size: 13px; /* Reduced font size */
    margin-bottom: 2px;
}

.wporg-example-header small {
    color: #646970;
    font-size: 11px; /* Reduced font size */
}

.wporg-code-block {
    display: flex;
    align-items: center;
    gap: 8px; /* Reduced gap */
    background: white;
    padding: 8px 10px; /* Reduced padding */
    border-radius: 3px;
    border: 1px solid #e5e5e5;
    min-width: 0;
}

.wporg-code-block code {
    flex: 1;
    min-width: 0;
    background: none;
    font-family: Consolas, Monaco, monospace;
    font-size: 11px; /* Reduced font size */
    color: #2c3338;
    word-break: break-all; /* Allow breaking of long codes */
}

.wporg-copy-btn {
    background: #2c3338;
    color: white;
    border: none;
    padding: 4px 8px; /* Reduced padding */
    border-radius: 3px;
    cursor: pointer;
    font-size: 10px; /* Reduced font size */
    font-weight: 500;
    letter-spacing: 0.3px;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.wporg-copy-btn:hover {
    background: #1e252b;
}

/* Preview Section - Clean 2-image layout with high quality small sizes */
.wporg-preview-wrapper {
    background: #f9f9f9;
    padding: 12px; /* Reduced padding */
    border-radius: 6px;
    border: 1px solid #e5e5e5;
    margin-bottom: 12px; /* Reduced margin */
    max-width: 250px; /* Reduced max width */
    margin-left: auto;
    margin-right: auto;
}

.wporg-preview-wrapper .wporg-photos-grid {
    padding: 0;
    gap: 8px; /* Reduced gap */
    display: flex;
    justify-content: center;
    align-items: center;
}

.wporg-preview-wrapper .wporg-photo-card {
    max-width: 100px; /* Reduced size */
    min-width: 100px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    border-radius: 8px;
    overflow: hidden;
}

.wporg-preview-wrapper .wporg-photo-image {
    aspect-ratio: 1;
    overflow: hidden;
}

.wporg-preview-wrapper .wporg-photo-image img {
    width: 100px; /* Reduced size */
    height: 100px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.wporg-preview-wrapper .wporg-photo-card:hover .wporg-photo-image img {
    transform: scale(1.05);
}

.wporg-preview-wrapper .wporg-photo-content {
    padding: 6px; /* Reduced padding */
    text-align: center;
}

.wporg-preview-wrapper .wporg-photo-content p {
    font-size: 10px; /* Reduced font size */
    line-height: 1.3;
    margin: 0;
    color: #2c3338;
    font-weight: 400;
}

.wporg-preview-note {
    font-size: 12px; /* Reduced font size */
    color: #646970;
    text-align: center;
    padding: 10px; /* Reduced padding */
    background: #f9f9f9;
    border-radius: 6px;
    border: 1px solid #e5e5e5;
}

.wporg-preview-note code {
    background: white;
    padding: 2px 6px; /* Reduced padding */
    border-radius: 4px;
    font-size: 11px; /* Reduced font size */
    border: 1px solid #ddd;
    font-family: 'Monaco', 'Consolas', monospace;
    color: #2c3338;
}

/* Tools Section - Enhanced Styling for Attractiveness */
.wporg-tool-item {
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

.wporg-tool-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.wporg-tool-content {
    display: flex;
    align-items: center;
    gap: 12px;
}

.wporg-tool-content::before {
    content: "🔄";
    font-size: 18px;
    display: inline-block;
}

.wporg-tool-content strong {
    display: block;
    color: #2c3338;
    font-size: 14px;
    margin-bottom: 2px;
    font-weight: 600;
}

.wporg-tool-content p {
    margin: 0;
    color: #646970;
    font-size: 12px;
}

.wporg-btn-tool {
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

.wporg-btn-tool:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(44, 51, 56, 0.3);
    background: linear-gradient(135deg, #1e252b 0%, #0f1419 100%);
}

.wporg-tool-links {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.wporg-tool-link {
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

.wporg-tool-link::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(44, 51, 56, 0.1), transparent);
    transition: left 0.5s;
}

.wporg-tool-link:hover::before {
    left: 100%;
}

.wporg-tool-link:hover {
    background: linear-gradient(135deg, #2c3338 0%, #1e252b 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(44, 51, 56, 0.2);
    border-color: #2c3338;
}

/* Add icons to tool links */
.wporg-tool-link:nth-child(1)::after {
    content: "📸";
    font-size: 16px;
    margin-left: auto;
}

.wporg-tool-link:nth-child(2)::after {
    content: "📖";
    font-size: 16px;
    margin-left: auto;
}

.wporg-tool-link:nth-child(3)::after {
    content: "💬";
    font-size: 16px;
    margin-left: auto;
}

/* Modal - Clean and Functional */
.wporg-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
}

.wporg-modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
}

.wporg-modal-container {
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

.wporg-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    background: #f9f9f9;
    border-bottom: 1px solid #e5e5e5;
}

.wporg-modal-header h3 {
    margin: 0;
    color: #2c3338;
    font-size: 16px;
    font-weight: 500;
}

.wporg-modal-close {
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

.wporg-modal-close:hover {
    background: #e5e5e5;
    color: #2c3338;
}

.wporg-modal-content {
    padding: 25px;
}

.wporg-help-note {
    background: #fff3cd;
    padding: 15px;
    border-radius: 3px;
    margin-bottom: 20px;
    border-left: 3px solid #f0ad4e;
    font-size: 14px;
}

.wporg-help-steps {
    margin-bottom: 20px;
}

.wporg-help-step {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
}

.wporg-help-step-number {
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

.wporg-help-step-content {
    flex: 1;
    font-size: 14px;
}

.wporg-help-step-content strong {
    display: block;
    margin-bottom: 4px;
    color: #2c3338;
}

.wporg-help-step-content code {
    background: #f9f9f9;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 13px;
    border: 1px solid #e5e5e5;
}

.wporg-help-step-content kbd {
    background: #2c3338;
    color: white;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: monospace;
    font-size: 12px;
}

.wporg-help-example {
    background: #d4edda;
    padding: 15px;
    border-radius: 3px;
    border-left: 3px solid #28a745;
}

.wporg-help-example h4 {
    margin: 0 0 10px 0;
    color: #155724;
    font-size: 14px;
}

.wporg-help-example div {
    font-size: 13px;
    margin-bottom: 5px;
}

.wporg-help-example strong {
    color: #155724;
}

.wporg-help-example code {
    background: rgba(21, 87, 36, 0.1);
}

/* Responsive Design - Updated for better overflow handling */
@media (max-width: 1200px) {
    .wporg-main-content {
        max-width: 100%;
        padding: 0 20px 60px;
    }
}

@media (max-width: 1024px) {
    .wporg-main-content {
        grid-template-columns: 1fr;
        gap: 25px;
        padding: 0 20px 40px;
    }
    
    .wporg-header-content {
        flex-direction: column;
        gap: 20px;
        text-align: center;
    }
    
    .wporg-columns-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .wporg-preview-wrapper {
        max-width: 100%;
    }
    
    .wporg-settings-sections .form-table th,
    .wporg-settings-sections .form-table td {
        display: block;
        width: 100%;
        padding: 15px 25px;
    }
    
    .wporg-settings-sections .form-table th {
        border-bottom: none;
        padding-bottom: 10px;
    }
    
    .wporg-settings-sections .form-table td {
        padding-top: 0;
    }
}

@media (max-width: 640px) {
    .wporg-main-content {
        padding: 0 15px 30px;
    }
    
    .wporg-header {
        padding: 25px 15px;
    }
    
    .wporg-columns-grid {
        grid-template-columns: 1fr;
    }
    
    .wporg-card-content {
        padding: 15px;
    }
    
    .wporg-preview-wrapper .wporg-photos-grid {
        flex-direction: column;
        gap: 6px;
    }
    
    .wporg-preview-wrapper .wporg-photo-card {
        max-width: 80px;
        min-width: 80px;
    }
    
    .wporg-preview-wrapper .wporg-photo-image img {
        width: 80px;
        height: 80px;
    }
    
    .wporg-code-block {
        flex-direction: column;
        align-items: stretch;
        gap: 6px;
    }
    
    .wporg-copy-btn {
        align-self: flex-end;
        width: auto;
    }
}
</style>

        <?php
    }

    /**
     * Enhanced admin footer script
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
            $('.wporg-column-option input').on('change', function() {
                $('.wporg-column-option').removeClass('selected');
                $(this).closest('.wporg-column-option').addClass('selected');
            });
            
            // Copy functionality
            $('.wporg-copy-btn').on('click', function() {
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
            
            $('#close-help-modal, .wporg-modal-backdrop').on('click', function() {
                $('#user-id-help-modal').fadeOut(200);
            });
            
            $('.wporg-modal-container').on('click', function(e) {
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
     * Clear cache
     */
    private function clear_cache() {
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wporg_photos_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wporg_photos_%'");
    }

    /**
     * Enqueue styles
     */
    public function enqueue_styles() {
        $css = '
        .wporg-photos-grid {
            display: grid;
            gap: 1.5rem;
            padding: 1rem 0;
        }
        
        .wporg-photos-grid.columns-1 { grid-template-columns: 1fr; }
        .wporg-photos-grid.columns-2 { grid-template-columns: repeat(2, 1fr); }
        .wporg-photos-grid.columns-3 { grid-template-columns: repeat(3, 1fr); }
        .wporg-photos-grid.columns-4 { grid-template-columns: repeat(4, 1fr); }
        .wporg-photos-grid.columns-5 { grid-template-columns: repeat(5, 1fr); }
        .wporg-photos-grid.columns-6 { grid-template-columns: repeat(6, 1fr); }
        
        @media (max-width: 768px) {
            .wporg-photos-grid {
                grid-template-columns: 1fr !important;
            }
        }
        
        .wporg-photo-card {
            background: #fff;
            border-radius: 3px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
            border: 1px solid #e5e5e5;
        }
        
        .wporg-photo-card:hover {
            transform: translateY(-2px);
        }
        
        .wporg-photo-card a {
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .wporg-photo-image {
            aspect-ratio: 1;
            overflow: hidden;
        }
        
        .wporg-photo-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .wporg-photo-content {
            padding: 1rem;
        }
        
        .wporg-photo-content p {
            margin: 0;
            font-size: 14px;
            color: #2c3338;
        }
        
        .wporg-photos-error {
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
        ), $atts, 'wporg_photos');

        if (empty($atts['user_id'])) {
            return '<div class="wporg-photos-error">' . esc_html__('Please set your User ID in plugin settings.', 'wp-photo-contributor-gallery') . '</div>';
        }

        $user_id = sanitize_text_field($atts['user_id']);
        $per_page = max(1, min(50, absint($atts['per_page'])));
        $columns = max(1, min(6, absint($atts['columns'])));

        $photos = $this->get_photos($user_id, $per_page);

        if (is_wp_error($photos)) {
            return '<div class="wporg-photos-error">' . esc_html($photos->get_error_message()) . '</div>';
        }

        if (empty($photos)) {
            return '<div class="wporg-photos-error">' . esc_html__('No photos found.', 'wp-photo-contributor-gallery') . '</div>';
        }

        return $this->render_grid($photos, $columns);
    }

    /**
     * Get photos from API
     */
    private function get_photos($user_id, $per_page) {
        $cache_key = 'wporg_photos_' . md5($user_id . '_' . $per_page);
        $photos = get_transient($cache_key);

        if (false !== $photos) {
            return $photos;
        }

        $api_url = add_query_arg(array(
            'author' => $user_id,
            'per_page' => $per_page,
            '_embed' => 'wp:featuredmedia'
        ), 'https://wordpress.org/photos/wp-json/wp/v2/photos/');

        $response = wp_remote_get($api_url, array('timeout' => 15));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $photos = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', __('Invalid response from API', 'wp-photo-contributor-gallery'));
        }

        set_transient($cache_key, $photos, $this->options['cache_time'] ?? 3600);
        return $photos;
    }

    /**
     * Render grid
     */
    private function render_grid($photos, $columns) {
        $output = sprintf('<div class="wporg-photos-grid columns-%d">', esc_attr($columns));

        foreach ($photos as $photo) {
            $image_url = '';
            $title = '';
            $link = $photo['link'] ?? '';

            if (isset($photo['_embedded']['wp:featuredmedia'][0]['media_details']['sizes']['large']['source_url'])) {
                $image_url = $photo['_embedded']['wp:featuredmedia'][0]['media_details']['sizes']['large']['source_url'];
            }

            if (isset($photo['content']['rendered'])) {
                $title = wp_strip_all_tags($photo['content']['rendered']);
                $title = mb_substr($title, 0, 30) . (mb_strlen($title) > 30 ? '...' : '');
            }

            if ($image_url && $link) {
                $target = ($this->options['open_in_new_tab'] ?? true) ? ' target="_blank" rel="noopener"' : '';
                $show_title = ($this->options['show_photo_titles'] ?? true) && !empty($title);
                
                $output .= sprintf('
                <div class="wporg-photo-card">
                    <a href="%s"%s>
                        <div class="wporg-photo-image">
                            <img src="%s" alt="%s" loading="lazy">
                        </div>
                        %s
                    </a>
                </div>',
                    esc_url($link),
                    $target,
                    esc_url($image_url),
                    esc_attr($title),
                    $show_title ? '<div class="wporg-photo-content"><p>' . esc_html($title) . '</p></div>' : ''
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
        add_option('wporg_photos_grid_options', $this->get_default_options());
    }

    /**
     * Plugin deactivation
     */
    public function plugin_deactivate() {
        $this->clear_cache();
    }
}

// Initialize the plugin
new WPOrgPhotosGrid();