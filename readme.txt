=== Contributor Photo Gallery ===
Contributors: hellosatya
Tags: gallery, photography, portfolio, shortcode, responsive
Requires at least: 5.8
Tested up to: 6.8
Stable tag: 2.5.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 7.4

Showcase WordPress.org/photos contributions in fast, responsive, SEO-friendly galleries with professional card styles and flexible layout options.

== Description ==

Contributor Photo Gallery fetches photos you contributed to WordPress.org/photos and displays them as elegant, responsive galleries on your website. Built for photographers, agency portfolios, and community contributors, the plugin emphasizes performance, accessibility, and visual polish.

Key benefits:
- Turn your WordPress.org/photos contributions into portfolio galleries with a single shortcode.
- Multiple card styles: Modern, Polaroid, Circle, Fixed Height — each optimized for visual presentation.
- Fine-grained styling: background, border style/width/color, drop shadows, and caption (title) text color.
- Live admin preview with auto-refresh so you can style confidently without guesswork.
- Backwards compatibility: legacy shortcode support and settings migration ensure smooth upgrades.
- Performance-minded: smart caching, lazy loading, and optimized API usage.

New in v2.5.0:
* Primary shortcode: [cp_gallery]
* Legacy shortcode [wpcontrib_photos] preserved for backward compatibility
* Caption text color option and real-time admin preview support
* Enhanced grid styles (Polaroid, Circle, Fixed Height) and card customization
* Admin UI fixes: border select overflow, polaroid badge, shadow mapping
* Settings migration and preservation on upgrade
* Minimum WordPress version updated to 5.8

== Shortcodes ==

* [cp_gallery] — primary shortcode (recommended)
  - Examples:
    * `[cp_gallery]` — use saved settings
    * `[cp_gallery per_page="12" columns="3"]`
    * `[cp_gallery per_page="20" columns="4" user_id="21053005"]`

* [wpcontrib_photos] — legacy shortcode kept for compatibility (calls same handler as [cp_gallery])

Shortcode attributes:
- per_page — photos per gallery page (1-50). Example: per_page="12"
- columns — grid columns (1-6). Example: columns="3"
- user_id — override saved WordPress.org numeric user id. Example: user_id="21053005"

== Installation ==

1. Upload the folder to `/wp-content/plugins/contributor-photo-gallery/` or install via the plugin installer.
2. Activate the plugin via the 'Plugins' menu.
3. Visit Settings → Contributor Photo Gallery and enter your WordPress.org numeric User ID.
4. Configure styling options (card style, borders, shadow, caption color) and save.
5. Insert `[cp_gallery]` (or the legacy `[wpcontrib_photos]`) in any page, post, or widget.

== Frequently Asked Questions ==

= How do I find my WordPress.org User ID? =
1. Go to: `https://wordpress.org/photos/author/YOUR-USERNAME/`
2. View page source (right-click → View Source)
3. Search for `wp-json/wp/v2/users/`
4. The numeric ID following the endpoint is your User ID (e.g. `12345678`)

= Are my settings preserved when I update the plugin? =
Yes. Settings are stored under a single option (cpg_options) and preserved on update. v2.5.0 also performs safe migration checks for common legacy option keys to retain older installs' values.

= Can I style output with CSS? =
Yes. The plugin uses predictable class names (cpg-gallery-grid, cpg-photo-card, cpg-photo-image, cpg-photo-content) to allow theme-level CSS overrides and easy customization.

= Will this plugin affect performance? =
No — the plugin is optimized for performance:
- Smart caching configurable between 5 minutes and 24 hours
- Lazy loading for frontend images
- Efficient API usage and lightweight markup

== Screenshots ==

1. Admin settings page with live preview and styling controls
2. Card styles: Modern, Polaroid, Circle, Fixed Height
3. Border controls and color picker
4. Shadow presets with visual chips
5. Caption text color control and frontend result
6. Mobile and desktop responsive gallery

== Changelog ==

= 2.5.0 - 2025-08-16 =
* New primary shortcode: [cp_gallery]
* Legacy shortcode [wpcontrib_photos] preserved for backward compatibility
* Added caption text color option with live admin preview
* Enhanced grid styles: Polaroid, Circle, Fixed Height, Modern
* Advanced card customization: background, border style/width/color
* Drop shadow presets and visual picker
* Admin fixes: select overflow, polaroid badge conflict, shadow mapping
* Auto-refresh live preview (removed Refresh Preview button)
* Robust settings migration to preserve prior configurations on upgrade
* Minimum WordPress version updated to 5.8

= 2.0.3 =
* Security hardening, cache improvements, and backward compatibility refinements

= 2.0.0 =
* Major UI/UX overhaul, responsive grid updates, live admin preview, performance improvements

= 1.0.0 =
* Initial release: fetches WordPress.org/photos contributions and renders responsive galleries

== Upgrade Notice ==

= 2.5.0 =
This release includes a new primary shortcode `[cp_gallery]`. Existing usage of `[wpcontrib_photos]` will continue to work. All settings are preserved during upgrade; no reconfiguration required.

== Support ==

- Documentation: https://github.com/askhellosatya/contributor-photo-gallery/wiki
- Issues: https://github.com/askhellosatya/contributor-photo-gallery/issues
- Discussions: https://github.com/askhellosatya/contributor-photo-gallery/discussions

For commercial support, contact: https://satyamvishwakarma.com

== License ==

This plugin is released under the GPLv2 or later.
