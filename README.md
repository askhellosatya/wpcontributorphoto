# WP Contributor Photo Gallery

<center><img width="1425" height="732" alt="demo_page" src="https://github.com/user-attachments/assets/bbfc4e30-a2a6-490a-9892-c4a8f9006a49" /></center>

**Showcase your WordPress.org photo contributions in beautiful, responsive grids on your website.**

[![WordPress Plugin](https://img.shields.io/badge/WordPress-Plugin-blue.svg)](https://wordpress.org)
[![Version](https://img.shields.io/badge/Version-2.0.3-green.svg)](https://github.com/askhellosatya/wpcontributorphoto/releases)
[![License](https://img.shields.io/badge/License-GPL%20v2%2B-red.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

Transform your WordPress.org photo contributions into stunning portfolio galleries with just one shortcode. Perfect for photographers who contribute to the WordPress.org/photos directory and want to showcase their community involvement on their personal or professional websites.

- 🌐 **Repository**: [https://github.com/askhellosatya/wpcontributorphoto](https://github.com/askhellosatya/wpcontributorphoto)
- 📖 **Documentation**: [https://github.com/askhellosatya/wpcontributorphoto/wiki](https://github.com/askhellosatya/wpcontributorphoto/wiki)
- 🐛 **Issues**: [https://github.com/askhellosatya/wpcontributorphoto/issues](https://github.com/askhellosatya/wpcontributorphoto/issues)
- 💬 **Discussions**: [https://github.com/askhellosatya/wpcontributorphoto/discussions](https://github.com/askhellosatya/wpcontributorphoto/discussions)

## 🚀 Quick Start

### Step 1: Install & Activate
1. Go to **Plugins → Add New** in your WordPress admin
2. Search for **"WP Contributor Photo Gallery"**
3. Click **Install** → **Activate**

<center><img width="1240" height="75" alt="Plugin_page" src="https://github.com/user-attachments/assets/623bbecb-ad67-4c67-bf41-9056d8d1a222" /></center>


### Step 2: Configure Your Profile
1. Navigate to **WP Contributor Photo Gallery** in your admin menu
   
<center><img width="159" height="243" alt="Admin_dashboard_sidebar_menu" src="https://github.com/user-attachments/assets/a6394ae7-8c0d-4eaa-b029-1c2baf9c0c92" /></center>

3. Enter your **WordPress.org User ID** (not username - see guide below)

<center><img width="1274" height="748" alt="plugin_settings_page" src="https://github.com/user-attachments/assets/cfa8267f-d50a-45b8-a4a5-8842cf6d454a" /></center>


5. Choose your preferred settings:
   - **Photos per gallery**: 12 (portfolio) to 20-24 (showcase)
   - **Grid columns**: 3-4 for portfolios, 2 for compact layouts
   - **Cache duration**: 1 hour (recommended)
6. Click **Save Settings**

<center><img width="1255" height="585" alt="Display_performance_preview" src="https://github.com/user-attachments/assets/30acdc34-77ab-4011-8607-79a23852bbf7" /></center>


### Step 3: Display Your Gallery
Add this shortcode anywhere: `[wpcontrib_photos]`

## 📖 Usage Examples

<center><img width="307" height="673" alt="Usage_example_shortcodes" src="https://github.com/user-attachments/assets/515f45a8-fc16-4ce6-8da0-88ffeb3ca977" /></center>


| Use Case | Shortcode | Description |
|----------|-----------|-------------|
| 🏆 **Portfolio Showcase** | `[wpcontrib_photos per_page="20" columns="4"]` | Professional portfolios and showcases |
| 👤 **About Page** | `[wpcontrib_photos per_page="12" columns="3"]` | Personal branding sections |
| ✍️ **Blog Enhancement** | `[wpcontrib_photos per_page="6" columns="2"]` | Content creation and articles |
| 📱 **Sidebar Widget** | `[wpcontrib_photos per_page="4" columns="1"]` | Compact sidebar displays |
| ⚙️ **Default** | `[wpcontrib_photos]` | Uses your configured settings |


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

Access **WP Contributor Photo Gallery** in your WordPress admin for:

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

We welcome contributions! Please see our [contributing guidelines](https://github.com/askhellosatya/wpcontributorphoto/blob/main/CONTRIBUTING.md).

### **Ways to Contribute**
- 🐛 Report bugs and issues
- 💡 Suggest new features
- 📝 Improve documentation
- 🔧 Submit pull requests
- ⭐ Star the repository

## 📞 Support & Resources

- 📖 **Documentation**: [GitHub Wiki](https://github.com/askhellosatya/wpcontributorphoto/wiki)
- 💬 **Community Support**: [GitHub Discussions](https://github.com/askhellosatya/wpcontributorphoto/discussions)
- 🐛 **Report Issues**: [GitHub Issues](https://github.com/askhellosatya/wpcontributorphoto/issues)
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


