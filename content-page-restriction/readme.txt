=== Content/Page Restriction ===
Contributors: @aakashsky
Tags: content restriction, access control, user roles, content protection, security
Requires at least: 6.1
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

== Short Description ==
Restrict access to pages and content based on user roles with an easy-to-use plugin.

== Description ==
Content/Page Restriction is a simple yet powerful WordPress plugin that allows administrators to control access to pages and specific content based on user roles. With an easy-to-use interface, you can restrict entire pages or sections of content inside posts using a shortcode.

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/content-page-restriction/` directory.
2. Activate the plugin through the ‘Plugins’ screen in WordPress.
3. Configure settings via **Content Restrict** in the admin menu.
4. Use the `[restrict_content]` shortcode to protect content.

== Frequently Asked Questions ==
= How do I restrict a page? =
Edit the page, go to the **Page Permissions** meta box, and select user roles allowed to access it.

= Can I restrict only a section of a post? =
Yes! Use the shortcode `[restrict_content]Your Protected Content[/restrict_content]`.

= What happens if a user doesn’t have access? =
You can show a custom error message instead of redirecting them.

== Changelog ==
= 1.0 =
* Initial release.

== Upgrade Notice ==
= 1.0 =
First stable release. No upgrades required.