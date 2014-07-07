<?php

/**
 * Tests that we get ACF data for posts where applicable
 *
 * @package wordpress-plugins-tests
 */
class WP_QQ_PAGING extends WP_UnitTestCase {

	function test_paging() {

		$post_ids = $this->factory->post->create_many( 20 ); // make sure we have more posts than we need

		$posts_per_page = 5;
		$page = 3;

		// get ALL posts
		$query = new WP_Query('posts_per_page=-1');
		$all_posts = $query->posts;

		$this->assertNotEmpty($all_posts, 'we have posts!');

		$query = new WP_Query( 'posts_per_page=' . $posts_per_page . '&paged=' . $page );
		$wp_paged_posts = $query->posts;

		// now get a QQ going
		$qq = new QQuery();
		$paged_qq_posts = $qq->ppp($posts_per_page)->page($page)->sort('ID')->go();

		$this->assertEquals( array_map("get_ID", $paged_qq_posts), array_map("get_ID", $wp_paged_posts) );

	}

	function test_offset() {

		$post_ids = $this->factory->post->create_many( 25 ); // make sure we have more posts than we need

		$posts_per_page = 10;
		$page = 2;
		$offset = 1;

		$query = new WP_Query( 'posts_per_page=' . $posts_per_page . '&paged=' . $page . '&offset=' . $offset );
		$wp_offset_posts = $query->posts;

		// now get a QQ going
		$qq = new QQuery();
		$offset_qq_posts = $qq->ppp($posts_per_page)->page($page)->offset($offset)->sort('ID')->go();

		$this->assertEquals( array_map("get_ID", $offset_qq_posts), array_map("get_ID", $wp_offset_posts) );
	}
}

/**
 * a wild method appears!
 * @param  WP_POST 	$post
 * @return int 		The post ID
 */
function get_ID($post) {
	return $post->ID;
}
