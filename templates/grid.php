<?php
// expects $photos, $options (fallback cpg_get_plugin_options), and $atts passed from renderer
if (!isset($options)) {
    $options = cpg_get_plugin_options();
}
if (!isset($photos) || !is_array($photos)) {
    $photos = array();
}

// Resolve columns as integer (prioritize shortcode attribute then saved option)
$columns = intval($atts['columns'] ?? ($options['default_columns'] ?? 4));
$columns = max(1, min(6, $columns)); // ensure within 1..6

$card_style = $options['card_style'] ?? 'default';
$show_captions = isset($options['show_captions']) ? !empty($options['show_captions']) : true;
$caption_color = isset($options['caption_text_color']) ? $options['caption_text_color'] : '#0f1724';

$css_vars = [];

if (!empty($options['card_bg_color']) && strtolower($options['card_bg_color']) !== '#ffffff') {
    $css_vars['--cpg-card-bg'] = sanitize_hex_color($options['card_bg_color']);
}

if (!empty($options['card_border_style']) && $options['card_border_style'] !== 'none') {
    $border_width = max(0, intval($options['card_border_width'] ?? 1));
    $border_color = !empty($options['card_border_color']) 
        ? sanitize_hex_color($options['card_border_color']) 
        : '#e5e5e5';
    $css_vars['--cpg-card-border'] = "{$border_width}px {$options['card_border_style']} {$border_color}";
}

$shadow_styles = [
    'none'   => ['base' => 'none', 'hover' => 'none'],
    'subtle' => ['base' => '0 1px 4px rgba(0,0,0,0.12)', 'hover' => '0 4px 12px rgba(0,0,0,0.16)'],
    'medium' => ['base' => '0 4px 14px rgba(0,0,0,0.16)', 'hover' => '0 8px 22px rgba(0,0,0,0.22)'],
    'strong' => ['base' => '0 10px 28px rgba(0,0,0,0.22)', 'hover' => '0 14px 36px rgba(0,0,0,0.28)'],
];

$shadow_key = strtolower($options['card_shadow_style'] ?? '');
if (isset($shadow_styles[$shadow_key])) {
    $css_vars['--cpg-card-shadow'] = $shadow_styles[$shadow_key]['base'];
    $css_vars['--cpg-card-shadow-hover'] = $shadow_styles[$shadow_key]['hover'];
}

if (!empty($caption_color)) {
    $css_vars['--cpg-caption-color'] = sanitize_hex_color($caption_color);
}

// Build inline style
$style_attr = '';
if ($css_vars) {
    $pairs = [];
    foreach ($css_vars as $key => $value) {
        $pairs[] = $key . ': ' . esc_attr($value);
    }   
    $style_attr = ' style="' . implode('; ', $pairs) . '"';
}

$caption_class = !$show_captions ? ' cpg-no-captions' : '';

// Debug hint (temporary): HTML comment with the columns value and data attribute to inspect quickly.
// Remove this line once confirmed working.
echo '<!-- cpg: columns=' . esc_attr($columns) . ' -->';
echo '<div class="cpg-gallery-grid columns-' . esc_attr($columns) . ' ' . esc_attr($caption_class) . '" data-cpg-columns="' . esc_attr($columns) . '"' . $style_attr . '>';

foreach ($photos as $photo) {
    $image_url = '';
    $title = '';
    $link = $photo['link'] ?? '';
    
    $width = '';
    $height = '';
    $srcset = '';
    $sizes  = '(max-width: 640px) 100vw, 640px';

    // Try to pull image + sizes from _embedded
    if (!empty($photo['_embedded']['wp:featuredmedia'][0]['media_details'])) {
        $media = $photo['_embedded']['wp:featuredmedia'][0]['media_details'];

        // Prefer 'large', fall back to 'medium_large' or 'full'
        $preferred_keys = ['large', 'medium_large', 'full', 'medium', 'thumbnail'];
        foreach ($preferred_keys as $k) {
            if (!empty($media['sizes'][$k]['source_url'])) {
                $image_url = $media['sizes'][$k]['source_url'];
                $width  = $media['sizes'][$k]['width']  ?? '';
                $height = $media['sizes'][$k]['height'] ?? '';
                break;
            }
        }

        // Build srcset from all available sizes
        if (!empty($media['sizes']) && is_array($media['sizes'])) {
            $parts = [];
            foreach ($media['sizes'] as $size_key => $size_info) {
                if (!empty($size_info['source_url']) && !empty($size_info['width'])) {
                    $w = intval($size_info['width']);
                    $parts[] = esc_url($size_info['source_url']) . ' ' . $w . 'w';
                }
            }
            if ($parts) {
                // sort by width ascending for neatness
                usort($parts, function($a, $b) {
                    return intval(preg_replace('/\D/', '', $a)) <=> intval(preg_replace('/\D/', '', $b));
                });
                $srcset = implode(', ', $parts);
            }
        }
    }

    if (isset($photo['content']['rendered'])) {
        $title = wp_strip_all_tags($photo['content']['rendered']);
        $title = mb_substr($title, 0, 30) . (mb_strlen($title) > 30 ? '...' : '');
    }

    if ($image_url) {
        $target = ($options['open_in_new_tab'] ?? true) ? '_blank' : '';
        $rel = ($options['open_in_new_tab'] ?? true) ? 'noopener' : '';

        echo '<div class="cpg-photo-card cpg-style-' . esc_attr($card_style) . '">';
        echo '<a href="' . esc_url($link) . '"' . ($target ? ' target="' . esc_attr($target) . '"' : '') . ($rel ? ' rel="' . esc_attr($rel) . '"' : '') . '>';
        echo '<div class="cpg-photo-image">';
        echo '<img'
            . ' src="' . esc_url($image_url) . '"'
            . ($title ? ' alt="' . esc_attr($title) . '"' : ' alt=""')
            . ' loading="lazy" decoding="async"'
            . ($width  ? ' width="' . intval($width) . '"'   : '')
            . ($height ? ' height="' . intval($height) . '"' : '')
            . ($srcset ? ' srcset="' . esc_attr($srcset) . '"' : '')
            . ' sizes="' . esc_attr($sizes) . '"'
            . ' />';
        echo '</div>';

        if ($show_captions && !empty($title)) {
            echo '<div class="cpg-photo-content"><p style="color: var(--cpg-caption-color, ' . esc_attr($caption_color) . ');">' . esc_html($title) . '</p></div>';
        }

        echo '</a></div>';
    }
}

echo '</div>';
?>
