=== Contributor Photo Gallery ===
Contributors: hellosatya, bhargavbhandari90  
Tags: gallery, photography, portfolio, shortcode, responsive
Donate link: https://paypal.me/hellosatya
Requires at least: 5.8  
Tested up to: 6.8  
Stable tag: 2.5.0  
Requires PHP: 7.4  
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  

Showcase your WordPress.org/photos contributions in fast, responsive, SEO-friendly galleries with professional card styles and flexible layout options.  

== Description ==

Showcase WordPress.org/photos contributions in fast, responsive, SEO-friendly galleries with professional card styles and flexible layouts.

# Contributor Photo Gallery – Display, Customize & Share Your WordPress.org Photo Contributions  

Contributor Photo Gallery is a modern WordPress plugin that turns your WordPress.org/photos contributions into elegant, responsive galleries.  
Built for photographers, agencies, and community contributors, it makes your work shine while staying lightweight, accessible, and SEO-ready.  

Whether you’re building a portfolio, an about page, or a community profile, Contributor Photo Gallery helps you showcase your involvement beautifully — no coding required.  

**Fast & Easy**: Create responsive photo galleries with a single shortcode.  
**Multiple Styles**: Choose from Modern, Polaroid, Circle, or Fixed Height cards.  
**Fully Customizable**: Adjust borders, shadows, backgrounds, and caption colors.  
**Live Preview**: Style your gallery in the admin and see changes instantly.  
**SEO & Accessibility**: Clean semantic HTML, alt attributes, and WCAG-friendly contrast.  
**Performance-Minded**: Smart caching, lazy loading, and optimized API calls.  
**Backwards Compatible**: Includes `[cp_gallery]` (new) and `[wpcontrib_photos]` (legacy).  

**Perfect for:**  
- **Photographers** — build a professional portfolio using your WordPress.org photos.  
- **Agencies & Professionals** — highlight team contributions on your site.  
- **Community Members & Speakers** — add visual credibility to profiles and bios.  

Lightweight, privacy-friendly, and compatible with any WordPress theme, Contributor Photo Gallery gives you the tools to showcase your WordPress.org photos with performance and polish.  
  

## New in v2.5.0  
- Primary shortcode: `[cp_gallery]` (recommended).  
- Legacy shortcode `[wpcontrib_photos]` preserved for compatibility.  
- Caption text color option with live admin preview.  
- New gallery styles: Polaroid, Circle, Fixed Height.  
- Advanced card customization: borders, backgrounds, shadows.  
- Auto-refresh preview (removed manual refresh button).  
- Smooth settings migration to keep existing configurations.  
- Minimum WordPress version updated to 5.8.  

== Shortcodes ==

**Primary Shortcode:**  
`[cp_gallery]`  

Examples:  
- `[cp_gallery]` — uses saved settings.  
- `[cp_gallery per_page="12" columns="3"]`  
- `[cp_gallery per_page="20" columns="4" user_id="21053005"]`  

**Legacy Shortcode:**  
- `[wpcontrib_photos]` — fully supported, calls the same handler as `[cp_gallery]`.  

**Attributes:**  
- `per_page` — photos per page (1–50). Example: `per_page="12"`  
- `columns` — grid columns (1–6). Example: `columns="3"`  
- `user_id` — override saved WordPress.org numeric user ID. Example: `user_id="21053005"`  

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/contributor-photo-gallery/` or install via the Plugin Installer.  
2. Activate the plugin through the "Plugins" menu.  
3. Go to **Settings → Contributor Photo Gallery** and enter your WordPress.org numeric User ID.  
4. Adjust styling (card style, borders, shadow, caption color) and save.  
5. Add `[cp_gallery]` (or `[wpcontrib_photos]`) to any page, post, or widget.  

== Frequently Asked Questions ==

= How do I find my WordPress.org User ID? =  
1. Visit: `https://wordpress.org/photos/author/YOUR-USERNAME/`  
2. Right-click → "View Source."  
3. Search for `wp-json/wp/v2/users/`.  
4. The numeric ID following the endpoint is your User ID.  

= Will my settings be preserved when I update? =  
Yes. Settings are stored in a single option (`cpg_options`) and preserved during updates. v2.5.0 also migrates common legacy option keys.  

= Can I style the gallery with CSS? =  
Yes. The plugin outputs predictable CSS classes like:  
- `.cpg-gallery-grid`  
- `.cpg-photo-card`  
- `.cpg-photo-content`  

These can be targeted for custom styling in your theme.  

= Will this plugin slow down my site? =  
No. Contributor Photo Gallery is performance-optimized with:  
- Smart caching (configurable between 5 minutes–24 hours).  
- Lazy loading for images.  
- Efficient API usage and lightweight markup.  

== Screenshots ==

1. Admin settings page with live preview and styling options.  
2. Card styles: Modern, Polaroid, Circle, Fixed Height.  
3. Border and color customization controls.  
4. Shadow presets with visual selector.  
5. Caption text color option in action.  
6. Responsive gallery display (mobile and desktop).  

== Changelog ==

= 2.5.0 - 2025-08-16 =  
* New shortcode: `[cp_gallery]`.  
* Legacy shortcode `[wpcontrib_photos]` preserved.  
* Caption text color option with live preview.  
* New gallery styles: Polaroid, Circle, Fixed Height, Modern.  
* Advanced card customization (backgrounds, borders, shadows).  
* Admin fixes: border select overflow, polaroid badge, shadow mapping.  
* Auto-refresh preview; removed manual refresh button.  
* Safe settings migration to preserve prior values.  
* Minimum WordPress version bumped to 5.8.  

= 2.0.3 =  
* Security improvements and caching refinements.  

= 2.0.0 =  
* Major UI/UX overhaul, responsive grids, live preview, performance updates.  

= 1.0.0 =  
* Initial release with gallery fetch from WordPress.org/photos.  

== Upgrade Notice ==

= 2.5.0 =  
New `[cp_gallery]` shortcode with fresh styling and customization options. Legacy `[wpcontrib_photos]` still works — all your settings are safe.  

== Support ==

- Documentation: https://github.com/askhellosatya/contributor-photo-gallery/wiki  
- Issues: https://github.com/askhellosatya/contributor-photo-gallery/issues  
- Discussions: https://github.com/askhellosatya/contributor-photo-gallery/discussions  

For commercial support, contact: [Satyam Vishwakarma](https://satyamvishwakarma.com)  

== License ==  

This plugin is licensed under the GPL v2 or later. See the [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) for details.  
