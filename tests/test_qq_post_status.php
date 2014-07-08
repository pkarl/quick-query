<?php

/**
 * Tests that we get ACF data for posts where applicable
 *
 * @package wordpress-plugins-tests
 */
class WP_QQ_POST_STATUS extends WP_UnitTestCase {

	function test_status() {

		$test_post_status_0 = 'draft';
		$test_post_status_1 = 'pending';
		$test_post_status_2 = 'private';

		$basic_post_count = 5;
		$draft_post_count = 10;
		$pending_post_count = 15;
		$private_post_count = 20;

		$total_post_count = $basic_post_count + $draft_post_count + $pending_post_count + $private_post_count;

		$post_ids = 			$this->factory->post->create_many( $basic_post_count );
		$post_draft_ids = 		$this->factory->post->create_many( $draft_post_count,   array('post_status'=>$test_post_status_0) );
		$post_pending_ids = 	$this->factory->post->create_many( $pending_post_count, array('post_status'=>$test_post_status_1) );
		$post_private_ids = 	$this->factory->post->create_many( $private_post_count, array('post_status'=>$test_post_status_2) );

		$qq = new QQuery();

		$all_posts = 		$qq->all()->status('any')->go();
		$draft_posts = 		$qq->status( $test_post_status_0 )->all()->go();
		$pending_posts = 	$qq->status( $test_post_status_1 )->all()->go();
		$private_and_pending_posts =
							$qq->status( [$test_post_status_2, $test_post_status_1] )->all()->go();

		print_r( count($all_posts) . "\n");
		print_r( count($draft_posts) . "\n");
		print_r( count($pending_posts) . "\n");
		print_r( count($private_and_pending_posts) . "\n");

		$this->assertEquals( count($all_posts), $total_post_count);
		$this->assertEquals( count($draft_posts), $draft_post_count);
		$this->assertEquals( count($pending_posts), $pending_post_count);
		$this->assertEquals( count($private_and_pending_posts), $pending_post_count + $private_post_count);

	}
}
