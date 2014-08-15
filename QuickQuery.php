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

	// default WP_Query config
	private $query_assoc = array();

	private $default_assoc = array(
		'post_type' => 'any',
		'post_status' => 'publish'
	);

	private $meta_fields = array();
	private $tax_query = array();


	public $uses_acf = false;

	// private $comments_params = array(
	// 		'status' => 'approve',
	// 	);

	/**
	 * set up defaults and set up flags for ACF
	 */
	public function __construct() {
		$this->reset();

		$this->all();

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

		if(! ($post instanceof WP_Post)) {
			return false;
		}

		if( class_exists('acf') ) {
			$posts = QQuery::acf_filter( [$post] );
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
		$this->query_assoc['nopaging'] = true;
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
		unset($this->query_assoc['nopaging']);
		$this->query_assoc['posts_per_page'] = $posts_per_page;
		return $this;
	}

	/**
	 * page returns a subset of selected posts based on posts-per-page and the page number
	 * @param  int $page_number number of posts to return per page
	 * @return current QQuery instance
	 */
	public function page($page_number) { // which page are we on..?
		if(isset($this->query_assoc['offset'])){
			QQ_Utils::warn('PAGING_AND_OFFSET_CONFLICT');
		}
		$this->query_assoc['paged'] = $page_number;
		return $this;
	}

	/**
	 * offsets set of returned posts by this number. Given [1,2,3,4,5,6], ppp = 3, an offset of 1 would return [2,3,4]
	 * @param  int $offset number by which to offset the posts
	 * @return current QQuery instance
	 */
	public function offset( $offset ) {
		if(isset($this->query_assoc['page'])){
			QQ_Utils::warn('PAGING_AND_OFFSET_CONFLICT');
		}
		$this->query_assoc['offset'] = $offset;
		return $this;
	}

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

	/**
	 * limit post selection to post status
	 * @param  string|array $status a string or array of post statuses
	 * @return current QQuery instance
	 */
	public function status($status) {
		$this->query_assoc['post_status'] = $status;
		return $this;
	}

	// /** CATEGORIES

	// 	cat (int) - use category id.
	// 	category_name (string) - use category slug (NOT name).
	// 	category__and (array) - use category id.
	// 	category__in (array) - use category id.
	// 	category__not_in (array) - use category id.
	// */

	/**
	 * interface for taxonomy queries that use the built-in 'categories' taxonomy
	 * @param  string|int $cat_id an integer ID or string slug that represent a category
	 * @return current QQuery instance
	 */
	public function category( $cat_id ) {

		// if(is_numeric( $cat_id )) {
		// 	$cat_id = get_term_by('id', $cat_id, 'category');
		// }

		return $this->tax( ['category'=>[$cat_id]] );
	}

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

	// sets tax() relationship
	public function _or() {
		return $this;
	}

	// sets tax() relationship
	public function _and() {
		return $this;
	}

	/**
	 * This is the most flexible means of interacting with WP_Query's tax_query attribute.
	 * I think what I'm going to have folks do is call tax() once per query they'd like to make and
	 * limit the arguments to one array of information.
	 *
	 * For example: $qq->tax(['pets'=>'kittens'])->_or()->tax('home_repair')->go();
	 *
	 * ...and not: $qq->tax(['some'=>['complicated', 'single'], 'argument'=>['in', 'this','space']], ['with'=>'options']);
	 *
	 * @param  string|array $t still in flux, may accept 'term_slug', ['term_slug'], [10,20,30],
	 *                         '10,20,30', ['term_slug', 20, 30], ['tax_name'=>['term_slug']], etc.
	 * @param  array $options  various tax_query overrides, not sure if this will stick around
	 * @return current QQuery instance
	 */
	public function tax($t, $options=array()) {
		global $wpdb;

		// print_r("\n\nORIGINAL \$t -------------");
		// print_r($t);

		/* accepts:
			- single term_id
			- single term slug (term_name)
			- array of term_ids (default relation: AND)
			- array of term slugs (default relation: AND)
			- nested array of [taxonomy=>terms()]
			- nested array of [taxonomy=>terms(),taxonomy=>terms(),taxonomy=>terms(),...]
			- any of these with an args relation of AND or OR
			- and of these with an args incl_children of TRUE or FALSE

			What's critical is that when we receive data, we should assume 'field'
			is going to either be ID or slug, and not a mix

			'relation' is only necessary if there's more than one taxonomy arr
		*/

		// seeing as how we're accepting mixed terms (like 'term', 3, '10')

		// convert string $t to an array, explode if necessary
		if (is_string($t)) {
			if(strpos($t, ',')) {
				$t = explode(',', $t);
			} else {
				$t = [$t];
			}
		} elseif (is_numeric($t)) {
			$t = [$t];
		}

		// now we have ['some_slug'], or [10], or ['some_slug', 'list', 30]

		// now convert contents of this array to slugs!
		foreach($t as $key=>$token) {
			if(is_array($token)) { // assume we have a ['tax'=>['terms']] relationship
				// loop over terms to convert to slugs

				// print_r("\$token\n");
				// print_r($token);

				foreach($token as $token_key=>$term) {
					if(is_numeric($term)) {
						$term = get_term_by('id', $term, $key);
					} else {
						$term = get_term_by('slug', $term, $key);
					}
					$t[$key][$token_key] = $term;
				}

			// otherwise, we don't know the taxonomy, so we have to get it...
			} elseif(is_numeric($token)) {

				$results = $wpdb->get_results("SELECT * FROM $wpdb->term_taxonomy AS wptt LEFT JOIN $wpdb->terms AS wpt ON wpt.term_id = wptt.term_id WHERE wpt.term_id = '" . $token . "'");

				if(!empty($results)) {
					$term = get_term_by('id', $token, $results[0]->taxonomy);
					$t[$key] = $term;
				} else {
					// TODO: throw warning, eh?
					QQ_Utils::warn('TERM_NOT_FOUND');
					unset($t[$key]); // remove the erroneous original value
				}

			} elseif(is_string($token)) {

				$results = $wpdb->get_results("SELECT * FROM $wpdb->term_taxonomy AS wptt LEFT JOIN $wpdb->terms AS wpt ON wpt.term_id = wptt.term_id WHERE slug = '" . $token . "'");

				// assume one instance of each term FOR NOW
				// print_r("RESULTS\n");
				// print_r($results[0]);

				$term = $results[0];

				$term = get_term_by('slug', $term->slug, $term->taxonomy);

				$t[$key] = $term;

			}
		}

		// print_r("\$t\n");
		// print_r($t);

		// if 1D arr
			// if single term slug, get ID for term
			// get tax for each term...
			// convert terms into tax=>term format

		// $defaults = array(
		// 	'relation'=>'AND',
		// 	'include_children'=>true
		// );

		// if(!empty($this->tax_query)) {
		// 	$this->tax_query = [];
		// } else {

		// }

		$query = [];

		foreach($t as $index=>$term) {

			if(is_numeric($index) && is_array($term) || is_object($term)) {

				// print_r("\n\$foreach T as TERM, NUMERIC || OBJECT\n");
				// print_r($term);

				$query[] = [
					'taxonomy' 	=> $term->taxonomy,
					'terms' 	=> $term->slug,
					'field' 	=> 'slug'
				];

			} elseif(is_array($term)) {

				// print_r("\nTHE OBJECT:\n");
				// print_r($term);
				// print_r("\n\n");

				foreach($term as $at) {
					$query[] = [
						'taxonomy' 	=> $at->taxonomy,
						'terms' 	=> $at->slug,
						'field' 	=> 'slug'
					];
				}

			} else {
				print_r("\n\$foreach T as TERM, ??????????????\n");
				print_r($t);
				print_r("\n\n");
			}

			// build query objects from each of these, combining terms where applicable
		}

		if(count($query) > 1) {
			if(array_key_exists('relation', $options)) {
				$query['relation'] = $options['relation'];
			} else {
				$query['relation'] = 'AND';
			}
		}

		// ignore chained tax() for now
		$this->query_assoc['tax_query'] = $query;

		// print_r("\n\$query\n");
		// print_r($query);

		return $this;
	}

	/**
	 * extend accepts a string or array of metadata properties to fetch additional data for
	 * @param  string|array $meta_fields
	 * @return current QQuery instance
	 */
	public function extend($meta_fields) {

		$this->to_array($meta_fields);
		$this->meta_fields = array_merge($this->meta_fields, $meta_fields);

		return $this;
	}

	// /**
	//  * META FILTRATION
	//  *
	//  * supports:
	//  *
	//  * 	author - fetches author data per post
	//  * 	thumbnail - fetches thumbnail data per post
	//  */

	/**
	 * meta_filter is used by QQuery to fetch data as id'd by the extend() method
	 * @param  array[WP_Post] $posts array of WP_Posts to extend based on contents of meta_fields arr
	 * @return array        		 modified array of WP_Post objects
	 */
	private function meta_filter($posts) {
		global $wpdb;

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

		if (in_array('terms', $this->meta_fields)){
			$taxonomies = get_taxonomies();

			// TODO: optimize this with one big query, maybe?
			// 			WHERE post.ID in [some,post,ids]

			foreach($posts as $post){

				$query = "SELECT *
							FROM $wpdb->term_relationships AS wptr
							LEFT JOIN $wpdb->term_taxonomy AS wptt
								ON wptr.term_taxonomy_id = wptt.term_taxonomy_id
							LEFT JOIN $wpdb->terms AS wpt
								ON wpt.term_id = wptt.term_id";

				$terms = $wpdb->get_results($query);

				if(!empty($terms)) {
					$post->terms = $terms;
				} else {
					$post->terms = new stdClass();
				}
			}
		}

		foreach(['terms'] as $value) {
			if(($key = array_search($value, $this->meta_fields)) !== false) {
			    unset($this->meta_fields[$key]);
			}
		}

		if(count($this->meta_fields) > 0) {
			QQ_Utils::warn('META_NOT_FOUND');
		}

		return $posts;
	}

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
	 * @return array        same set of WP_Post objects, but extended with ACF data (if any)
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
		$this->meta_fields = array();
	}

	// /** RUN THE QUERY */

	public function go() {

		$query = new WP_Query($this->query_assoc);
		if($this->uses_acf) {
			$posts = $this->acf_filter($query->posts);
		} else {
			$posts = $query->posts;
		}
		$posts = $this->meta_filter($posts);
		// $posts = apply_filters('wp_ups_query_go_posts', $posts);

		// if(count($posts) == 1) {
		// 	$post = $posts[0];
		// 	// $post->comments = get_comments( $post->ID );
		// 	return $post;
		// }

		// print_r($query);

		// print_r("\n\n" . $query->request . "\n\n");

		// print_r("\n\n" . count($posts) . " posts returned\n\n");

		$this->reset();
		return $posts;
	}

}
