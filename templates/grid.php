<?php
// expects $photos, $options (fallback cpg_get_plugin_options), and $atts passed from renderer
if ( ! isset( $options ) ) {
    $options = cpg_get_plugin_options();
}
if ( ! isset( $photos ) || ! is_array( $photos ) ) {
    $photos = array();
}

// Resolve columns as integer (prioritize shortcode attribute then saved option)
$columns = intval( $atts['columns'] ?? ( $options['default_columns'] ?? 4 ) );
$columns = max( 1, min( 6, $columns ) ); // ensure within 1..6

$card_style    = $options['card_style'] ?? 'default';
$show_captions = isset( $options['show_captions'] ) ? ! empty( $options['show_captions'] ) : true;
$caption_color = isset( $options['caption_text_color'] ) ? $options['caption_text_color'] : '#0f1724';

// Build CSS variables
$css_vars = array();

if ( ! empty( $options['card_bg_color'] ) && $options['card_bg_color'] !== '#ffffff' ) {
    $css_vars[] = '--cpg-card-bg: ' . $options['card_bg_color'];
}
if ( ! empty( $options['card_border_style'] ) && $options['card_border_style'] !== 'none' ) {
    $border_width = intval( $options['card_border_width'] ?? 1 );
    $border_color = $options['card_border_color'] ?? '#e5e5e5';
    $css_vars[]   = '--cpg-card-border: ' . $border_width . 'px ' . $options['card_border_style'] . ' ' . $border_color;
}

$shadow_styles = array(
    'none'   => array( 'base' => 'none', 'hover' => 'none' ),
    'subtle' => array( 'base' => '0 1px 4px rgba(0,0,0,0.12)', 'hover' => '0 4px 12px rgba(0,0,0,0.16)' ),
    'medium' => array( 'base' => '0 4px 14px rgba(0,0,0,0.16)', 'hover' => '0 8px 22px rgba(0,0,0,0.22)' ),
    'strong' => array( 'base' => '0 10px 28px rgba(0,0,0,0.22)', 'hover' => '0 14px 36px rgba(0,0,0,0.28)' ),
);

if ( ! empty( $options['card_shadow_style'] ) && isset( $shadow_styles[ $options['card_shadow_style'] ] ) ) {
    $css_vars[] = '--cpg-card-shadow: ' . $shadow_styles[ $options['card_shadow_style'] ]['base'];
    $css_vars[] = '--cpg-card-shadow-hover: ' . $shadow_styles[ $options['card_shadow_style'] ]['hover'];
}

if ( ! empty( $caption_color ) ) {
    $css_vars[] = '--cpg-caption-color: ' . $caption_color;
}

$style_value = ! empty( $css_vars ) ? implode( '; ', $css_vars ) : '';
$caption_class = ! $show_captions ? ' cpg-no-captions' : '';

// Debug hint (temporary): HTML comment with the columns value and data attribute to inspect quickly.
// Remove this line once confirmed working.
echo '<!-- cpg: columns=' . esc_attr( $columns ) . ' -->';

echo '<div class="cpg-gallery-grid columns-' . esc_attr( $columns ) . esc_attr( $caption_class ) . '" data-cpg-columns="' . esc_attr( $columns ) . '"' . ( $style_value ? ' style="' . esc_attr( $style_value ) . '"' : '' ) . '>';

foreach ( $photos as $photo ) {
    $image_url = '';
    $title     = '';
    $link      = $photo['link'] ?? '';

    if ( isset( $photo['_embedded']['wp:featuredmedia'][0]['media_details']['sizes']['large']['source_url'] ) ) {
        $image_url = $photo['_embedded']['wp:featuredmedia'][0]['media_details']['sizes']['large']['source_url'];
    }

    if ( isset( $photo['content']['rendered'] ) ) {
        $title = wp_strip_all_tags( $photo['content']['rendered'] );
        $title = mb_substr( $title, 0, 30 ) . ( mb_strlen( $title ) > 30 ? '...' : '' );
    }

    if ( $image_url ) {
        $target = ( $options['open_in_new_tab'] ?? true ) ? '_blank' : '';
        $rel    = ( $options['open_in_new_tab'] ?? true ) ? 'noopener' : '';

        echo '<div class="cpg-photo-card cpg-style-' . esc_attr( $card_style ) . '"' . ( $style_value ? ' style="' . esc_attr( $style_value ) . '"' : '' ) . '>';
        echo '<a href="' . esc_url( $link ) . '"' . ( $target ? ' target="' . esc_attr( $target ) . '"' : '' ) . ( $rel ? ' rel="' . esc_attr( $rel ) . '"' : '' ) . '>';
        echo '<div class="cpg-photo-image"><img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $title ) . '" loading="lazy"></div>';

        if ( $show_captions && ! empty( $title ) ) {
            echo '<div class="cpg-photo-content"><p style="color: var(--cpg-caption-color, ' . esc_attr( $caption_color ) . ');">' . esc_html( $title ) . '</p></div>';
        }

        echo '</a></div>';
    }
}

echo '</div>';
