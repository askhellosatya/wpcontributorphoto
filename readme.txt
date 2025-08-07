=== WordPress.org Contributor Photo Gallery ===
Contributors: satyamvishwakarma
Tags: wordpress.org, photo contributions, contributor gallery, photography showcase, portfolio
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Showcase your photo contributions to WordPress.org/photos directory in beautiful responsive grids on your website.

== Description ==

**Transform your WordPress.org photo contributions into stunning portfolio galleries with professional design.**

W.Org Photo Gallery helps photographers and WordPress community members showcase their photo contributions to the WordPress.org/photos directory on their own websites. Perfect for building professional portfolios, demonstrating community involvement, and getting recognition for your contributions.

= 🌟 Why Showcase Your Contributions? =

**Professional Benefits:**
* Build credibility with visible proof of WordPress expertise
* Enhance your portfolio with community contributions  
* Demonstrate commitment to open source and community
* Create SEO value with links back to WordPress.org
* Professional photography-focused design interface

**Community Benefits:**
* Inspire others to contribute photos to WordPress.org
* Get recognition for your valuable contributions
* Connect with other WordPress contributors
* Share your WordPress community journey visually

= 🚀 New in Version 2.0.0 =

**Major UI/UX Overhaul:**
* Professional photography-focused admin interface
* Fixed layout overflow issues for consistent experience
* Enhanced gallery preview with 2 high-quality images
* 30-character caption truncation for clean display

**Enhanced User Experience:**
* Copy-to-clipboard functionality for shortcode examples
* Real-time User ID validation with instant feedback
* Stylish Tools & Resources panel with gradients and animations
* 65%-35% admin layout for optimal space utilization

**Performance & Accessibility:**
* Improved mobile responsiveness
* Better color contrast and accessibility
* Performance optimizations and code cleanup
* Enhanced caching mechanisms

= 📖 Easy Usage Examples =

**Basic showcase** (uses your settings):
`[wporg_photos]`

**Portfolio showcase** (professional portfolios):
`[wporg_photos per_page="20" columns="4"]`

**About page integration** (personal branding):
`[wporg_photos per_page="12" columns="3"]`

**Blog post enhancement** (content creation):
`[wporg_photos per_page="6" columns="2"]`

**Sidebar widget** (ongoing showcase):
`[wporg_photos per_page="4" columns="1"]`

= ⚙️ Professional Configuration =

**Essential Settings:**
* WordPress.org User ID configuration with step-by-step guide
* Photos per gallery: 1-50 (recommended: 12 for portfolio, 20-24 for showcase)
* Grid layout: 1-6 columns with visual preview cards
* Real-time validation and helpful guidance

**Display & Performance:**
* Cache duration: 5 minutes to 24 hours (recommended: 1 hour)
* Link behavior: Open in new tab (recommended) or same tab
* Photo descriptions: Show/hide with 30-character truncation
* Lazy loading: Enable for better page performance

= 🏆 Key Features =

**Professional Design:**
* Clean, photography-focused interface respecting open-source values
* Responsive grid layouts that adapt to any design
* High-quality image preview with optimized loading
* Subtle hover effects for enhanced user experience

**Enhanced Admin Experience:**
* Copy-to-clipboard for quick shortcode access
* Interactive User ID help with step-by-step instructions
* Range sliders and toggle switches for better UX
* Live preview showing exactly how your gallery will look

**Performance Optimized:**
* Smart caching with configurable duration
* Lazy loading for faster page speeds
* Optimized API calls to WordPress.org
* Mobile-responsive design for all devices

**Developer Friendly:**
* Works with any page builder supporting shortcodes
* Clean CSS classes for easy customization
* Well-structured, maintainable codebase
* Accessibility features with proper contrast

= 🎯 Perfect For =

**WordPress Photographers**: Showcase contributions alongside commercial work in professional portfolios

**WordPress Professionals**: Demonstrate diverse community involvement beyond code contributions  

**Content Creators**: Add authentic visual elements to WordPress-related content

**Community Members**: Share your WordPress journey and build connections with other contributors

== Installation ==

1. Install through **Plugins → Add New** or upload manually
2. Activate the plugin
3. Go to **W.Org Photo Gallery** in your admin menu (main menu item for easy access)
4. Enter your WordPress.org User ID (see guide below)
5. Configure your preferred gallery layout and settings
6. Use `[wporg_photos]` shortcode anywhere on your site

== Frequently Asked Questions ==

