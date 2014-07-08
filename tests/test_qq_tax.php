<?php

/**
 * Tests that we get ACF data for posts where applicable
 *
 * @package wordpress-plugins-tests
 */
class WP_QQ_TAXONOMY extends WP_UnitTestCase {

	function test_category() {

		$post_id = $this->factory->post->create( ['tag'=>['test_category']] );

		$qq = new QQuery();

		$post = $qq->id($post_id)->extend('terms')->go();

		print_r($post);

	}

	function test_tag() {

	}

	function test_term() {

	}

	function test_taxonomy() {

	}

	// this is the crazy one
	function test_tax() {

	}

	function test_exclude() {

	}
}
