=== Product Categories/Tags Bottom Description for WooCommerce ===
Contributors: dieguraa
Tags: seo, ux, woocommerce, content
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 5.6
Stable tag: 3.5.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Add a custom content field to WooCommerce product categories, tags, attributes, and brands. Improve SEO & UX.

== Description ==

**This plugin will add a new content field to your WooCommerce product categories, tags, attributes, and brands that will be displayed right after your list of products in the product category/tag/attribute/brand page. You can also change where the description is displayed based on your needs. There's also an option to hide this new description and show it anywhere you want through a shortcode. In addition, the plugin includes global visibility settings so you can prevent the bottom description from loading in specific WooCommerce taxonomies from the frontend.**

This allows you to split your product categories/tags/attributes/brands content between the top and bottom parts of the page. You can use this bottom field to add additional content to your product category/tag/attribute/brand to improve your SEO, while keeping the products visible in the top part and improving the UX.

https://www.youtube.com/embed/CfLNduZflmA

Once the plugin is enabled, a new content field will appear in your product categories/tags/attributes ready to use.

**Shortcodes**
- **[woo-bottom-description]**: displays the bottom description of the current product category/tag/attribute.
- **[woo-bottom-description category_slug="my-category"]**: displays the bottom description of the product category specified by the "category_slug" parameter (replace "my-category" with the appropriate slug).

**Plugin features**
- Adds a new WYSIWYG metabox to your WooCommerce product categories, tags, and attributes.
- Display the content in the bottom section of your product archive pages (after the product list).
- Option to choose where the bottom description is displayed using standard WooCommerce hooks.
- Option to hide the description from the archive page and use shortcodes instead.
- Compatible with product categories, product tags, and product attributes.
- Great for adding additional SEO content without affecting the main product list visibility.
- Simple yet powerful plugin with no performance impact.
- Compatible with PHP 8.

**New in latest versions**
- Includes a dedicated settings page under the WooCommerce menu.
- Customize the styling of the bottom description without coding:
  - Control margin and padding.
  - Set a max-width.
  - Choose background color.
  - Set border thickness and color.
  - Apply border-radius.
- Added compatibility with WooCommerce Brands.
- Added global visibility controls by taxonomy.
- Improved frontend conditional loading to avoid rendering hidden descriptions.
- All styles are applied directly to the bottom description block.
