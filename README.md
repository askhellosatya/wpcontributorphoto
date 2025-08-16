# Contributor Photo Gallery

[![WordPress Plugin](https://img.shields.io/badge/WordPress-Plugin-blue.svg)](https://wordpress.org)
[![Version](https://img.shields.io/badge/Version-2.5.0-green.svg)](https://github.com/askhellosatya/contributor-photo-gallery/releases)
[![License](https://img.shields.io/badge/License-GPL%20v2%2B-red.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

Contributors: askhellosatya  
Tags: gallery, photography, portfolio, wordpress, wordpress.org, contributor, photos, responsive, shortcode, image-gallery  
Requires at least: 5.8  
Tested up to: 6.8  
Stable tag: 2.5.0

Turn your WordPress.org/photos contributions into beautiful, responsive, and SEO-friendly portfolio galleries. Contributor Photo Gallery is built for photographers, agencies, and community contributors who want a polished gallery with full styling control and minimal setup.

---

## Quick overview

- Fetches photos you contributed to WordPress.org/photos and renders them on your site.
- Multiple gallery/card styles: Modern, Polaroid, Circle, Fixed Height.
- Fine-grained styling: background color, border style/width/color, shadow presets, and caption (title) text color.
- Live admin preview with auto-refresh — style and see changes instantly.
- New primary shortcode: `[cp_gallery]` (recommended). Legacy `[wpcontrib_photos]` still supported.
- Preserves existing settings on upgrade and includes migration for common legacy keys.
- Performance-minded: smart caching + lazy loading.

## Why use this plugin (SEO & benefits)

- Sends authoritative links back to WordPress.org/photos: good for provenance and credibility.
- Produces semantic, crawlable HTML (proper alt attributes and link markup) to help image search.
- Lightweight markup and lazy loading reduce page load times — better Core Web Vitals.
- Customizable caption color and card contrast to meet accessibility guidelines and brand needs.

---

## What's new in v2.5.0 (high level)

- Primary shortcode: `[cp_gallery]`  
- Legacy shortcode `[wpcontrib_photos]` preserved for compatibility.  
- Caption Text Color option — customize caption font color for contrast and branding.  
- Enhanced grid styles: Polaroid, Circle, Fixed Height, Modern.  
- Card customization: background, border (style/width/color), drop shadows, caption toggle.  
- Live admin preview auto-refresh; removed manual "Refresh Preview" button.  
- Robust settings migration - upgrades keep user settings intact.  
- Minimum WordPress requirement updated to 5.8.

---

## Installation

1. Upload the plugin to `/wp-content/plugins/contributor-photo-gallery/` or install via GitHub/ZIP.
2. Activate the plugin in the WordPress admin under Plugins → Installed Plugins.
3. Go to Settings → Contributor Photo Gallery.
4. Enter your WordPress.org numeric User ID (example: `21053005`) and save.
5. Customize styling (card style, borders, shadows, caption color), check the live preview, then use the shortcode.

---

## Shortcodes

- `[cp_gallery]` (primary, recommended)
  - Attributes:
    - `per_page` — photos per gallery page (1–50). Example: `per_page="12"`
    - `columns` — grid columns (1–6). Example: `columns="3"`
    - `user_id` — override saved WordPress.org numeric user ID. Example: `user_id="21053005"`
  - Examples:
    - `[cp_gallery]` — use saved settings
    - `[cp_gallery per_page="12" columns="3"]`
    - `[cp_gallery per_page="20" columns="4" user_id="21053005"]`

- `[wpcontrib_photos]` (legacy)
  - Fully supported to preserve galleries created with previous plugin versions. Internally routes to the same handler as `[cp_gallery]`.

---

## Admin / Settings (what you can control)

- Essential
  - WordPress.org numeric User ID
  - Photos per gallery (range slider 1–50)
  - Grid columns (1–6) with visual preview cards

- Card Styling & Appearance
  - Card style: Modern, Polaroid, Circle, Fixed Height
  - Background color (HEX)
  - Border style: none, solid, dashed, dotted
  - Border width (px)
  - Border color (HEX)
  - Drop shadow presets: None, Light, Medium, Strong
  - Photo captions: show/hide toggle
  - Caption text color (HEX) — NEW in 2.5.0

- Performance & Behavior
  - Cache duration: 5 min — 24 hours (configurable)
  - Link behavior: open in new tab (recommended)
  - Lazy loading: enable to improve page load speed

All settings are kept under a single option (`cpg_options`) so upgrades preserve your saved configuration. The plugin also checks for common legacy option keys and migrates them on activation if present.

---

## Frontend structure & CSS classes

The plugin outputs easy-to-target CSS classes so you can theme the gallery from your theme or custom CSS:

- `.cpg-gallery-grid` — container grid
- `.cpg-photo-card` — single photo card
- `.cpg-photo-image` — image wrapper
- `.cpg-photo-content` — caption container
- `.cpg-style-<name>` — style-specific classes (e.g., `.cpg-style-polaroid`)
- CSS variables supported for quick theme overrides:
  - `--cpg-card-bg`
  - `--cpg-card-border`
  - `--cpg-card-shadow`
  - `--cpg-caption-color`

Example: override caption color in your theme:
```css
.cpg-gallery-grid .cpg-photo-content p {
  color: #ffffff !important;
}
```

---

## Developer Notes

- Main shortcode handler: `CPG_Frontend::render_shortcode()` (called by global wrapper).
- Templates:
  - `templates/grid.php` — markup for gallery grid (used by the shortcode).
  - `templates/admin/settings-page.php` — admin page layout.
- AJAX endpoints:
  - `admin-ajax.php?action=cpg_refresh_preview` — admin preview refresh (secured with nonce).
  - `admin-ajax.php?action=wpcpg_clear_cache` — clear cached photo results.
- Options stored as: `get_option('cpg_options')` (array).
- Hooks/filters you can use (examples):
  - `apply_filters('cpg_api_photos', $photos, $user_id)` — filter photos returned from API.
  - `apply_filters('cpg_card_markup', $html, $photo, $options)` — alter card HTML before output.
  - `do_action('cpg_after_render', $options)` — run actions after rendering gallery.

If you want additional hooks added for deep customization, open an issue or PR and I can suggest precise hook points.

---

## Accessibility & SEO best practices

- Use descriptive titles on WordPress.org/photos for each image — these are used as captions/alt text.
- Enable captions selectively; captions provide context and can help with image search relevance.
- The plugin preserves `alt` attributes and outputs semantic anchor tags linking back to WordPress.org/photos.
- Caption color and card contrast are configurable for WCAG compliance — test contrast for readability.

---

## Changelog (high level)

### 2.5.0 — 2025-08-16
- Primary shortcode added: `[cp_gallery]`
- Legacy `[wpcontrib_photos]` retained for compatibility
- Caption text color option + live admin preview
- Grid styles: Modern, Polaroid, Circle, Fixed Height
- Card customization: background, border style/width/color, shadow presets
- Admin UX fixes: select overflow, polaroid badge, shadow mapping
- Auto-refresh live preview; removed manual refresh button
- Safe settings migration & preservation on upgrade
- Minimum WP version: 5.8

(See `readme.txt` for full backward changelog included in the plugin package.)

---

## Contributing & Support

- GitHub repository: https://github.com/askhellosatya/contributor-photo-gallery  
- Issues & bug reports: https://github.com/askhellosatya/contributor-photo-gallery/issues  
- Discussions: https://github.com/askhellosatya/contributor-photo-gallery/discussions

Maintainers:
- `hellosatya` (primary contributor)
- `askhellosatya` (repo maintainer)

If you need commercial customization or priority support, contact the author: https://satyamvishwakarma.com

---

## License

Contributor Photo Gallery is licensed under the GPL v2 or later — see `LICENSE` file.

---

## Final notes & best practices for plugin directory

- Use descriptive screenshots (PNG) in repo root named `screenshot-1.png`, `screenshot-2.png`, etc., to display in the WordPress.org plugin directory.
- Keep the `readme.txt` updated with the stable tag and tested WP versions.
- Include translation PO/MO files in `/languages` for i18n.
- Keep changelog and upgrade notices concise and helpful for users.
- Preserve existing option keys (we use `cpg_options`) to ensure seamless upgrades.