<?php

/**
 * Tests that we get the kinds of results we expect to get
 *
 * @package wordpress-plugins-tests
 */
class WP_QQ_Type extends WP_UnitTestCase {

	function test_get_by_type() {

		$test_type_0 = 'test_type_0';
		$test_type_1 = 'test_type_1';
		$test_type_2 = 'test_type_2';

		add_action( 'init', 'create_post_types' );
		function create_post_type() {
			register_post_type( $test_type_0 );
			register_post_type( $test_type_1 );
			register_post_type( $test_type_2 );
		}

		$num_type_1 = 11;
		$this->factory->post->create_many( $num_type_1, array( 'post_type' => $test_type_1 ) );

		$num_type_2 = 12;
		$this->factory->post->create_many( $num_type_2, array( 'post_type' => $test_type_2 ) );

		// seeding additional records
		$this->factory->post->create_many( 5 );

		$qq = new QQuery();
		$all_posts = $qq->all()->go();

		$type_0_posts = $qq->type( $test_type_0 )->all()->go();
		$type_1_posts = $qq->type( $test_type_1 )->all()->go();
		$type_2_posts = $qq->type( $test_type_2 )->all()->go();

		$this->assertEquals( $num_type_1, count($type_1_posts) );
		$this->assertEquals( $num_type_2, count($type_2_posts) );

		$this->assertEmpty( $type_0_posts );
	}

}
