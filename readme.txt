=== WP-Whoami ===

Contributors: realloc
Donate link: http://www.greenpeace.org/international/
Tags: widget, author, bio, social media
Requires at least: 3.1
Tested up to: 4.0
Stable tag: 0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Just another widget to show a photo, a bio and some social media links with nice webfont-icons

== Description ==

Just another widget to show a photo, a bio and some social media links with nice webfont-icons

The plugin is using the JustVector Social Icons Font created by [Alex Peattie](http://www.alexpeattie.com/projects/justvector_font/).

== Installation ==

* download the plugin and uncompress it with your preferred unzip programme
* copy the entire directory in your plugin directory of your wordpress blog (/wp-content/plugins)
* activate the plugin in your plugin page
* set some configuration in your profile and place the widget in your sidebar
* optionally you can place the code `<?php if ( function_exists( 'the_whoami_bio' ) ) the_whoami_bio( $user_id ); ?>` directly in your theme files if you want to print out the bio of a specific user 

== Changelog ==

= 0.4 =
* Bugfixes strict mode

= 0.3 =
* new function `the_whoami_bio` for echoing the bio of a specific user
* some minor improvements

= 0.2 =
* de_DE language-files added
* `rel="me"` to the profile links added
* whoami_admin_networks-filter added
* whoami_frontend_css-filter added

= 0.1 =
* first version
