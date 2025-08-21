<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin class for Contributor Photo Gallery
 * - Registers settings, renders settings page, enqueues admin assets
 * - Shows a site-wide setup notice when plugin is not configured
 * - Shows a one-time settings-page "new shortcode" notice
 * - Provides AJAX handlers to persist dismissals
 */
class CPG_Admin {

    private $options;

    public function __construct() {
        $this->options = cpg_get_plugin_options();


        add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
        add_action( 'admin_init', [ $this, 'admin_init' ] );

        // enqueue on admin pages (we filter by hook inside the method)
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_assets' ] );

        // Show site-wide setup notice (if plugin likely needs initial setup)
        add_action( 'admin_notices', [ $this, 'maybe_show_setup_notice' ] );

        // Show shortcode update notice on settings page
        add_action( 'admin_notices', [ $this, 'maybe_show_shortcode_notice' ] );

        // AJAX handlers for dismissible notices
        add_action( 'wp_ajax_cpg_dismiss_new_shortcode_notice', [ $this, 'ajax_dismiss_new_shortcode_notice' ] );
        add_action( 'wp_ajax_cpg_dismiss_setup_notice', [ $this, 'ajax_dismiss_setup_notice' ] );
        add_action( 'wp_ajax_cpg_dismiss_shortcode_notice', [ $this, 'ajax_dismiss_shortcode_notice' ] );
    }

