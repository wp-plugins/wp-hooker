<?php
namespace WPHooker\Classes;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
* Class for registering and handling WP hooker's post type
*/
class HookerPostTypes
{
	/**
	 * Remove these features from the post type
	 * @var array
	 */
	private static $removeSupport = array(
	  	'editor',
	  	'author',
	  	'thumbnail',
	  	'excerpt',
	  	'trackbacks',
	  	'custom-fields',
	  	'comments',
	  	'revisions',
	  	'page-attributes',
	  	'post-formats'
	);

	function __construct()
	{
		
	}
	/**
	 * Init wp_hooker post type functions
	 * @return void
	 */
	public static function init()
	{
		self::registerPostType();
		//self::removePostTypeSupport();
	}
	/**
	 * Register wp_hooker post type
	 * @return void
	 */
	private static function registerPostType() {
	  register_post_type( 'wp_hooker',
	    array(
	      	'labels' => array(
	        	'name' => __( 'Hooker Sessions', 'wp_hooker' ),
	        	'singular_name' => __( 'Hooker Session', 'wp_hooker' )
	      	),
	      	'public' => true,
	      	'has_archive' => false,
	      	'supports' => array('title'),
	      	'capabilities' => array(
		        'create_posts' => false,
		    ),
		    'map_meta_cap' => true, 
	    )
	  );
	}
	/**
	 * Removes unneeded features for the wp_hooker post type
	 * @return void
	 */
	private static function removePostTypeSupport()
	{
		foreach (self::$removeSupport as $feature) {
	  		remove_post_type_support( 'wp_hooker', $feature );
	  	}
	}
}
?>