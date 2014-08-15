<?php

/**
 * Tests that we get ACF data for posts where applicable
 *
 * @package wordpress-plugins-tests
 */
class WP_QQ_ACF extends WP_UnitTestCase {

	public static function setUpBeforeClass () {
		if(function_exists("register_field_group")) {
			register_field_group(array (
				'id' => 'acf_test-post-data',
				'title' => 'Test Post data',
				'fields' => array (
					array (
						'key' => 'field_53b71bb393d1a',
						'label' => 'Test ACF',
						'name' => 'test_acf',
						'type' => 'text',
						'instructions' => 'just add some text, yo',
						'default_value' => 'foo to the bar',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'formatting' => 'html',
						'maxlength' => '',
					),
				),
				'location' => array (
					array (
						array (
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'post',
							'order_no' => 0,
							'group_no' => 0,
						),
					),
				),
				'options' => array (
					'position' => 'normal',
					'layout' => 'no_box',
					'hide_on_screen' => array (
					),
				),
				'menu_order' => 0,
			));
		}

	}

	function test_acf_found() {

		// $qq = new QQuery();

		// $this->assertTrue($qq->uses_acf, 'ACF exists');

	}

	function test_acf_data() {

		// $post_id = $this->factory->post->create( );

		// $qq = new QQuery();

	// 	// fetch that post via QQ
		// $qq_post = $qq->get( $post_id );

		// print_r( get_fields($post_id) );

	// 	$this->assertObjectHasAttribute('acf', $qq_post);

	}
}
