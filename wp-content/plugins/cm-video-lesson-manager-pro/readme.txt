=== Plugin Name ===
Name: CM Video Lesson Manager Pro
Contributors: CreativeMindsSolutions
Donate link: https://plugins.cminds.com/
Tags: video,vimeo,e-learning,lessons,tutorial,wordpress video,player,video player,shortcode,video gallery,gallery,elearning, video teaching, teaching,online,embed video,videos,video plugin,vimeo player,vimeo shortcode,web videos,wordpress vimeo,course,video course,plugin,post,jquery,pages,pages,posts,widget,widgets,wordpress
Requires at least: 4.0
Tested up to: 4.7.5
Stable tag: 2.1.7

Enables to manage video courses and lessons while allowing users and admin to track progress, leave notes and mark favorites.

== Description ==

Allows you to manage video courses and lessons while allowing users and admin to track progress, leave notes and mark favorites; It also supports managing your own pay-per-view lessons and open them for views for a limited period of time.

The plugin supports watching videos generated from Vimeo Private Video lessons. Once the admin sets the API key for the specified Vimeo account, the plugin will add the option to build several courses/ webinars / lessons based on the lessons that are already defined in the account. Using shortcodes, the admin can display the videos in any page or post and allow users to view the videos, leave notes, bookmark and receive progress update.


