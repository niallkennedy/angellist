=== Plugin Name ===
Contributors: niallkennedy
Tags: angellist, startups
Requires at least: 3.3
Tested up to: 3.4
Stable tag: 1.1
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add AngelList company profiles to your posts.

== Description ==

Associate an individual post with one or more [AngelList](http://angel.co/) companies to automatically display company logo, summary, description, and more after your post content.

The plugin generates HTML markup for posts with associated companies. Company profiles are cached on your server for fast access without remote requests to AngelList servers with each page load.

Includes [Schema.org markup](http://schema.org/) for rich company metadata and search engine friendliness.

== Installation ==

1. Upload to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Start a search from a new post or edit post page

== Changelog ==

= 1.1 =

* Display up to three people with confirmed involvement in the company. Preference given to founders.
* Defer image loading until page is visible and a visitor has scrolled to the AngelList content.
* Display startup market tag.
* Display startup headquarters location.
* Ping AngelList when a post mentioning a company is first published. Adds a press mention to the AngelList company page for supported publishers.

= 1.0 =
* Tag posts. Display company profiles.

== Upgrade Notice ==

= 1.1 =

Key people. Display market tag + location. Publicize new posts through AngelList. Lazy load images.

= 1.0 =
Initial release.

== Frequently Asked Questions ==

= You are missing a few features from the JavaScript widget =

The JavaScript widget includes one company market, one location, and people associated with the company. This additional data may be added in future plugin versions.

== Screenshots ==

1. Search AngelList by company name, add one or more companies, and reorder to add context to your posts.
2. Display one or more claimed or community-generated company profiles after your post content.