    /**
     * Register admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'Contributor Photo Gallery Settings', 'contributor-photo-gallery' ),
            __( 'Contributor Photo Gallery', 'contributor-photo-gallery' ),
            'manage_options',
            'contributor-photo-gallery',
            [ $this, 'settings_page' ],
            'dashicons-camera',
            58
        );
        
        // Add settings submenu
        add_submenu_page(
            'contributor-photo-gallery',
            __( 'Settings', 'contributor-photo-gallery' ),
            __( 'Settings', 'contributor-photo-gallery' ),
            'manage_options',
            'contributor-photo-gallery',
            [ $this, 'settings_page' ]
        );
        
        // Add plugin action links
        add_filter( 'plugin_action_links_' . plugin_basename( CPG_PLUGIN_PATH . 'contributor-photo-gallery.php' ), [ $this, 'add_plugin_action_links' ] );
    }
    
    /**
     * Add plugin action links
     */
    public function add_plugin_action_links( $links ) {
        $settings_link = '<a href="' . admin_url( 'admin.php?page=contributor-photo-gallery' ) . '">' . __( 'Settings', 'contributor-photo-gallery' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

    /**
     * Register settings, sections and fields
     */
    public function admin_init() {
        register_setting( 'cpg_settings', 'cpg_options', [ 'sanitize_callback' => [ $this, 'validate_options' ] ] );

        add_settings_section( 'cpg_main', __( 'Essential Configuration', 'contributor-photo-gallery' ), [ $this, 'settings_section_callback' ], 'contributor-photo-gallery' );
        add_settings_section( 'cpg_styling', __( 'Card Styling & Appearance', 'contributor-photo-gallery' ), [ $this, 'styling_settings_section_callback' ], 'contributor-photo-gallery' );
        add_settings_section( 'cpg_advanced', __( 'Display & Performance', 'contributor-photo-gallery' ), [ $this, 'advanced_settings_section_callback' ], 'contributor-photo-gallery' );

        $this->add_settings_fields();
    }

    /**
     * Enqueue admin scripts/styles — only for our settings page (and related admin screens)
     */
    public function admin_enqueue_assets( $hook ) {

    // Only our settings page (or any screen containing our slug)
    if ( $hook !== 'toplevel_page_contributor-photo-gallery' && strpos( $hook, 'contributor-photo-gallery' ) === false ) {
        return;
    }

    wp_enqueue_style( 'cpg-admin', CPG_PLUGIN_URL . 'assets/css/admin.css', [], CPG_VERSION );
    wp_enqueue_script( 'cpg-admin', CPG_PLUGIN_URL . 'assets/js/admin.js', [ 'jquery' ], CPG_VERSION, true );

    wp_localize_script( 'cpg-admin', 'wpcpgAdmin', [
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'wpcpg_admin_nonce' ),
    ] );
}

    /**
     * Show setup notice if user ID is not configured
     */
    public function maybe_show_setup_notice() {
        
        $options = cpg_get_plugin_options();
        $user_id = isset( $options['default_user_id'] ) ? $options['default_user_id'] : '';

        if ( ! empty( $user_id ) ) {
            return;
        }

        $settings_url = esc_url( admin_url( 'admin.php?page=contributor-photo-gallery' ) );
        $nonce = wp_create_nonce( 'cpg_setup_nonce' );

        // Beautiful, elegant setup notice matching WordPress.org plugin style
        ?>
        <div class="cpg-setup-notice-wrapper">
            <div class="cpg-setup-notice" data-cpg-nonce="<?php echo esc_attr( $nonce ); ?>" data-cpg-action="cpg_dismiss_setup_notice">
                <div class="cpg-setup-notice-content">
                    <div class="cpg-setup-notice-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2L13.09 8.26L20 9L13.09 9.74L12 16L10.91 9.74L4 9L10.91 8.26L12 2Z" fill="currentColor"/>
                        </svg>
                    </div>
                    <div class="cpg-setup-notice-text">
                        <strong><?php esc_html_e( 'Contributor Photo Gallery', 'contributor-photo-gallery' ); ?>:</strong>
                        <span><?php esc_html_e( 'Ready to showcase your photo contributions?', 'contributor-photo-gallery' ); ?></span>
                        <a href="<?php echo esc_url( $settings_url ); ?>" class="cpg-setup-notice-link"><?php esc_html_e( 'Complete Setup →', 'contributor-photo-gallery' ); ?></a>
                    </div>
                    <button type="button" class="cpg-setup-notice-dismiss" aria-label="<?php esc_attr_e( 'Dismiss this notice', 'contributor-photo-gallery' ); ?>">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
            </div>
        </div>

        <script type="text/javascript">
        (function(){
            console.log('CPG: Setup notice script loaded');
            
            // Clean dismiss functionality
            document.addEventListener('click', function (e) {
                var el = e.target;
                if (el && (el.classList.contains('cpg-setup-notice-dismiss') || el.closest('.cpg-setup-notice-dismiss'))) {
                    e.preventDefault();
                    console.log('CPG: Dismiss button clicked');
                    var notice = el.closest('.cpg-setup-notice');
                    if (notice) {
                        // Smooth fade out
                        notice.style.opacity = '0';
                        notice.style.transform = 'translateY(-10px)';

                        var nonce = notice.getAttribute('data-cpg-nonce') || '';
                        var data = new FormData();
                        data.append('action', 'cpg_dismiss_setup_notice');
                        data.append('nonce', nonce);

                        fetch("<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>", {
                            method: 'POST',
                            credentials: 'same-origin',
                            body: data
                        }).catch(function(){ /* ignore network errors */ });
                        
                    }
                }
            }, { capture: true });

            // Simple entrance animation
            document.addEventListener('DOMContentLoaded', function() {
                var notice = document.querySelector('.cpg-setup-notice');
                if (notice) {
                    console.log('CPG: Notice found, adding animation');
                    setTimeout(function() {
                        notice.style.opacity = '1';
                        notice.style.transform = 'translateY(0)';
                    }, 100);
                } else {
                    console.log('CPG: No notice found in DOM');
                }
            });
        })();
        </script>
        <?php
    }

    /**
     * Show shortcode update notice on settings page
     */
    public function maybe_show_shortcode_notice() {
        // Only show on our settings page
        if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'contributor-photo-gallery' ) { // phpcs:ignore
            return;
        }

        // Check if user has dismissed this notice
        $dismissed = get_option( 'cpg_shortcode_notice_dismissed', 0 );
        if ( $dismissed ) {
            return;
        }

        ?>
        <div class="cpg-shortcode-notice-wrapper">
            <div class="cpg-shortcode-notice" data-cpg-action="cpg_dismiss_shortcode_notice">
                <div class="cpg-shortcode-notice-content">
                    <div class="cpg-shortcode-notice-icon">
                        <span class="dashicons dashicons-shortcode"></span>
                    </div>
                    <div class="cpg-shortcode-notice-text">
                        <strong><?php esc_html_e( 'Shortcode Updated:', 'contributor-photo-gallery' ); ?></strong>
                        <span>
                            <?php esc_html_e( 'The gallery shortcode has changed to', 'contributor-photo-gallery' ); ?>
                            <code>[cp_gallery]</code>.<?php esc_html_e( 'For the best experience, please update your existing shortcodes. Your old shortcodes will continue to work for now.', 'contributor-photo-gallery' ); ?>
                        </span>
                    </div>
                    <button type="button" class="cpg-shortcode-notice-dismiss" aria-label="<?php esc_attr_e( 'Dismiss this notice', 'contributor-photo-gallery' ); ?>">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Add settings fields (kept same field set as your implementation)
     */
    private function add_settings_fields() {
        $fields = array(
            'default_user_id'       => array( 'title' => __( 'User ID', 'contributor-photo-gallery' ), 'callback' => [ $this, 'user_id_field_callback' ], 'section' => 'cpg_main' ),
            'default_per_page'      => array( 'title' => __( 'Photos Per Page', 'contributor-photo-gallery' ), 'callback' => [ $this, 'per_page_field_callback' ], 'section' => 'cpg_main' ),
            'default_columns'       => array( 'title' => __( 'Grid Layout', 'contributor-photo-gallery' ), 'callback' => [ $this, 'columns_field_callback' ], 'section' => 'cpg_main' ),
            'card_style'            => array( 'title' => '', 'callback' => [ $this, 'card_style_field_callback' ], 'section' => 'cpg_styling' ),
            'card_bg_color'         => array( 'title' => '', 'callback' => [ $this, 'card_bg_color_field_callback' ], 'section' => 'cpg_styling' ),
            'card_border_style'     => array( 'title' => '', 'callback' => [ $this, 'card_border_style_field_callback' ], 'section' => 'cpg_styling' ),
            'card_shadow_style'     => array( 'title' => '', 'callback' => [ $this, 'card_shadow_style_field_callback' ], 'section' => 'cpg_styling' ),
            'show_captions'         => array( 'title' => '', 'callback' => [ $this, 'show_captions_field_callback' ], 'section' => 'cpg_styling' ),
            'caption_text_color'    => array( 'title' => '', 'callback' => [ $this, 'caption_text_color_field_callback' ], 'section' => 'cpg_styling' ),
            'cache_time'            => array( 'title' => __( 'Cache Duration', 'contributor-photo-gallery' ), 'callback' => [ $this, 'cache_time_field_callback' ], 'section' => 'cpg_advanced' ),
            'open_in_new_tab'       => array( 'title' => __( 'Link Behavior', 'contributor-photo-gallery' ), 'callback' => [ $this, 'new_tab_field_callback' ], 'section' => 'cpg_advanced' ),
            'enable_lazy_loading'   => array( 'title' => __( 'Lazy Loading', 'contributor-photo-gallery' ), 'callback' => [ $this, 'lazy_loading_field_callback' ], 'section' => 'cpg_advanced' ),
        );

        foreach ( $fields as $id => $field ) {
            add_settings_field( $id, $field['title'], $field['callback'], 'contributor-photo-gallery', $field['section'] );
        }
    }

    /* -------------------------
       Section callbacks
       ------------------------- */
    public function settings_section_callback() {
        echo '<div class="cpg-section-intro"><p>' . esc_html__( 'Configure basic gallery display settings.', 'contributor-photo-gallery' ) . '</p></div>';
    }

    public function styling_settings_section_callback() {
        echo '<div class="cpg-section-intro"><p>' . esc_html__( 'Customize the visual appearance of photo cards.', 'contributor-photo-gallery' ) . '</p></div>';
    }

    public function advanced_settings_section_callback() {
        echo '<div class="cpg-section-intro"><p>' . esc_html__( 'Performance and behavior optimization settings.', 'contributor-photo-gallery' ) . '</p></div>';
    }

    /* -------------------------
       Field callbacks (copied/kept from your implementation)
       ------------------------- */

    public function user_id_field_callback() {
        $user_id = isset( $this->options['default_user_id'] ) ? $this->options['default_user_id'] : '';
        ?>
        <div class="cpg-field-container">
            <div class="cpg-input-group">
                <input type="text" id="default_user_id" name="cpg_options[default_user_id]" value="<?php echo esc_attr( $user_id ); ?>" class="cpg-input-field" placeholder="<?php esc_attr_e( 'e.g., 21053005', 'contributor-photo-gallery' ); ?>" />
                <button type="button" id="user-id-help-btn" class="cpg-help-btn" title="<?php esc_attr_e( 'How to find your User ID', 'contributor-photo-gallery' ); ?>">?</button>
            </div>
            <div id="user-id-status" class="cpg-field-status"></div>
            <p class="cpg-field-desc">
                <?php esc_html_e( 'Your unique contributor numeric ID from WordPress.org profile.', 'contributor-photo-gallery' ); ?>
                <a href="#" id="user-id-guide-toggle" class="cpg-help-link"><?php esc_html_e( 'Need help? →', 'contributor-photo-gallery' ); ?></a>
            </p>
        </div>
        <?php
    }

    public function per_page_field_callback() {
        $per_page = isset( $this->options['default_per_page'] ) ? $this->options['default_per_page'] : 12;
        ?>
        <div class="cpg-field-container">
            <div class="cpg-range-container">
                <input type="range" id="per_page_range" min="1" max="50" value="<?php echo esc_attr( $per_page ); ?>" class="cpg-range-slider" />
                <div class="cpg-range-value">
                    <span id="per_page_display"><?php echo esc_html( $per_page ); ?></span> <?php esc_html_e( 'photos', 'contributor-photo-gallery' ); ?>
                </div>
            </div>
            <input type="hidden" id="default_per_page" name="cpg_options[default_per_page]" value="<?php echo esc_attr( $per_page ); ?>" />
            <p class="cpg-field-desc">
                <?php esc_html_e( 'Number of photos to display per gallery page.', 'contributor-photo-gallery' ); ?>
            </p>
        </div>
        <?php
    }

    public function columns_field_callback() {
        $columns = isset( $this->options['default_columns'] ) ? $this->options['default_columns'] : 4;
        $column_options = array(
            1 => array( 'label' => __( 'Single', 'contributor-photo-gallery' ), 'desc' => __( 'One column', 'contributor-photo-gallery' ) ),
            2 => array( 'label' => __( 'Two Columns', 'contributor-photo-gallery' ), 'desc' => __( 'Compact grid', 'contributor-photo-gallery' ) ),
            3 => array( 'label' => __( 'Three Columns', 'contributor-photo-gallery' ), 'desc' => __( 'Portfolio layout', 'contributor-photo-gallery' ) ),
            4 => array( 'label' => __( 'Four Columns', 'contributor-photo-gallery' ), 'desc' => __( 'Balanced grid', 'contributor-photo-gallery' ) ),
            5 => array( 'label' => __( 'Five Columns', 'contributor-photo-gallery' ), 'desc' => __( 'Dense layout', 'contributor-photo-gallery' ) ),
            6 => array( 'label' => __( 'Six Columns', 'contributor-photo-gallery' ), 'desc' => __( 'Maximum width', 'contributor-photo-gallery' ) ),
        );
        ?>
        <div class="cpg-field-container">
            <div class="cpg-columns-grid">
                <?php foreach ( $column_options as $value => $option ): ?>
                    <label class="cpg-column-option <?php echo $columns == $value ? 'selected' : ''; ?>" data-value="<?php echo esc_attr( $value ); ?>">
                        <input type="radio" name="cpg_options[default_columns]" value="<?php echo esc_attr( $value ); ?>" <?php checked( $columns, $value ); ?> class="cpg-column-radio" />
                        <div class="cpg-column-card">
                            <div class="cpg-column-label"><?php echo esc_html( $option['label'] ); ?></div>
                            <div class="cpg-column-desc"><?php echo esc_html( $option['desc'] ); ?></div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
            <p class="cpg-field-desc">
                <?php esc_html_e( 'Gallery grid automatically adapts for mobile devices.', 'contributor-photo-gallery' ); ?>
            </p>
        </div>
        <?php
    }

    public function card_style_field_callback() {
        $card_style = isset( $this->options['card_style'] ) ? $this->options['card_style'] : 'default';
        $style_options = array(
            'default'  => array( 'label' => __( 'Modern', 'contributor-photo-gallery' ), 'desc' => __( 'Clean & minimal', 'contributor-photo-gallery' ) ),
            'polaroid' => array( 'label' => __( 'Polaroid', 'contributor-photo-gallery' ), 'desc' => __( 'Vintage style', 'contributor-photo-gallery' ) ),
            'circle'   => array( 'label' => __( 'Circle', 'contributor-photo-gallery' ), 'desc' => __( 'Rounded images', 'contributor-photo-gallery' ) ),
            'fixed'    => array( 'label' => __( 'Fixed Height', 'contributor-photo-gallery' ), 'desc' => __( 'Uniform cards', 'contributor-photo-gallery' ) ),
        );
        ?>
        <label class="cpg-label"><?php esc_html_e( 'Card Style', 'contributor-photo-gallery' ); ?></label>
        <div class="cpg-field-container">
            <div class="cpg-style-grid">
                <?php foreach ( $style_options as $value => $option ): ?>
                    <label class="cpg-style-option <?php echo $card_style == $value ? 'selected' : ''; ?>">
                        <input type="radio" name="cpg_options[card_style]" value="<?php echo esc_attr( $value ); ?>" <?php checked( $card_style, $value ); ?> />
                        <div class="cpg-style-card cpg-style-<?php echo esc_attr( $value ); ?>">
                            <div class="cpg-style-preview"></div>
                            <div class="cpg-style-label"><?php echo esc_html( $option['label'] ); ?></div>
                            <div class="cpg-style-desc"><?php echo esc_html( $option['desc'] ); ?></div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
            <p class="cpg-field-desc"><?php esc_html_e( 'Choose visual style for photo cards.', 'contributor-photo-gallery' ); ?></p>
        </div>
        <?php
    }

    public function card_bg_color_field_callback() {
        $bg_color = isset( $this->options['card_bg_color'] ) ? $this->options['card_bg_color'] : '#ffffff';
        ?>
        <label class="cpg-label"><?php esc_html_e( 'Background Color', 'contributor-photo-gallery' ); ?></label>
        <div class="cpg-field-container">
            <div class="cpg-color-group">
                <input type="color" id="card_bg_color" name="cpg_options[card_bg_color]" value="<?php echo esc_attr( $bg_color ); ?>" class="cpg-color-picker" />
                <input type="text" id="card_bg_color_text" value="<?php echo esc_attr( $bg_color ); ?>" class="cpg-color-text" placeholder="#ffffff" />
                <button type="button" class="cpg-color-reset" data-default="#ffffff"><?php esc_html_e( 'Reset', 'contributor-photo-gallery' ); ?></button>
            </div>
            <p class="cpg-field-desc"><?php esc_html_e( 'Background color for photo cards.', 'contributor-photo-gallery' ); ?></p>
        </div>
        <?php
    }

    public function card_border_style_field_callback() {
        $border_style = isset( $this->options['card_border_style'] ) ? $this->options['card_border_style'] : 'solid';
        $border_width = isset( $this->options['card_border_width'] ) ? $this->options['card_border_width'] : 1;
        $border_color = isset( $this->options['card_border_color'] ) ? $this->options['card_border_color'] : '#e5e5e5';
        ?>
        <label class="cpg-label"><?php esc_html_e( 'Border Style', 'contributor-photo-gallery' ); ?></label>
        <div class="cpg-field-container">
            <div class="cpg-border-controls">
                <div class="cpg-border-row">
                    <label class="cpg-control-label"><?php esc_html_e( 'Style', 'contributor-photo-gallery' ); ?></label>
                    <select name="cpg_options[card_border_style]" class="cpg-select-field">
                        <option value="none" <?php selected( $border_style, 'none' ); ?>><?php esc_html_e( 'None', 'contributor-photo-gallery' ); ?></option>
                        <option value="solid" <?php selected( $border_style, 'solid' ); ?>><?php esc_html_e( 'Solid', 'contributor-photo-gallery' ); ?></option>
                        <option value="dashed" <?php selected( $border_style, 'dashed' ); ?>><?php esc_html_e( 'Dashed', 'contributor-photo-gallery' ); ?></option>
                        <option value="dotted" <?php selected( $border_style, 'dotted' ); ?>><?php esc_html_e( 'Dotted', 'contributor-photo-gallery' ); ?></option>
                    </select>
                </div>
                <div class="cpg-border-row">
                    <label class="cpg-control-label"><?php esc_html_e( 'Width', 'contributor-photo-gallery' ); ?></label>
                    <div class="cpg-number-input-group">
                        <input type="number" name="cpg_options[card_border_width]" min="0" max="10" value="<?php echo esc_attr( $border_width ); ?>" class="cpg-number-input" />
                        <span class="cpg-input-suffix">px</span>
                    </div>
                </div>
                <div class="cpg-border-row">
                    <label class="cpg-control-label"><?php esc_html_e( 'Color', 'contributor-photo-gallery' ); ?></label>
                    <input type="color" name="cpg_options[card_border_color]" value="<?php echo esc_attr( $border_color ); ?>" class="cpg-color-picker cpg-border-color-picker" />
                </div>
            </div>
            <p class="cpg-field-desc"><?php esc_html_e( 'Customize border appearance of cards.', 'contributor-photo-gallery' ); ?></p>
        </div>
        <?php
    }

    public function card_shadow_style_field_callback() {
        $shadow_style = isset( $this->options['card_shadow_style'] ) ? $this->options['card_shadow_style'] : 'subtle';
        ?>
        <label class="cpg-label"><?php esc_html_e( 'Drop Shadow', 'contributor-photo-gallery' ); ?></label>
        <div class="cpg-field-container">
            <div class="cpg-shadow-picker">
                <?php
                $shadow_options = array(
                    'none'   => array( 'label' => __( 'None', 'contributor-photo-gallery' ), 'desc' => __( 'No shadow', 'contributor-photo-gallery' ) ),
                    'subtle' => array( 'label' => __( 'Light', 'contributor-photo-gallery' ), 'desc' => __( 'Subtle depth', 'contributor-photo-gallery' ) ),
                    'medium' => array( 'label' => __( 'Medium', 'contributor-photo-gallery' ), 'desc' => __( 'Balanced shadow', 'contributor-photo-gallery' ) ),
                    'strong' => array( 'label' => __( 'Strong', 'contributor-photo-gallery' ), 'desc' => __( 'Deep shadow', 'contributor-photo-gallery' ) ),
                );

                foreach ( $shadow_options as $value => $option ): ?>
                    <label class="cpg-shadow-option cpg-shadow-<?php echo esc_attr( $value ); ?> <?php echo $shadow_style === $value ? 'selected' : ''; ?>">
                        <input type="radio" name="cpg_options[card_shadow_style]" value="<?php echo esc_attr( $value ); ?>" <?php checked( $shadow_style, $value ); ?> />
                        <div class="cpg-shadow-chip"></div>
                        <div class="cpg-shadow-info">
                            <span class="cpg-shadow-label"><?php echo esc_html( $option['label'] ); ?></span>
                            <span class="cpg-shadow-desc"><?php echo esc_html( $option['desc'] ); ?></span>
                        </div>
                    </label>
                <?php endforeach; ?>

                <div class="cpg-shadow-live-preview">
                    <div class="cpg-shadow-demo cpg-shadow-<?php echo esc_attr( $shadow_style ); ?>">
                        <div class="cpg-shadow-demo-content"></div>
                    </div>
                    <!-- removed stray Preview text as requested -->
                    <div class="wpcpg-shadow-preview-placeholder" style="display:none;"></div>
                </div>
            </div>
            <p class="cpg-field-desc"><?php esc_html_e( 'Add depth with drop shadows.', 'contributor-photo-gallery' ); ?></p>
        </div>
        <?php
    }

    public function show_captions_field_callback() {
        $show_captions = isset( $this->options['show_captions'] ) ? $this->options['show_captions'] : 1;
        ?>
        <label class="cpg-label"><?php esc_html_e( 'Photo Captions', 'contributor-photo-gallery' ); ?></label>
        <div class="cpg-field-container">
            <input type="hidden" name="cpg_options[show_captions]" value="0" />
            <label class="cpg-toggle-container">
                <input type="checkbox" id="show_captions" name="cpg_options[show_captions]" value="1" <?php checked( $show_captions, 1 ); ?> />
                <span class="cpg-toggle-slider"></span>
                <span class="cpg-toggle-label"><?php esc_html_e( 'Display captions on cards', 'contributor-photo-gallery' ); ?></span>
            </label>
            <p class="cpg-field-desc"><?php esc_html_e( 'Show or hide photo titles on cards.', 'contributor-photo-gallery' ); ?></p>
        </div>
        <?php
    }

    public function caption_text_color_field_callback() {
        $color = isset( $this->options['caption_text_color'] ) ? $this->options['caption_text_color'] : '#0f1724';
        ?>
        <label class="cpg-label"><?php esc_html_e( 'Caption Text Color', 'contributor-photo-gallery' ); ?></label>
        <div class="cpg-field-container">
            <div class="cpg-color-group">
                <input type="color" id="caption_text_color" name="cpg_options[caption_text_color]" value="<?php echo esc_attr( $color ); ?>" class="cpg-color-picker" />
                <input type="text" id="caption_text_color_text" value="<?php echo esc_attr( $color ); ?>" class="cpg-color-text" />
            </div>
            <p class="cpg-field-desc"><?php esc_html_e( 'Customize caption font color for better contrast or branding.', 'contributor-photo-gallery' ); ?></p>
        </div>
        <?php
    }

    public function cache_time_field_callback() {
        $cache_time = isset( $this->options['cache_time'] ) ? $this->options['cache_time'] : 3600;
        $options    = array(
            300   => __( '5 minutes', 'contributor-photo-gallery' ),
            900   => __( '15 minutes', 'contributor-photo-gallery' ),
            1800  => __( '30 minutes', 'contributor-photo-gallery' ),
            3600  => __( '1 hour (recommended)', 'contributor-photo-gallery' ),
            7200  => __( '2 hours', 'contributor-photo-gallery' ),
            21600 => __( '6 hours', 'contributor-photo-gallery' ),
            86400 => __( '24 hours', 'contributor-photo-gallery' ),
        );
        ?>
        <div class="cpg-field-container">
            <select id="cache_time" name="cpg_options[cache_time]" class="cpg-select-field">
                <?php foreach ( $options as $value => $label ): ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $cache_time, $value ); ?>><?php echo esc_html( $label ); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="cpg-field-desc"><?php esc_html_e( 'How long to store photo data for faster loading.', 'contributor-photo-gallery' ); ?></p>
        </div>
        <?php
    }

