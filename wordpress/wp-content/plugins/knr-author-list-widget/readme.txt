=== KNR Author List Widget ===
Contributors: k_nitin_r
Tags: author, list, sidebar, widget, ordered, sorted
Requires at least: 3.0
Tested up to: 3.3
Stable tag: 2.0.4

Displays a list of authors, contributors, editors, and administrators on the blog as an ordered list, unordered list, or a dropdown list. You can use the ordered list to display a list of 'top authors' on the blog. Tweaked for performance and highly configurable.

== Description ==

The KNR Author List Widget plugin, by Nitin Reddy Katkam, displays a list of authors, and editors 
on the blog as an ordered list, unordered list, or a dropdown list. You can use the ordered list to display a list of 'top authors' on the blog.

== Installation ==

1. Upload the plugin to the `/wp-content/plugins/knr-author-list-widget/` directory
(this can be done automatically via the WordPress 2.7/2.8 Plugin Browser/Installer interface)
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Through the 'Widgets' sub-menu (under 'Appearance' menu), add the widget to your sidebar

== Screenshots ==

1. KNR Author list on the classic WordPress theme
2. KNR Author List in the list of widgets after activating the plugin. There are more options not shown in the screenshot.
3. KNR Author list Widget options in the Appearance menu
4. Custom Author Ordering

== Changelog ==

= 2.0.4 =
* Fixed: Remove empty class attribute in OL/UL tags when no class name is specified

= 2.0.3 =
* Fixed the deprecated errors in WP 3.x
* Fixed errors handling checkbox settings
* The updated add_options_page call requires WP 3.x

= 2.0.2 =
* Fixed the saving of the author limit widget setting

= 2.0.1 =
* Validation of values when saving custom author order to avoid SQL injection
* Fixed the loading of plugin options found in 2.0.0

= 2.0.0 =
* Uses the newer (introduced in 2.8) WordPress widgets API. You can now include the widget more than once in your sidebars
* Uses the WP-generated author URL (to support permalinks) instead of constructing the URL with the author's ID

= 1.9.1 =
* Added sorting by display name, and hide on pages matching URL.

= 1.9 =
* Fixed the "more" link functionality when displaying a limited number of authors. You do not need this update unless you are using the Author Limit and More Authors Link settings.

= 1.8 =
* Renamed 'Unordered list class' to 'List class' in widget options as it also applies to ordered lists.
* Fixed markup problem when specifying 'List class' (bug only affected users who specified a value for this in Markup options).
* A little clean up in the code for displaying the dropdown instead of the list.

= 1.7 =
* Added Gravatar support.
* Added 'Show as dropdown' feature.
* Added 'Show as ordered list' feature.

= 1.6 =
* Fixed the filtering of posts that led to an incorrect post count in some cases.

= 1.5 =
* You can now add a "more authors" link if the number of authors exceeds the limit.

= 1.4 =
* The widget now enables you to set a custom order for the authors. If you want only specific 
authors to appear on the list, set the order manually and set the limit to the number of authors 
you want to display.
* Custom ordering enables the user to order the items manually, so the 'reverse' option is
ignored. It's an application of the "Eschew Obfuscation" principle :-)

= 1.3 =
* In addition to displaying authors, you can opt to display administrators, editors, and contributors in the list.
* You change the sort order to First Name, Last Name, First & Last Name, Last & First Name, No. of Posts, 
Author Registration Date or No Sorting.

= 1.2 =
* The administrator can now opt for whether to include contributors in the list.

= 1.1 =
* The administrator can now opt for whether to include (i) authors without any posts, (ii) editors, (iii) administrators. There's also an option to limit the number of authors in the list.

= 1.0 =
* Author listing - displays a list of all authors and editors. This is an improvement over plugins that use the wp_list_authors especially if you've got a lot of subscribers on your blog.


== Notes ==

= General Info =

Send any queries, comments, feedback, contributions to k.nitin.r (a) gmail.com

= Other Notes =

The custom author order interface under settings requires WordPress 2.6 or higher but the other features work just fine on WordPress 2.5. This is due to the inavailability of the jQuery library on pre-2.6 versions.

'Show as dropdown' overrides 'Show as ordered list'. This may seem counter-intuitive but admin interface needs a bit of cleanup. I'll soon make it simpler to use.

= Tips =

* To improve performance by reducing the number of full table scans in the database, create an index on the column "knr_author_order" in the table "(wp_prefix)users".
* You can style the author list with CSS by defining the CSS classes in the markup options of the widget. Using a text-align left for the list items with a float right for the post count aligns the post count of all the items in the list vertically. UL/OL with CSS is so much more flexible than HTML tables!! :-)
