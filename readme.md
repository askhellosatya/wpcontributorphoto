# WordPress.org Contributor Photo Gallery

**Showcase your WordPress.org photo contributions in beautiful, responsive grids on your website.**

[![WordPress Plugin](https://img.shields.io/badge/WordPress-Plugin-blue.svg)](https://wordpress.org)
[![Version](https://img.shields.io/badge/Version-2.0.0-green.svg)](https://github.com/askhellosatya/wpphotogallery/releases)
[![License](https://img.shields.io/badge/License-GPL%20v2%2B-red.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

Transform your WordPress.org photo contributions into stunning portfolio galleries with just one shortcode. Perfect for photographers who contribute to the WordPress.org/photos directory and want to showcase their community involvement on their personal or professional websites.

- 🌐 **Repository**: [https://github.com/askhellosatya/wpphotogallery](https://github.com/askhellosatya/wpphotogallery)
- 📖 **Documentation**: [https://github.com/askhellosatya/wpphotogallery/wiki](https://github.com/askhellosatya/wpphotogallery/wiki)
- 🐛 **Issues**: [https://github.com/askhellosatya/wpphotogallery/issues](https://github.com/askhellosatya/wpphotogallery/issues)
- 💬 **Discussions**: [https://github.com/askhellosatya/wpphotogallery/discussions](https://github.com/askhellosatya/wpphotogallery/discussions)

## 🌟 Key Features (v2.0.0)

### **Professional Gallery Display**
- **Responsive Grid Layouts**: 1-6 column options that adapt perfectly to any design
- **High-Quality Preview**: Smart image loading with optimized thumbnails
- **Clean Typography**: 30-character caption truncation for consistent display
- **Hover Effects**: Subtle animations that enhance user experience

### **Enhanced Admin Experience**
- **Professional Interface**: Photography-focused design respecting open-source values
- **Real-time Validation**: Instant feedback for User ID configuration
- **Copy-to-Clipboard**: Quick access to shortcode examples
- **65%-35% Layout**: Optimal space utilization for settings and resources

### **Performance & Accessibility**
- **Smart Caching**: Configurable cache duration (5 minutes to 24 hours)
- **Lazy Loading**: Images load only when needed for better performance
- **Mobile Optimized**: Perfect display on all devices with responsive breakpoints
- **SEO Friendly**: Proper linking back to your WordPress.org contributions

### **Developer-Friendly**
- **Easy Integration**: Works with any page builder supporting shortcodes
- **Clean Code**: Well-structured, maintainable codebase
- **Accessibility**: High contrast and screen reader friendly
- **Customizable**: CSS classes for easy styling customization

## 🚀 Quick Start

### Step 1: Install & Activate
1. Go to **Plugins → Add New** in your WordPress admin
2. Search for **"W.Org Photo Gallery"**
3. Click **Install** → **Activate**

### Step 2: Configure Your Profile
1. Navigate to **W.Org Photo Gallery** in your admin menu
2. Enter your **WordPress.org User ID** (not username - see guide below)
3. Choose your preferred settings:
   - **Photos per gallery**: 12 (portfolio) to 20-24 (showcase)
   - **Grid columns**: 3-4 for portfolios, 2 for compact layouts
   - **Cache duration**: 1 hour (recommended)
4. Click **Save Settings**

### Step 3: Display Your Gallery
Add this shortcode anywhere: `[wporg_photos]`

## 🔍 Finding Your WordPress.org User ID

**Important:** This plugin requires your **User ID** (a number like `21053005`), not your username.

### Quick Method:
1. **Visit your author page**: `https://wordpress.org/photos/author/YOUR-USERNAME/`
2. **View page source**: Right-click → "View page source" or press `Ctrl+U`
3. **Search for User ID**: Press `Ctrl+F`, search for `users/`
4. **Find the number**: Look for `wp-json/wp/v2/users/21053005` - the number is your User ID

### Example:
- **Username**: `hellosatya`
- **Author page**: `https://wordpress.org/photos/author/hellosatya/`
- **User ID found**: `21053005`
- **Enter in plugin**: `21053005`

### ⚠️ Common Mistakes:
- ❌ Don't use your username (`hellosatya`)
- ❌ Don't use your display name
- ✅ Use only the numeric ID (`21053005`)

## 📖 Usage Examples

### **Portfolio Showcase** (Professional)
[wporg_photos per_page="20" columns="4"]
Perfect for portfolio pages and professional showcases.

### **About Page Integration** (Personal branding)
[wporg_photos per_page="12" columns="3"]

Great for about pages and personal branding sections.

### **Blog Post Enhancement** (Content creation)
[wporg_photos per_page="6" columns="2"]

Ideal for enhancing blog posts and articles.

### **Sidebar Widget** (Ongoing showcase)
[wporg_photos per_page="4" columns="1"]

Perfect for sidebar widgets and compact displays.

### **Default Settings**
[wporg_photos]


Uses your configured default settings.

## 🎯 Perfect For

### **WordPress Photographers**
- **Portfolio Websites**: Showcase WordPress.org contributions alongside commercial work
- **Professional Profiles**: Demonstrate community involvement to potential clients
- **Photography Blogs**: Add authentic WordPress-related visual content

### **WordPress Professionals**
- **Developer Portfolios**: Show diverse contributions beyond code
- **Agency Websites**: Highlight team members' community involvement
- **Consultant Profiles**: Build trust through visible community participation

### **Content Creators & Community Members**
- **WordPress Blogs**: Add visual elements to WordPress-related posts
- **Personal Websites**: Share your WordPress journey visually
- **Speaker Profiles**: Show community involvement for WordCamp applications

## ⚙️ Configuration Options

Access **W.Org Photo Gallery** in your WordPress admin for:

### **Essential Configuration**
- **WordPress.org User ID**: Your unique contributor identifier
- **Photos Per Gallery**: 1-50 photos (recommended: 12-24)
- **Grid Layout**: 1-6 columns with visual preview

### **Display & Performance**
- **Cache Duration**: 5 minutes to 24 hours optimization
- **Link Behavior**: Open in new tab (recommended) or same tab
- **Photo Descriptions**: Show/hide captions with 30-character truncation
- **Lazy Loading**: Enable for better page performance

## 📱 Responsive Design

Your galleries automatically adapt:
- **📱 Mobile**: Single column for touch-friendly browsing
- **📟 Tablet**: 2 columns for balanced presentation
- **💻 Desktop**: Full grid layout for maximum impact
- **🖥️ Large Displays**: Optimal spacing for professional presentation

## 🛠️ Technical Details

### **System Requirements**
- WordPress 5.0 or higher
- PHP 7.4 or higher
- Modern browser support

### **Performance Features**
- Smart caching with configurable duration
- Lazy loading for images
- Optimized API calls to WordPress.org
- Responsive image loading

### **Integration Support**
- All page builders supporting shortcodes
- Widget areas and sidebars
- Gutenberg blocks
- Custom post types and templates

## 📋 Changelog

### **Version 2.0.0** (Latest)
- **🎨 Major UI/UX Overhaul**: Professional photography-focused design
- **📐 Layout Improvements**: Fixed overflow issues, 65%-35% admin layout
- **🖼️ Enhanced Gallery Preview**: Limited to 2 high-quality images with 30-character captions
- **✨ Stylish Tools Panel**: Gradient effects, animations, and resource icons
- **📋 Copy-to-Clipboard**: Quick access to shortcode examples
- **🎛️ Better Controls**: Range sliders, toggle switches, real-time validation
- **📱 Mobile Optimized**: Enhanced responsive experience
- **♿ Accessibility**: Improved contrast and screen reader support
- **⚡ Performance**: Code optimization and faster loading

### **Version 1.0.0**
- Initial release with basic photo gallery functionality
- WordPress.org User ID configuration and validation
- Responsive grid layouts and customization options
- Smart caching and performance features

## 🤝 Contributing

We welcome contributions! Please see our [contributing guidelines](https://github.com/askhellosatya/wpphotogallery/blob/main/CONTRIBUTING.md).

### **Ways to Contribute**
- 🐛 Report bugs and issues
- 💡 Suggest new features
- 📝 Improve documentation
- 🔧 Submit pull requests
- ⭐ Star the repository

## 📞 Support & Resources

- 🌐 **Plugin Homepage**: [satyamvishwakarma.com](https://satyamvishwakarma.com)
- 📖 **Documentation**: [GitHub Wiki](https://github.com/askhellosatya/wpphotogallery/wiki)
- 💬 **Community Support**: [GitHub Discussions](https://github.com/askhellosatya/wpphotogallery/discussions)
- 🐛 **Report Issues**: [GitHub Issues](https://github.com/askhellosatya/wpphotogallery/issues)
- 📸 **WordPress.org Photos**: [Contribute Photos](https://wordpress.org/photos/)

## 💝 Show Your Appreciation

If this plugin helps showcase your WordPress contributions:
- ⭐ **Star us on GitHub** - Help other contributors discover it
- 📝 **Share Your Gallery** - Show off your contribution showcase
- 🤝 **Contribute More Photos** - Keep building the WordPress.org photo library
- ☕ **[Support Development](https://paypal.me/hellosatya/)** - Help maintain this tool

## 📄 License

This plugin is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

---

**Developed with ❤️ by [Satyam Vishwakarma](https://satyamvishwakarma.com) for the WordPress Community**

**Ready to showcase your WordPress.org photo contributions? Install now and build your contributor portfolio! 🚀**


