<?php

/**
 * Tests that we get ACF data for posts where applicable
 *
 * @package wordpress-plugins-tests
 */
class WP_QQ_TAXONOMY extends WP_UnitTestCase {

	function test_term_data() {

		$post_id = $this->factory->post->create();

		wp_set_object_terms( $post_id, 'some-term', 'category' ); // replace existing, once
		wp_set_object_terms( $post_id, 'some-term-2', 'category', true );
		wp_set_object_terms( $post_id, 'some-term-3', 'category', true );

		$qq = new QQuery();
		$post = $qq->id($post_id)->extend('terms')->go();

		var_dump($post);

		// $this->assertNotEmpty($post->terms);

	}

	function test_category() {

		$howmany = 5;
		$post_ids = $this->factory->post->create_many( $howmany );

		$term = 'test-category';
		foreach($post_ids as $key=>$post_id) {
			wp_set_object_terms( $post_id, $term, 'category'); // append
		}

		$qq = new QQuery();
		$posts = $qq->category($term)->all()->go();

		$this->assertEquals( $howmany, count($posts) );
	}

	function test_single_term() {

		$qq = new QQuery();

		$howmany = 7;
		$post_ids = $this->factory->post->create_many( $howmany );

		$posts = $qq->tax('uncategorized')->go();

		$this->assertEquals($howmany, count($posts));
	}

	function test_multiple_terms() {

		$post_1 = $this->factory->post->create();
		$post_2 = $this->factory->post->create();

		register_taxonomy( 'test', 'post', [] );

		wp_set_object_terms( $post_1, 'term-1', 'test' );
		wp_set_object_terms( $post_1, 'term-2', 'test', true );

		wp_set_object_terms( $post_2, 'term-1', 'test' );
		wp_set_object_terms( $post_2, 'term-3', 'test', true );

		$qq = new QQuery();

		$posts_both = $qq->tax('term-1')->go(); // should return both posts
		$this->assertEquals(2, count($posts_both) );

		$post_first = $qq->tax('term-2')->go(); // should only return the first
		$this->assertEquals(1, count($post_first) );

		$post_second = $qq->tax('term-3')->go(); // should only return the second
		$this->assertEquals(1, count($post_second) );

		$posts_none = $qq->tax(['term-2', 'term-3'])->go(); // should return none because default to AND
		$this->assertEquals(0, count($posts_none) );

		$posts_both_again = $qq->tax(['term-2', 'term-3'], ['relation'=>'OR'])->go(); // should return both because we set OR
		$this->assertEquals(2, count($posts_both_again) );
	}

	function test_tax_exclude() {

		// $term_id = $this->factory->term->create( array( 'name' => 'Test Term Category', 'taxonomy' => 'category', 'slug' => $term ) );

	}
}
