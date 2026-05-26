=== GeoScale – Programmatic SEO Engine ===
Contributors: romosamatya
Requires at least: 5.8
Tested up to: 7.0
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generate thousands of geo-targeted landing pages from a CSV upload — without creating physical posts in your WordPress database.

== Description ==

GeoScale is an advanced programmatic SEO engine designed for WordPress. Instead of creating thousands of physical posts (which slows down your database), GeoScale uses virtual routing to intercept URLs and render dynamic content on the fly.

Simply upload a CSV with your location data, configure a master template, and GeoScale will instantly generate thousands of perfectly optimized landing pages complete with dynamic JSON-LD Schema.

### Premium Features
* **Spintax Engine**: Generate unique content variations automatically.
* **Dynamic Schema Generation**: Output perfectly structured JSON-LD for LocalBusiness and Services.
* **Bulk Actions**: Batch process and map variables at an enterprise scale.

== Source Code ==

The full source code for this plugin, including all build tools and scripts, is publicly available at:
https://github.com/romosamatya/geoscale-seo-engine

The React-based admin UI is built using @wordpress/scripts. To regenerate the compiled assets, run `npm install` then `npm run build` from the plugin root.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/geoscale-programmatic-seo-engine` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Click on the "GeoScale" menu item in the WordPress admin sidebar.

== Frequently Asked Questions ==

= Does this create physical pages in WordPress? =
No. GeoScale intercepts URLs dynamically, rendering the page on the fly without writing thousands of posts to the `wp_posts` table.

== Changelog ==

= 1.0.0 =
* Initial release of GeoScale engine.
