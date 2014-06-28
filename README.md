# Quick Query

Quick Query is an interface for fetching data from WordPress that's intuitive and pleasant to use. It uses a jQuery-like syntax to chain together simple pieces of information and get you the right information in return.

## How it works

Quick Query began as a scrap of syntactic sugar for dealing with [WP_Query](http://codex.wordpress.org/Class_Reference/WP_Query). Over the course of a few projects, this sugary treat grew into necessity that simplified 80% of the time-consuming query code into a few pleasant lines.

Here's an comparison in a demanding scenario:
```php
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

## The plan

I'm working on creating an honest-to-goodness open source version of it from the ground up. At the time of this writing, Quick Query is just a class wrapper with all of our code commented out. 

I'll be working on this (along with anyone who enjoys the idea of mitigating the emotional damage WP_Query causes) to incrementally re-add the functionality of `QQuery` along with tests and docs as we go.

# Running Tests

I'm just going to document what I went through to get a local test env up in OSX.

I'm following this first: https://github.com/Homebrew/homebrew-php to get PHP + Pear + PHPUnit