= What is this plugin for? =

This plugin displays the photos you've contributed to wordpress.org/photos on your own WordPress website. It's perfect for showcasing your community involvement and building professional portfolios with beautiful, responsive galleries.

= How do I find my WordPress.org User ID? =

Your User ID is a number (like 21053005), not your username. Follow these steps:

1. Visit https://wordpress.org/photos/author/YOUR-USERNAME/ (replace YOUR-USERNAME)
2. Right-click and select "View page source" 
3. Press Ctrl+F (Cmd+F on Mac) and search for "users/"
4. Look for: wp-json/wp/v2/users/12345678
5. The number (12345678) is your User ID

The plugin includes a detailed step-by-step guide with interactive help.

= Can I customize the gallery appearance? =

Yes! Version 2.0.0 includes professional styling with multiple layout options:
- Choose 1-6 columns for different contexts
- Control photo descriptions and truncation
- Adjust cache duration for performance
- Customize link behavior (new tab/same tab)

All elements use `wporg-` CSS prefixes for easy custom styling.

= Will this help my SEO and professional credibility? =

Absolutely! The plugin creates proper links back to your WordPress.org contributions, improving your professional credibility and search engine presence. It's perfect for demonstrating your involvement in the WordPress community.

= Does it work with page builders? =

Yes! The shortcode works with all major page builders:
- Gutenberg blocks
- Elementor  
- Divi Builder
- Beaver Builder
- Any builder supporting shortcodes

= How does the caching work? =

The plugin includes smart caching (5 minutes to 24 hours) to ensure fast loading while keeping your gallery updated. You can manually clear the cache anytime from the admin panel.

== Screenshots ==

1. Professional admin interface with User ID configuration and real-time validation
2. Enhanced gallery preview showing 2 high-quality images with clean layout
3. Copy-to-clipboard shortcode examples for different use cases
4. Stylish Tools & Resources panel with gradients and animations
5. Responsive gallery display on frontend with hover effects
6. Mobile-optimized admin interface and gallery presentation

== Changelog ==

= 2.0.0 =
* **Major UI/UX Overhaul**: Complete redesign with photography-focused, professional interface
* **Enhanced Admin Layout**: 65%-35% layout split for optimal space utilization
* **Fixed Layout Issues**: Resolved overflow problems for consistent responsive experience
* **Improved Gallery Preview**: Limited to 2 high-quality images with 30-character caption truncation
* **Copy-to-Clipboard**: Quick access to shortcode examples for different use cases
* **Stylish Tools Panel**: Enhanced Resources section with gradients, animations, and icons
* **Better Form Controls**: Range sliders, toggle switches, and interactive elements
* **Real-time Validation**: Instant feedback for User ID configuration with helpful guidance
* **Updated Admin Menu**: Changed to "W.Org Photo Gallery" with clearer camera icon
* **Mobile Optimization**: Enhanced responsive design for all screen sizes
* **Accessibility Improvements**: Better color contrast and screen reader support
* **Performance Optimization**: Code cleanup and faster loading throughout
* **Enhanced Documentation**: Comprehensive help system and user guidance

= 1.1.0 =
* Added comprehensive admin settings for contributors
* Enhanced professional gallery layouts  
* Improved responsive design for portfolio use
* Better SEO optimization for contributor profiles
* Performance improvements for professional websites

= 1.0.0 =
* Initial release with basic photo gallery functionality
* WordPress.org User ID configuration and validation
* Responsive grid layouts and customization options
* Smart caching and performance features

== Upgrade Notice ==

= 2.0.0 =
Major design overhaul with professional photography-focused interface, enhanced admin experience, and improved mobile responsiveness. Includes copy-to-clipboard shortcuts, better gallery preview, and performance optimizations. Recommended for all users.

== Support ==

**Support & Development:**
* 🌐 Plugin Homepage: [satyamvishwakarma.com](https://satyamvishwakarma.com)
* 📖 Documentation: [GitHub Wiki](https://github.com/askhellosatya/wpphotogallery/wiki)
* 💬 Community Support: [GitHub Discussions](https://github.com/askhellosatya/wpphotogallery/discussions)
* 🐛 Report Issues: [GitHub Issues](https://github.com/askhellosatya/wpphotogallery/issues)
* 📸 WordPress.org Photos: [Contribute Photos](https://wordpress.org/photos/)

**Developer**: Satyam Vishwakarma - WordPress Consultant & Community Contributor