> #### Plugin Site
> * [Plugin Site](https://plugins.cminds.com/cm-video-lessons-manager-plugin-for-wordpress/)
> * [Pro Version Detailed Features List](https://plugins.cminds.com/cm-video-lessons-manager-plugin-for-wordpress/)
> * Plugin demo [Read Only mode](https://plugins.cminds.com/cm-video-lessons-manager-plugin-for-wordpress/).

---
 
 
> #### Follow Us
> [Blog](http://plugin.cminds.com/blog/) | [Twitter](http://twitter.com/cmplugins)  | [Google+](https://plus.google.com/108513627228464018583/) | [LinkedIn](https://www.linkedin.com/company/creativeminds) | [YouTube](https://www.youtube.com/user/cmindschannel) | [Pinterest](http://www.pinterest.com/cmplugins/) | [FaceBook](https://www.facebook.com/cmplugins/)


**More Plugins by CreativeMinds**

* [CM Ad Changer](http://wordpress.org/plugins/cm-ad-changer/) - Manage, Track and Report Advertising Campaigns Across Sites. Can turn your Turn your WP into an Ad Server
* [CM Super ToolTip Glossary](http://wordpress.org/extend/plugins/enhanced-tooltipglossary/) - Easily creates a Glossary, Encyclopaedia or Dictionary of your website's terms and shows them as a tooltip in posts and pages when hovering. With many more powerful features.
* [CM Download Manager](http://wordpress.org/extend/plugins/cm-download-manager) - Allows users to upload, manage, track and support documents or files in a download directory listing database for others to contribute, use and comment upon.
* [CM MicroPayments](https://plugins.cminds.com/cm-micropayment-platform/) - Adds the in-site support for your own "virtual currency". The purpose of this plugin is to allow in-site transactions without the necessity of processing the external payments each time (quicker & easier). Developers can use it as a platform to integrate with their own plugins.
* [CM Video Tutorials](https://wordpress.org/plugins/cm-plugins-video-tutorials/) - Video Tutorials showing how to use WordPress and CM Plugins like Q&A Discussion Forum, Glossary, Download Manager, Ad Changer and more.
* [CM OnBoarding](https://wordpress.org/plugins/cm-onboarding/) - Superb Guidance tool which improves the online experience and the user satisfaction.


== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Manage your CM Video Lesson Manager from Left Side Admin Menu

== Frequently Asked Questions ==

> [More FAQ's](https://plugins.cminds.com/cm-video-lessons-manager-plugin-for-wordpress/)
>


== Screenshots ==

1. Plugin Video Lesson Manager Page.
2. Plugin's General Settings.


== Changelog ==
= 2.1.7 =
* Fixed the 2.x upgrade script issue occurring in the multisite network.

= 2.1.6 =
* Fixed bug in the 2.x upgrade script.

= 2.1.5 =
* Fixed PHP bug in the 2.x upgrade script.

= 2.1.4 =
* Modification related to the EDD Payments addon.

= 2.1.3 =
* Fixed PHP error in the tiles view.

= 2.1.2 =
* Fixed issue with creating a Micropayments subscription.

= 2.1.1 =
* Fixed issue with lesson progress values.

= 2.1.0 =
* Added option to change the page template, video layout and playlist layout for each lesson.
* Added options to show the lesson's description and the course's description on the lesson's page.

= 2.0.3 =
* Fixed bug with missing Vimeo.php file.

= 2.0.2 =
* Fixed bug with loading lessons by AJAX when using a shortcode.

= 2.0.1 =
* Fixed bug that occurs with old PHP versions when using empty(static::CONTANT).

= 2.0.0 =
* Reduced API requests usage by storing video meta data directly in Wordpress.
* Added tool to import single video and assign it to a lesson.
* Added Wistia support.
* Changed default names: Channels to Lesson, Categories to Courses.
* Created separate notifications for videos, lessons and courses.
* Changes related to the CM Video Lessons EDD Payments Addon - supporting a single video payment.
* Changes related to the CM Video Lessons Certificates Addon.

= 1.6.3 =
* Added option to disable video description.
* Added option to disable video notes.

= 1.6.2 =
* Disabled automatic privacy settings checks in order to not exceed the Vimeo API requests rate limit.
* Added button "Unlock private videos" to the plugin settings. Admin can add the domain name to the whitelist for each video.

= 1.6.1 =
* Increased cache lifetime due to the new Vimeo API limits.

= 1.6.0 =
* Changes related to the EDD Payments addon integration.

= 1.5.2 =
* Fixed PHP error in the playlist view.

= 1.5.1 =
* Changes related to the EDD Payments addon - added Back link after the payment.

= 1.5.0 =
* Added new cmvl-playlist shortcode's attribute value to hide the videos menu: layout=nomenu
* Fixed an issue with embedding scripts.

= 1.4.3 =
* Updated licensing support.

= 1.4.2 =
* Fixed loading videos on playlist by AJAX.

= 1.4.1 =
* Added option to control the max-width of the playlist widget.
* Added new shortcode parameter maxwidth=0 for the cmvl-playlist shortcode.
* Fixed issue with overwritting the lesson description when choosing another lesson.
* CSS adjustments.

= 1.4.0 =
* Added option for Instant EDD Payments Addon to buy all lessons at once.
* Labels adjustment.
* Improvements of the stats feature.
* Updated licensing support.

= 1.3.1 =
* Fixed issues related to new Wordpress version.

= 1.3.0 =
* Added feature to reload browser when subscription has expired.

= 1.2.16 =
* Small config change.

= 1.2.15 =
* Updated licensing api.
* Added Test Configuration button on the Settings page.
* Added admin notification to configure Vimeo access.
* Interface improvements.

= 1.2.14 =
* Fixed the licensing issue causing the AJAX didn't work.

= 1.2.13 =
* Made the search field requred.
* Updated licensing api support.

= 1.2.12 =
* Fixed searching notes.

= 1.2.11 =
* Fixed settings page issue.
* Fixed licensing issue.

= 1.2.10 =
* Updated the licensing api support. 

= 1.2.9 =
* Updated the licensing api support.

= 1.2.8 =
* Integration with CM Instnat EDD Payments.
* Fixed Statistics page pagination.
* Added new shortcode cmvl-channels-list
* Added new shortcode cmvl-subscriptions

= 1.2.7 =
* Fixed PHP warnings.
* Sorting dashboard tabs in settings.
* Updated the licensing api support.

= 1.2.6 =
* Added minutes option for subscription.
* Added new labels.

= 1.2.5 =
* Removed the Subscription admin menu if Micropayments plugin is not available.

= 1.2.4 =
* Fixed Micropayments issue.
* Fixed JavaScript AJAX issue with playlist loading.
* Added option to embed a custom CSS.
* CSS improvements.

= 1.2.3 =
* Fixed recording statistics method.

= 1.2.2 =
* Fixed bug showing php warning.
* Added admin notifications about user progress.

= 1.2.1 =
* Fixed rounding issue in the users statistics.
* Excluding from the statistics channels which are not public.
* Added option to change video sorting for each channel.
* Added option to disable the channel pages to provide features only by the shortcodes.
* Improved integration with CM Download Manager.

= 1.2.0 =
* Integration with CM Download Manager.
* Added parsing the markup tags in the video description to display the additional buttons.
* Added new shortcode cmvl-dashboard to display the statistics and bookmarks in a tab view.
* Added settings option to manage the dashboard tabs.
* Added settings option to choose the dasbhoard page.
* Added settings option to redirect to the dashboard page after login.

= 1.1.5 =
* Updated the licensing API client.

= 1.1.4 =
* Fixed issue with saving statistics.
* Fixed issue with Vimeo frame origin.

= 1.1.3 =
* Fixed issue with permalinks and loading pages.
* Added JS scrolling to top when moving to the next page.
* Added links to the Micropayments Wallet and Checkout + settings options.
* Added option to manage the privacy requests caching.
* Added the pagination links above the video tiles.
* Added option to clear the search results.

= 1.1.2 =
* Fixed PHP error on the bookmarks page.
* Fixed a permalink issue on the WP SEO by Yoast sitemap.

= 1.1.1 =
* Added an URL search parameters.
* CSS improvements.

= 1.1.0 =
* Added pagination on the tiles view.
* Added the shortcode's parameters to show/hide the search bar and the navigation bar separately.
* Added responsive CSS for the tiles view.
* CSS improvements.

= 1.0.7 =
* Fixed embeding domains issue.
* Moved the video title below the player to avoid the alignment issues.
* CSS improvements.

= 1.0.6 =
* Added the Vimeo Albums support.
* Modified the search engine to avoid issues with private videos searching.

= 1.0.5 =
* Fixed security issue related to the add_query_arg() function.

= 1.0.4 =
* Added admin notification after subscription has been activated.

= 1.0.3 =
* Added the Subscriptions report.
* Support for manually add subscriptions.

= 1.0.2 =
* Fixed possible JavaScript issue.

= 1.0.1 =
* Fixed issue with Micropayments.

= 1.0.0 =
* Initial release