    public function new_tab_field_callback() {
        $checked = isset( $this->options['open_in_new_tab'] ) ? $this->options['open_in_new_tab'] : 1;
        ?>
        <div class="cpg-field-container">
            <input type="hidden" name="cpg_options[open_in_new_tab]" value="0" />
            <label class="cpg-toggle-container">
                <input type="checkbox" id="open_in_new_tab" name="cpg_options[open_in_new_tab]" value="1" <?php checked( $checked, 1 ); ?> />
                <span class="cpg-toggle-slider"></span>
                <span class="cpg-toggle-label"><?php esc_html_e( 'Open links in new tab', 'contributor-photo-gallery' ); ?></span>
            </label>
            <p class="cpg-field-desc"><?php esc_html_e( 'Keep visitors on your site when clicking photos.', 'contributor-photo-gallery' ); ?></p>
        </div>
        <?php
    }

    public function lazy_loading_field_callback() {
        $checked = isset( $this->options['enable_lazy_loading'] ) ? $this->options['enable_lazy_loading'] : 1;
        ?>
        <div class="cpg-field-container">
            <input type="hidden" name="cpg_options[enable_lazy_loading]" value="0" />
            <label class="cpg-toggle-container">
                <input type="checkbox" id="enable_lazy_loading" name="cpg_options[enable_lazy_loading]" value="1" <?php checked( $checked, 1 ); ?> />
                <span class="cpg-toggle-slider"></span>
                <span class="cpg-toggle-label"><?php esc_html_e( 'Enable performance optimization', 'contributor-photo-gallery' ); ?></span>
            </label>
            <p class="cpg-field-desc"><?php esc_html_e( 'Load images only when needed for faster pages.', 'contributor-photo-gallery' ); ?></p>
        </div>
        <?php
    }

