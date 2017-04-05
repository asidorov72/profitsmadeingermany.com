=== Easy Digital Downloads Search Widget ===
Contributors: daveshine, deckerweb
Donate link: http://genesisthemes.de/en/donate/
Tags: easy digital downloads, edd, digital downloads, downloads, e-downloads, search, widget, custom post type, search widget, searching, widget-only, shortcode, deckerweb
Requires at least: 3.3
Tested up to: 3.6 Beta
Stable tag: 1.1.0
License:  GPL-2.0+
License URI: http://www.opensource.org/licenses/gpl-license.php

This Plugin adds a search widget for the Easy Digital Downloads plugin post type independent from the regular WordPress search.

== Description ==

> #### Nice Little Helper Tool for EDD :-)
> This **small and lightweight plugin** is pretty much like the regular WordPress search widget but limited to only search the post type of the new and awesome [*Easy Digital Downloads*](http://easydigitaldownloads.com/) plugin: *Downloads*.
> 
> Just drag the widget to your favorite widget area and enjoy finally to have Downloads-limited search function for your [*Easy Digital Downloads*](http://wordpress.org/extend/plugins/easy-digital-downloads/) install ;-).

> Since v1.1.0 you can also use the Shortcode `[edd-searchbox]`. Futher, the plugin is also fully Multisite compatible, you can also network-enable it if ever needed (per site use is recommended).

= Features =
* Improved search results display for themes and (EDD) "Downloads" post type detection/ restriction.
* Optional widget display options for faster setup.
* Two - fully optional - text fields: "Intro text" and "Outro text" to display for example additional Downloads or user instructions. Just leave blank to not use them!
* Shortcode `[edd-searchbox]` to have (EDD) "Downloads" specific search box anywhere Shortcodes are supported. ([See FAQ here](http://wordpress.org/extend/plugins/edd-search-widget/faq/) for the supported parameters.)
* Lots of filter hooks & action hooks throughout the plugin, making it even more flexible and developer friendly
* Fully WPML compatible!
* Fully Multisite compatible, you can also network-enable it if ever needed (per site use is recommended).
* Tested with WordPress branches: 3.5, plus upcoming 3.6, as well as with older branches 3.3 and 3.4 - also in debug mode (no stuff there, ok? :)

= Localization =
* English (default) - always included
* German (de_DE) - always included
* .pot file (`edd-search-widget.pot`) for translators is also always included :)
* Easy plugin translation platform with GlotPress tool: [Translate "Easy Digital Downloads Search Widget"...](http://translate.wpautobahn.com/projects/wordpress-plugins-deckerweb/edd-search-widget)
* *Your translation? - [Just send it in](http://genesisthemes.de/en/contact/)*

[A plugin from deckerweb.de and GenesisThemes](http://genesisthemes.de/en/)

= Feedback =
* I am open for your suggestions and feedback - Thank you for using or trying out one of my plugins!
* Drop me a line [@deckerweb](http://twitter.com/#!/deckerweb) on Twitter
* Follow me on [my Facebook page](http://www.facebook.com/deckerweb.service)
* Or follow me on [+David Decker](http://deckerweb.de/gplus) on Google Plus ;-)

= Tips & More =
* *1st Plugin tip:* [My Easy Digital Downloads Toolbar / Admin Bar plugin](http://wordpress.org/extend/plugins/edd-toolbar/) -- a great time safer and helper tool :)
* *2nd Plugin tip:* [My Genesis Connect for Easy Digital Downloads plugin](http://wordpress.org/extend/plugins/genesis-connect-edd/) -- making digital downloads even easier with nice integration for Genesis Framework :)
* [Also see my other plugins](http://genesisthemes.de/en/wp-plugins/) or see [my WordPress.org profile page](http://profiles.wordpress.org/daveshine/)
* Tip: [*GenesisFinder* - Find then create. Your Genesis Framework Search Engine.](http://genesisfinder.com/)

== Installation ==

1. Upload the entire `edd-search-widget` folder to the `/wp-content/plugins/` directory -- or just upload the ZIP package via 'Plugins > Add New > Upload' in your WP Admin
2. Activate the plugin through the 'Plugins' menu in WordPress
3. On the regular WordPress Widgets settings page just drag the *EDD Downloads Search* to your favorite widget area and you're done :)

= How It Works =
* All is done smoothly under the surface :)
* *Downloads*: searches in Downloads title, Downloads content/excerpts.

**Note for own translation/wording:** For custom and update-secure language files please upload them to `/wp-content/languages/edd-search-widget/` (just create this folder) - This enables you to use fully custom translations that won't be overridden on plugin updates. Also, complete custom English wording is possible with that, just use a language file like `edd-search-widget-en_US.mo/.po` to achieve that (for creating one see the tools on "Other Notes").

== Frequently Asked Questions ==

= What are the supported Shortcode parameters? =
Currently, these attributes/ parameters are available:

* `label_text` — Label text before the input field (default: `Search downloads for:`)
* `placeholder_text` — Input field placeholder text (default: `Search downloads...`)
* `button_text` — Submit button text (default: `Search`)
* `class` — Can be a custom class, added to the wrapper `div` container (default: none, empty)

= How to use the Shortcode? =
Place the Shortcode tag in any Post, Page, Download product, or Shortcode-aware area. A few examples:

`
[edd-searchbox]
--> displays search box with default values

[edd-searchbox label_text=""]
--> will display no label!

[edd-searchbox placeholder_text="Search our digital products..." class="my-custom-class"]
--> will display other placeholder, plus add custom wrapper class for custom styling :-)
`

= How can I style or remove the label "Search downloads for"? =
(1) There's an extra CSS class included for that, named `.eddsw-label` so you can style it with any rules or just remove this label with `display:none`.

(2) Second option, you can fully remove the label by adding a constant to your theme's/child theme's functions.php file or to a functionality plugin etc. - this will work for both, Widget & Shortcode:
`
/** Easy Digital Downloads Search Widget: Remove Search Label */
define( 'EDDSW_SEARCH_LABEL_DISPLAY', false );
`

= How can I change the text of the label "Search downloads for"? =
(1) You can use the translation language file to use custom wording for that - for English language the file would be /`wp-content/plugins/edd-search-widget/languages/edd-search-widget-en_US.mo`. Just via the appropiate language/translation file. For doing that, a .pot/.po file is always included.

(2) Second option: Or you use the built-in filter to change the string. Add the following code to your `functions.php` file of current them/child theme, just like that:
`
add_filter( 'eddsw_filter_label_string', 'custom_eddsw_label_string' );
/**
 * Easy Digital Downloads Search Widget: Custom Search Label
 */
function custom_eddsw_label_string() {
	return __( 'Your custom search label text', 'your-theme-textdomain' );
}
`

= How can I change the text of the placeholder in the search input field? =
(1) See above question: via language file!

(2) Or second option, via built-in filter for your `functions.php` file of theme/child theme:
`
add_filter( 'eddsw_filter_placeholder_string', 'custom_eddsw_placeholder_string' );
/**
 * Easy Digital Downloads Search Widget: Custom Placeholder Text
 */
function custom_eddsw_placeholder_string() {
	return __( 'Your custom placeholder text', 'your-theme-textdomain' );
}
`

= How can I change the text of the search button? =
(1) Again, see above questions: via language file!

(2) Or second option, via built-in filter for your `functions.php` file of theme/child theme:
`
add_filter( 'eddsw_filter_search_string', 'custom_eddsw_search_string' );
/**
 * Easy Digital Downloads Search Widget: Custom Search Button Text
 */
function custom_eddsw_search_string() {
	return __( 'Your custom search button text', 'your-theme-textdomain' );
}
`

All the custom & branding stuff code above as well as theme CSS hacks can also be found as a Gist on GitHub: https://gist.github.com/2857613 (you can also add your questions/ feedback there :)

= How can I further style the appearance of this widget? =
There are CSS classes for every little part included:

* main widget ID: `#edd_search-<ID>`
* main widget class: `.widget_edd_search`
* intro text: `.eddsw-intro-text`
* form wrapper ID: `#eddsw-form-wrapper`
* form: `.eddsw-search-form`
* form div container: `.eddsw-form-container`
* search label: `.eddsw-label`
* input field: `.eddsw-search-field`
* search button: `.eddsw-search-submit`
* outro text: `.eddsw-outro-text`

= How can I style the actual search results? =
This plugin's widget is limited to provide the widget and search functionality itself. Styling the search results output in your THEME or CHILD THEME is beyond the purpose of this plugin. You might style it yourself so it will fit your theme.

= In my theme this widget's display is "crashed" - what could I do? =
Please report in the [support forum here](http://wordpress.org/support/plugin/edd-search-widget), giving the correct name of your theme/child theme plus more info from where the theme is and where its documentation is located. For example the "iFeature Lite" theme, found on WordPress.org has issues with the CSS styling. For this example theme you found a CSS fix/hack directly here: https://gist.github.com/2857613#file_theme_ifeature_lite.css ---> Just place this additional CSS styling ad the bottom of this file `/wp-content/themes/ifeature/css/style.css` (please note the `/css/` subfolder here!)

== Screenshots ==

1. Easy Digital Downloads Search Widget in WordPress' widget settings area: default state ([Click here for larger version of screenshot](https://www.dropbox.com/s/mscc03gza4gr5ij/screenshot-1.png))
2. Easy Digital Downloads Search Widget in a sidebar: default state (shown here with [the free Autobahn Child Theme for Genesis Framework](http://genesisthemes.de/en/genesis-child-themes/autobahn/)) ([Click here for larger version of screenshot](https://www.dropbox.com/s/md4eqjv7kzon12b/screenshot-2.png))
3. Easy Digital Downloads Search Widget in WordPress' widget settings area: with custom intro and outro text ([Click here for larger version of screenshot](https://www.dropbox.com/s/309v5cjuz3bgk6p/screenshot-3.png))
4. Easy Digital Downloads Search Widget in a sidebar: custom intro and outro text shown - all parts can by styled individually, just [see FAQ section here](http://wordpress.org/extend/plugins/edd-search-widget/faq/) for custom CSS styling. ([Click here for larger version of screenshot](https://www.dropbox.com/s/zg41k72solyx3tj/screenshot-4.png))
5. Easy Digital Downloads Search Widget: plugin help tab in admin area. ([Click here for larger version of screenshot](https://www.dropbox.com/s/gd8zva4jsmnq4o7/screenshot-5.png))

== Changelog ==

= 1.1.0 (2013-04-29) =
* NEW: Added input options to Widget directly for: Label text, Placeholder text, Search button text -- now it's really easy to change this stuff, right? :-)
* NEW: Added widget display options to have faster setup of the widget. Default setting is "global" (as was before). -- *Note: If the provided options are not enough, just use the default setting and use other plugins like "Widget Logic" or "Widget Display" to setup more complex widget display behaviors.*
* NEW: Added Shortcode to display a little (EDD) "Downloads"-specific search box anywhere, [see FAQ here](http://wordpress.org/extend/plugins/edd-search-widget/faq/) for supported parameters.
* UPDATE: Some code refactoring for improved performance and better future maintenance.
* UPDATE: Improved and extended help tab system.
* CODE: Some code/ documentation updates & improvements.
* UPDATE: Updated German translations and also the .pot file for all translators!
* UPDATE: Initiated new three digits versioning, starting with this version.
* UPDATE: Moved screenshots to 'assets' folder in WP.org SVN to reduce plugin package size.
* NEW: Added banner image on WordPress.org for better plugin branding :)

= 1.0.0 (2012-06-02) =
* Initial release

== Upgrade Notice ==

= 1.1.0 =
Several additions & improvements: Added Shortcode; improved Widget; code/ documentation improvements. Added Spanish translations; updated German translations plus .pot file for all translators.

= 1.0.0 =
Just released into the wild.

== Plugin Links ==
* [Translations (GlotPress)](http://translate.wpautobahn.com/projects/wordpress-plugins-deckerweb/edd-search-widget)
* [User support forums](http://wordpress.org/support/plugin/edd-search-widget)
* [Code snippets archive for customizing, GitHub Gist](https://gist.github.com/2857613)
* *Plugin tip:* [My Easy Digital Downloads Toolbar / Admin Bar plugin](http://wordpress.org/extend/plugins/edd-toolbar/) -- a great time safer and helper tool :)

== Donate ==
Enjoy using *Easy Digital Downloads Search Widget*? Please consider [making a small donation](http://genesisthemes.de/en/donate/) to support the project's continued development.

== Translations ==

* English - default, always included
* German (de_DE): Deutsch - immer dabei! [Download auch via deckerweb.de](http://deckerweb.de/material/sprachdateien/easy-digital-downloads-plugin/#edd-search-widget)
* For custom and update-secure language files please upload them to `/wp-content/languages/edd-search-widget/` (just create this folder) - This enables you to use fully custom translations that won't be overridden on plugin updates. Also, complete custom English wording is possible with that, just use a language file like `edd-search-widget-en_US.mo/.po` to achieve that (for creating one see the following tools).

**Easy plugin translation platform with GlotPress tool:** [**Translate "Easy Digital Downloads Search Widget"...**](http://translate.wpautobahn.com/projects/wordpress-plugins-deckerweb/edd-search-widget)

*Note:* All my plugins are internationalized/ translateable by default. This is very important for all users worldwide. So please contribute your language to the plugin to make it even more useful. For translating I recommend the awesome ["Codestyling Localization" plugin](http://wordpress.org/extend/plugins/codestyling-localization/) and for validating the ["Poedit Editor"](http://www.poedit.net/), which works fine on Windows, Mac and Linux.

== Additional Info ==
**Idea Behind / Philosophy:** A search feature or a widget is just missing yet for the new and awesome Easy Digital Downloads forum plugin. So I just set up this little widget. It's small and lightweight and only limited to this functionality.