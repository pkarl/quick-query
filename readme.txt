=== Plugin Name ===
Contributors: pkarl
Tags: query, wp_query, posts, post, database
Requires at least: 3.0.1
Tested up to: 3.9.1
Stable tag: 0.0.2
License: MIT
License URI: https://github.com/pkarl/quick-query/blob/master/LICENSE

Get posts and data sanely with Quick Query.

== Description ==

Quick Query is a snappy interface for WP_Query that makes querying with WordPress straightforward and concise. It's built on top of WP_Query (with a few exceptions), and provides a much more flexible and accomodating interface for querying posts.

Here's an example:

```
// The WP_Query way
$args = array(
	'post_type' => 'post',
	'tax_query' => array(
		'relation' => 'AND',
		array(
			'taxonomy' => 'movie_genre',
			'field' => 'slug',
			'terms' => array( 'action', 'comedy' )
		),
		array(
			'taxonomy' => 'actor',
			'field' => 'id',
			'terms' => array( 103, 115, 206 ),
			'operator' => 'NOT IN'
		)
	)
);
$query = new WP_Query( $args );

// The Quick Query way
$posts = $q->type('post')->tags( ['movie_genre' => ['action','comedy'], 'actor' => [103, 115, 206]], 'AND' );
```

Learn more about Quick Query [here](https://github.com/pkarl/quick-query)

== Installation ==

We're working on getting this up into composer registries and what not. For now you can follow this simple process:

1. Upload `QuickQuery.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Query away!

== The Future! ==

Besides adding interfaces for the most common querying tasks (posts, tags, authors, sorting, etc.), I'd like to build in sensitivity to ACF fields/data and possibly a caching interface.

Before that can happen, there are lots of tests to be written and plenty of design decisions to be made (and, of course, documentation!).

== Changelog ==

= 0.0.2 =
* Initial push to wordpress.org