    /* -------------------------
       Settings validation / sanitization
       ------------------------- */
    public function validate_options( $input ) {
        if ( ! is_array( $input ) ) {
            return cpg_get_default_options();
        }

        $validated = array();

        // Essential
        $validated['default_user_id']   = sanitize_text_field( $input['default_user_id'] ?? '' );
        $validated['default_per_page']  = max( 1, min( 50, absint( $input['default_per_page'] ?? 12 ) ) );
        $validated['default_columns']   = max( 1, min( 6, absint( $input['default_columns'] ?? 4 ) ) );

        // Styling
        $validated['card_style'] = in_array( $input['card_style'] ?? 'default', [ 'default', 'polaroid', 'circle', 'fixed' ], true ) ? sanitize_text_field( $input['card_style'] ) : 'default';
        $validated['card_bg_color'] = sanitize_hex_color( $input['card_bg_color'] ?? '#ffffff' ) ?: '#ffffff';
        $validated['card_border_style'] = in_array( $input['card_border_style'] ?? 'solid', [ 'none', 'solid', 'dashed', 'dotted' ], true ) ? sanitize_text_field( $input['card_border_style'] ) : 'solid';
        $validated['card_border_width'] = max( 0, min( 10, absint( $input['card_border_width'] ?? 1 ) ) );
        $validated['card_border_color'] = sanitize_hex_color( $input['card_border_color'] ?? '#e5e5e5' ) ?: '#e5e5e5';
        $validated['card_shadow_style'] = in_array( $input['card_shadow_style'] ?? 'subtle', [ 'none', 'subtle', 'medium', 'strong' ], true ) ? sanitize_text_field( $input['card_shadow_style'] ) : 'subtle';
        $validated['show_captions'] = ! empty( $input['show_captions'] ) ? 1 : 0;

        // New: caption color
        $validated['caption_text_color'] = sanitize_hex_color( $input['caption_text_color'] ?? '#0f1724' ) ?: '#0f1724';

        // Advanced
        $validated['cache_time'] = absint( $input['cache_time'] ?? 3600 );
        $validated['open_in_new_tab'] = ! empty( $input['open_in_new_tab'] ) ? 1 : 0;
        $validated['enable_lazy_loading'] = ! empty( $input['enable_lazy_loading'] ) ? 1 : 0;

        add_settings_error( 'cpg_options', 'settings_saved', __( 'Settings saved successfully!', 'contributor-photo-gallery' ), 'updated' );

        // update local copy so the page reflects changes immediately after save
        $this->options = $validated;

        return $validated;
    }

