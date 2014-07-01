<?php

/**
 * Tests that we get the kinds of results we expect to get
 *
 * @package wordpress-plugins-tests
 */
class WP_QQ_Results extends WP_UnitTestCase {

 	// $this->post = $this->factory->post->create();
 	// $this->factory->post->create_many( 50 );

	function test_qq_exists() {

		$qq = new QQuery();
		$this->assertInstanceOf( 'QQuery', $qq );

	}

	function test_get_post_by_id() {

		// make a post
		$post_id = $this->factory->post->create();

		// fetch that post via QQ
		$qq_post = QQuery::get( $post_id );


		// test if the get_post post and the qq_post are the same
		$this->assertEquals( get_post( $post_id ), $qq_post );

		// make sure the qq_post is a WP_Post object
		$this->assertInstanceOf( 'WP_Post', $qq_post );

		// make sure QQ::get returns false when we provide junk input
		$this->assertFalse( QQuery::get( -1 ) );

	}

}
