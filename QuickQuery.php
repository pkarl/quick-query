<?php
/*
Plugin Name: Quick Query
Plugin URI: https://github.com/pkarl/quick-query
Description: This plugin provides a querying interface for posts, and returns homogenous objects
Version: 0.0.2
Author: Pete Karl II
Author URI: https://github.com/pkarl/quick-query
License: MIT
*/

require_once('QQ.Utils.php');

Class QQuery {

	/** we may need a set of proxy vars in case a user runs
		->author('pete')->authors(['jared','mike']), so one
		doesn't blow the other away */

	// default WP_Query config
	private $query_assoc = array();

	private $default_assoc = array(
		'post_type' => 'any',
		'post_status' => 'publish'
	);

	// private $meta_fields = array();

	// private $comments_params = array(
	// 		'status' => 'approve',
	// 	);
	public $uses_acf = false;

	public function __construct() {
		$this->reset();

		if( class_exists('acf') ) {
			$this->uses_acf = true;
		}
	}



	/**
	 * to_array accepts a reference to an array or string and converts to an array (or does nothing). This
	 * exists because it's a slice of logic that happens all the dang time.
	 *
	 * @param  array|string $array_or_string
	 */
	private function to_array( &$array_or_string ) {

		if( is_string( $array_or_string ) && strpos($array_or_string, ',') ) {
			$array_or_string = explode(',', $array_or_string);
			$array_or_string = array_map('trim', $array_or_string);
		} elseif ( is_numeric($array_or_string) || is_string($array_or_string) ) {
			$array_or_string = array(trim($array_or_string));
		} elseif ( is_array($array_or_string) ) {
			// nuttin'
		} else {
			QQ_Utils::warn('ARR_CONVERSION');
			return;
		}

 		if( count($array_or_string) === 0 ) {
 			QQ_Utils::warn('EMPTY_ARR');
 		}

	}

	/**
	 * Get one post object using a unique identifier
	 * @param  string/int 	$post_id_or_slug an int post ID or string post_name
	 * @return WP_Post
	 */
	public static function get( $post_id_or_slug ) {

		if(is_string( $post_id_or_slug )) {
			global $wpdb;
			$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s", $post_id_or_slug ));
		} else {
			$post_id = $post_id_or_slug;
		}

		// get_post will only return a WP_Post object OR null
		$post = get_post( $post_id );

		if(! ($post instanceof WP_Post) ) {
			return false;
		}

		if( class_exists('acf') ) {
			$posts = QQuery::acf_filter( array($post) );
			$post = $posts[0];
		}
		// $posts = $this->meta_filter( [$post] );
		// $posts = apply_filters('wp_ups_query_go_posts', $posts);

		// $this->comments_params['post_id'] = $post->ID;
		// $post->comments = get_comments( $this->comments_params );

		return $post;
	}

	/**
	 * the id() method emulates the behavior of WP_Query by providing an interface for
	 * users to feed in one or more IDs for a post__in query; just uses 'p=[id]' if single ID
	 *
	 * @param  int|array|string $ids
	 * @return current QQuery instance
	 */
	public function id( $ids ) {

		if(!$ids) {
			QQ_Utils::warn('EMPTY_SET');
		}

		$this->to_array( $ids );

		if(count($ids) > 1) {
			return $this->in($ids);
		} else {
			$this->query_assoc['p'] = $ids[0];
		}
		return $this;
	}

	/**
	 * in adds a filter to WP_Query for post__in
	 * @param  string|array $ids array may be a comma-delimited string or an PHP array
	 * @return current QQuery instance
	 */
	public function in( $ids ) {
		// if(is_string($array_of_ids)) {
		// 	$array_of_ids = explode(',', $array_of_ids);
		// }

		$this->to_array( $ids );
		$this->query_assoc['post__in'] = $ids;

		return $this;
	}

	/**
	 * sort accepts an orderby value for WP_Query
	 * @param  string $orderby Sort retrieved posts by parameter. Defaults to 'date'. One or more options can be passed
	 * @param  string $sort    Designates the ascending or descending order of the 'orderby' parameter. Defaults to 'DESC'
	 * @return current QQuery instance
	 */
	public function sort($orderby = 'date', $sort = 'DESC'){
		$this->query_assoc['orderby'] = $orderby;
		$this->query_assoc['order'] = $sort;
		return $this;
	}

	/**
	 * exclude posts from a set of provided IDs
	 * @param  string|array $ids array may be a comma-delimited string or an PHP array
	 * @return current QQuery instance
	 */
	public function exclude( $ids ){
		// if (is_string($exclude_ids) || is_numeric($exclude_ids)){
			// $arr = array($exclude_ids);
			$this->to_array( $ids );
		// }
		$this->query_assoc['post__not_in'] = $ids;
		return $this;
	}

	/**
	 * all is a shortcut for ppp(-1), which attempts to return ALL posts requested,
	 * without regard for pagniation
	 * @return current QQuery instance
	 */
	public function all() {
		return $this->ppp(-1);
	}

	/**
	 * set or limit the posts per page. Setting this to 0 will result in a warning
	 * @param  int $posts_per_page the number of posts per page to set
	 * @return current QQuery instance
	 */
	public function ppp( $posts_per_page ) {
		if($posts_per_page == 0) {
			QQ_Utils::warn('EMPTY_SET');
		}
		$this->query_assoc['posts_per_page'] = $posts_per_page;
		return $this;
	}

	// public function page($page_number) { // which page are we on..?
	// 	if(isset($this->query_assoc['offset'])){
	// 		trigger_error('UPS-Query: using `offset` with `paged` may produce unintended results', E_WARNING);
	// 	}
	// 	$this->query_assoc['paged'] = $page_number;
	// 	return $this;
	// }

	// public function offset( $offset ) {
	// 	if(isset($this->query_assoc['paged'])){
	// 		trigger_error('UPS-Query: using `offset` with `paged` may produce unintended results', E_WARNING);
	// 	}
	// 	$this->query_assoc['offset'] = $offset;
	// 	return $this;
	// }

	/**
	 * type() specifies the type(s) of posts you'd like to query for
	 * @param  string|array $post_type accepts a comma-delimited string, a string, or an array of
	 *                                 post_types. default: 'any'
	 * @return current QQuery instance
	 */
	public function type( $post_type ) {
		$this->to_array( $post_type );
		$this->query_assoc['post_type'] = $post_type;
		return $this;
	}

	// /**
	//  * slug(string) -
	//  */

	// public function slug( $slug ) {
	// 	$this->query_assoc['name'] = $slug;
	// 	return $this;
	// }

	// public function status($status) {
	// 	$this->query_assoc['post_status'] = $status;
	// 	return $this;
	// }

	// /** CATEGORIES

	// 	cat (int) - use category id.
	// 	category_name (string) - use category slug (NOT name).
	// 	category__and (array) - use category id.
	// 	category__in (array) - use category id.
	// 	category__not_in (array) - use category id.
	// */

	// public function category( $id_or_name, $recursive = false ) {
	// 	if(!is_numeric( $id_or_name )) {
	// 		$cat_id = get_cat_ID( $id_or_name );
	// 		$this->query_assoc['category_name'] = $id_or_name;
	// 	}
	// 	if($recursive) {
	// 		return $cat_id;
	// 	} else {
	// 		$this->query_assoc['cat'] = $cat_id;
	// 	}
	// 	return $this;
	// }

	// public function categories( $arr_of_ids_or_names ) {
	// 	$categories = [];
	// 	foreach($arr_of_ids_or_names as $id_or_name) {
	// 		array_push($categories, $this->category($id_or_name, true));
	// 	}

	// 	$this->query_assoc['cat'] = implode($categories, ',');

	// 	return $this;
	// }

	// /** AUTHORS

	// 	author (int) - use author id.
	// 	author_name (string) - use 'user_nicename'.
	// 	author__in (array) - use author id. (NOT implemented)
	// 	author__not_in (array) - use author id. (NOT implemented)
	// */

	// public function author( $id_or_name ) {
	// 	if( !is_numeric( $id_or_name ) ) {
	// 		$user = get_user_by( 'slug', $id_or_name );
	// 		$id_or_name = $user->ID;
	// 	}
	// 	if( isset($this->query_assoc['author']) && is_array($this->query_assoc['author']) ) {
	// 		array_push($this->query_assoc['author'], $id_or_name);
	// 	} else {
	// 		$this->query_assoc['author'] = $id_or_name;
	// 	}
	// 	// array_push($this->authors, $id_or_name);
	// 	return $this;
	// }

	// public function authors( $arr_of_ids_or_names ) {
	// 	$this->query_assoc['author'] = array();
	// 	foreach($arr_of_ids_or_names as $id_or_name) {
	// 		$this->author($id_or_name);
	// 	}
	// 	return $this;
	// }

	// /**
	//  * TAXONOMY
	//  */
	// public function taxonomy( $taxonomy_id_or_name ) {
	// 	// assume just a taxonomy name for now
	// 	$this->query_assoc['tax_query'] = array(
	// 		'taxonomy' => $taxonomy_id_or_name
	// 	);
	// 	return $this;
	// }

	// /**
	//  *
	//  * @param  [array] $tax_array see example format:
	//  *
	//  * array(
	//  * 		array(
	//  *   		'[taxonomy slug]',
	//  *     		'[term slug]'
	//  *      ), array(
	//  *      	'industry',
	//  *       	'government'
	//  *      )
	//  * )
	//  */
	// public function tags( $tax_array ) {

	// 	// group the received terms by taxonomy
	// 	$taxonomies = array();
	// 	foreach($tax_array as $raw) {

	// 		if(count($raw) !== 2) {
	// 			continue;
	// 		}

	// 		if(!is_array($taxonomies[$raw[0]])) {
	// 			$taxonomies[$raw[0]] = array();
	// 		}

	// 		$taxonomies[$raw[0]][] = $raw[1];
	// 	}

	// 	// now make the tax_query!
	// 	$tax_query = array('relation'=>'OR');
	// 	foreach($taxonomies as $taxonomy=>$terms) {
	// 		$tmp = array(
	// 			'taxonomy' => $taxonomy,
	// 			'terms' => $terms,
	// 			'field' => 'slug',
	// 			'include_children' => false
	// 		);
	// 		$tax_query[] = $tmp;
	// 	}

	// 	$this->query_assoc['tax_query'] = $tax_query;
	// 	return $this;
	// }

	// /**
	//  * EXTENDED DATA
	//  */
	// public function include_meta($meta_fields) {

	// 	if(!is_array($meta_fields)) {

	// 		if(strpos($meta_fields, ',') !== false) {
	// 			$meta_fields = explode($meta_fields, ',');
	// 		} else {
	// 			$meta_fields = [$meta_fields];
	// 		}

	// 	}

	// 	$this->meta_fields = array_merge($this->meta_fields, $meta_fields);

	// 	return $this;
	// }

	// /**
	//  * META FILTRATION
	//  *
	//  * supports:
	//  *
	//  * 	author - fetches author data per post
	//  * 	thumbnail - fetches thumbnail data per post
	//  */

	// private function meta_filter($posts) {
	// 	global $wpdb;

	// 	if(in_array('author', $this->meta_fields)) {

	// 		$headshot = get_option('iapp_headshot_url');

	// 		foreach($posts as $post) {

	// 			$post->authors = array();


	// 			if( isset($authors[$post->post_author]) ) {
	// 				$author_meta = $authors[$post->post_author];
	// 			} else {

	// 				$author_meta_raw = $wpdb->get_results( "select u.ID, m.meta_key, m.meta_value
	// 														from wp_usermeta m
	// 														  inner join wp_users u on u.ID = m.user_id
	// 														  inner join wp_terms t on t.name = u.user_email
	// 														  inner join wp_term_taxonomy x on x.term_id = t.term_id
	// 														  inner join wp_term_relationships r on r.term_taxonomy_id = x.term_taxonomy_id
	// 														where r.object_id = " . $post->ID . " and u.ID <> 1" );

	// 				$author_meta = array();
	// 				foreach($author_meta_raw as $meta_obj) {
	// 					if(!isset($post->authors[$meta_obj->ID])){
	// 						$post->authors[$meta_obj->ID] = array();
	// 						$post->authors[$meta_obj->ID]["test"] = "hey";
	// 					}
	// 					if($meta_obj->meta_key == "contact_id") {
	// 						$contact_id = $meta_obj->meta_value;
	// 						$post->authors[$meta_obj->ID]["permalink"] = home_url() . '/about/person/'.trim($contact_id);
	// 						$post->authors[$meta_obj->ID]["headshot"] = str_replace("{id}", trim($contact_id), $headshot);
	// 					}

	// 					$post->authors[$meta_obj->ID][$meta_obj->meta_key] = $meta_obj->meta_value;
	// 				}
	// 				$author_meta['ID'] = $post->post_author;
	// 			}
	// 		}
	// 	}

	// 	if (in_array('thumbnail', $this->meta_fields)) {
	// 		foreach($posts as $post){
	// 			$thumb_id = get_post_thumbnail_id($post->ID);
	// 			$url = wp_get_attachment_url($thumb_id);
	// 			$post->thumbnail = $url;
	// 		}
	// 	}

	// 	if (in_array('children', $this->meta_fields)) {
	// 		foreach($posts as $post) {
	// 			$post->children = get_children(array(
	// 				'post_parent' => $post->ID,
	// 				'post_type'   => $this->child_type,
	// 				'posts_per_page' => -1,
	// 				'post_status' => 'published'
	// 			));
	// 		};
	// 	}

	// 	if (in_array('terms', $this->meta_fields)){
	// 		foreach($posts as $post){
	// 			$taxs = get_object_taxonomies($post->post_type);
	// 			$terms = array();
	// 			foreach ($taxs as $tax) {
	// 				$terms[] = wp_get_post_terms($post->ID, $tax);
	// 			}
	// 			$post->terms = $terms;
	// 		}
	// 	}

	// 	return $posts;
	// }

	// public function parent($parent_id){
	// 	$this->query_assoc['post_parent'] = $parent_id;
	// 	return $this;
	// }

	// public function children($type='any') {
	// 	$this->child_type = $type;
	// 	$this->meta_fields[] = 'children';
	// 	return $this;
	// }

	/** PRIVATE */

	/**
	 * acf_filter fetches all of the ACF data for a given post
	 *
	 * @param  array $posts one or more WP_Post objects
	 * @return array        [description]
	 */
	private static function acf_filter( $posts ) {

		foreach($posts as $post) {
			$fields = get_fields($post->ID);
			$tmp = new stdClass();
			foreach($fields as $field_name=>$field_value) {
				$property_name = $field_name;
				$tmp->$property_name = $field_value;
			}
			$post->acf = $tmp;
		}

		return $posts;
	}

	/**
	 * rest the default values for this instance of QQ
	 */
	private function reset() {
		wp_reset_query();
		$this->query_assoc = $this->default_assoc;
		// $this->meta_fields = array();
	}

	// /** RUN THE QUERY */

	public function go() {

		$query = new WP_Query($this->query_assoc);
		if($this->uses_acf) {
			$posts = $this->acf_filter($query->posts);
		} else {
			$posts = $query->posts;
		}
		// $posts = $this->meta_filter($posts);
		// $posts = apply_filters('wp_ups_query_go_posts', $posts);

		// if(count($posts) == 1) {
		// 	$post = $posts[0];
		// 	// $post->comments = get_comments( $post->ID );
		// 	return $post;
		// }

		// print_r($query->request . "\n\n");

		$this->reset();
		return $posts;
	}

}