    /**
     * Render the settings page (loads template)
     */
    public function settings_page() {
        // Provide the notice flag to the template
        $notice_shown = get_option( 'cpg_new_shortcode_notice_shown', 0 );
        include CPG_PLUGIN_PATH . 'templates/admin/settings-page.php';
    }

    /**
     * AJAX: dismiss the one-time new-shortcode notice (settings-page notice)
     */
    public function ajax_dismiss_new_shortcode_notice() {
        check_ajax_referer( 'wpcpg_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Permission denied' ], 403 );
        }

        update_option( 'cpg_new_shortcode_notice_shown', 1 );
        wp_send_json_success( [ 'message' => 'Notice dismissed' ] );
    }

    /**
     * AJAX: dismiss the site-wide setup notice
     */
    public function ajax_dismiss_setup_notice() {
        check_ajax_referer( 'cpg_setup_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Permission denied' ], 403 );
        }

        // Persist dismissal globally (use update_user_meta if you prefer per-user)
        update_option( 'cpg_setup_notice_dismissed', 1 );
        wp_send_json_success( [ 'message' => 'Setup notice dismissed' ] );
    }

    /**
     * AJAX: dismiss the shortcode update notice
     */
    public function ajax_dismiss_shortcode_notice() {
        check_ajax_referer( 'wpcpg_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Permission denied' ], 403 );
        }

        update_option( 'cpg_shortcode_notice_dismissed', 1 );
        wp_send_json_success( [ 'message' => 'Shortcode notice dismissed' ] );
    }
}
