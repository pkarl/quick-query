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

	function test_reuse_same_instance() {

		$qq = new QQuery();

		$post_ids = $this->factory->post->create_many( 10 );

		$posts1 = $qq->id( $post_ids )->sort('name', 'DESC')->go();

		$posts2 = $qq->id( $post_ids )->go();

		// test that the sort DID NOT persist between go() requests
		$this->assertNotEquals( $posts1[0]->ID, $posts2[0]->ID );
	}

	function test_static_get_post_by_id() {

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

	function test_get_post_in_array() {

		$howmany = 20; // larger than default page size

		$post_ids = $this->factory->post->create_many( $howmany );
		shuffle($post_ids);

		$working_ids = array_slice( $post_ids, 5 );

		$qq = new QQuery();

		// using array-based set of IDs
		$posts1 = $qq->id( $working_ids )->all()->go();

		// make sure we get the same number of posts as we requested
		$this->assertEquals( count($posts1), count($working_ids) );

		$posts2 = $qq->id( implode( ',', $working_ids ) )->all()->go();

		// make sure a comma-delimited set of ids is returning posts properly
		$this->assertEquals( count($posts2), count($working_ids) );

		$warning = false;
		try {
			$qq->id( '' )->go();
		} catch(Exception $e) {
			$warning = true;
		}

		$this->assertTrue( $warning, 'a warning has been caught' );
	}

	function test_exclude_ids() {

		$howmany = 20;

		$post_ids = $this->factory->post->create_many( $howmany );

		shuffle($post_ids);
		$working_ids = array_slice( $post_ids, 5 );

		$qq = new QQuery();

		$posts1 = $qq->all()->go();

		$posts2 = $qq->all()->exclude( $working_ids )->go();

		$this->assertNotEquals( $posts1, $posts2);

	}

}
