=== Plugin Name ===
Contributors: niallkennedy
Tags: angellist, startups
Requires at least: 3.5
Tested up to: 3.5.1
Stable tag: 1.3.3
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add AngelList company profiles to your posts.

== Description ==

Associate an individual post with one or more [AngelList](http://angel.co/) companies to automatically display company logo, summary, description, key people, and more after your post content.

The plugin generates HTML markup for posts with associated companies. Company profiles are cached on your server for fast access without remote requests to AngelList servers with each page load.

Includes [Schema.org markup](http://schema.org/) for rich company metadata and search engine friendliness.

== Installation ==

1. Upload to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Start a search from a new post or edit post page

== Changelog ==

= 1.3.3 =
* Ping AngelList when post status is public. Fixes an issue where pings sent for non-public stati

= 1.3.2 =
* fix uninstaller get_posts request

= 1.3.1 =

* only add AngelList content to content in the loop
* minor style change for people display

= 1.3 =
* Use wp-ajax for company selector
* New startup roles endpoint
* Require WordPress 3.5

= 1.2.1 =
* Fix issue with photos.angel.co hostname not resolving since AWS East outage

= 1.2 =

* Display up to three job listings for claimed companies
* Remove AngelList content from excerpt generators
* Uninstaller

= 1.1 =

* Display up to three people with confirmed involvement in the company. Preference given to founders.
* Defer image loading until page is visible and a visitor has scrolled to the AngelList content.
* Display startup market tag.
* Display startup headquarters location.
* Ping AngelList when a post mentioning a company is first published. Adds a press mention to the AngelList company page for supported publishers.

= 1.0 =
* Tag posts. Display company profiles.

== Upgrade Notice ==

= 1.3.3 =
Fix pings being sent to AngelList for non-public post stati

= 1.3.2 =
Fix uninstaller get_posts request

= 1.3.1 =
Only add AngelList content to content in the loop. Improve line spacing for key people display.

= 1.3 =
WP Ajax autocomplete. New startup roles endpoint. WP 3.5 minimum.

= 1.2.1 =

Fix image URIs.

= 1.2 =

Up to three jobs per company. Exclude from excerpts. Uninstaller.

= 1.1 =

Key people. Display market tag + location. Publicize new posts through AngelList. Lazy load images.

= 1.0 =
Initial release.

== Frequently Asked Questions ==

= How do I disable Schema.org markup from appearing on my page? =

[HTML5 microdata](http://www.whatwg.org/specs/web-apps/current-work/multipage/microdata.html) may cause undesired results for some document types and theme markup. Tap into the `angellist_schema_org` [filter](http://codex.wordpress.org/Function_Reference/add_filter "WordPress add_filter function") from your theme's `functions.php` file or one of your site's custom plugins to override the default `true` state and disable Schema.org and its microdata output.

= How do I change the target attribute generated by AngelList links? =

Links to AngelList pages open in a new window by default through a `_blank` [browsing context](http://www.whatwg.org/specs/web-apps/current-work/multipage/browsers.html#valid-browsing-context-name-or-keyword). XHTML themes may dislike the `target` attributes on anchor elements. Tap into the `angellist_browsing_context` [filter](http://codex.wordpress.org/Function_Reference/add_filter "WordPress add_filter function") from your theme's `functions.php` file or one of your site's custom plugins to override this default context with another valid context name. Return an empty string (`''`) to remove the attribute.

= I run a high-traffic site. I think I have been rate limited =

The [AngelList API](http://angel.co/api) allows up to 1000 requests per hour bucketed by IP. A single company may consume three API requests (company data + people data + jobs data) while generating the necessary markup, effectively limiting sites to 333 generated company profiles per hour.

If you run into rate limiting issues you may consider bumping your transient cache times for each company. You may also [contact the AngelList API team](mailto:api@angel.co) to raise the limits for your server IP addresses.

== Screenshots ==

1. Search AngelList by company name, add one or more companies, and reorder to add context to your posts.
2. Display one or more claimed or community-generated company profiles after your post content.