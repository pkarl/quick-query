=== Plugin Name ===
Contributors: pkarl
Tags: query, wp_query, posts, post, database
Requires at least: 3.1
Tested up to: 3.9.1
Stable tag: 0.0.2
License: MIT
License URI: https://github.com/pkarl/quick-query/blob/master/LICENSE

Get posts and data sanely with Quick Query.

== Description ==

Quick Query is a snappy interface for WP_Query that makes querying with WordPress straightforward and concise. It's built on top of WP_Query (with a few exceptions), and provides a much more flexible and accomodating interface for querying posts.

Learn more about Quick Query and see code examples [here](https://github.com/pkarl/quick-query).

## Quick Query is under heavy development!

I'm writing code + testing as fast as I can to get us to a good minimum set of functionality.

(photo [credit](https://www.flickr.com/photos/seeminglee/4556156477))

== Installation ==

We're working on getting this up into composer registries and what not. For now you can follow this simple process:

1. Upload `QuickQuery.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Query away!

== The Future! ==

Besides adding interfaces for the most common querying tasks (posts, tags, authors, sorting, etc.), I'd like to build in sensitivity to ACF fields/data and possibly a caching interface.

Before that can happen, there are lots of tests to be written and plenty of design decisions to be made (and, of course, documentation!).

== Who's this for? ==

This is certainly geared more toward developers than designers and hobbyists. That said, It does return WP_Post objects, so all/most of the functionality you're used to may be there.

== Changelog ==

= 0.0.2 =
* Initial push to wordpress.org
