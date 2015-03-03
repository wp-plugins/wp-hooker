<?php
use WPHooker\Classes\HookerSettings;
use WPHooker\Classes\HookerPostTypes;
use WPHooker\Classes\HookerAdminRender;
/*
Plugin Name: WP Hooker
Plugin URI: http://www.innovator.se
Description: WP Hooker records all fired WP hooks and their hooked functions, to easily let you debug hook issues!
Version: 1.0.1
Author: Innovator Digital Markets AB
Author URI: http://www.innovator.se
Text Domain: wp-hooker
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once( 'classes/HookerSettings.php');
require_once( 'classes/HookerPostTypes.php');
require_once( 'classes/HookerAdminRender.php');

/**
 * Main Class for WP Hooker
 */
class WPHooker
{
	/**
	 * Instance variable
	 * @var object
	 */
	private static $_instance;
	/*
	 * Class variables
	 */
	private $hooks,
			$hooksInfo,
			$settings,
			$hookLog;
	
	function __construct()
	{
		
		//add_action( 'wp_enqueue_scripts', array($this, 'enqueueScripts') );
	}
	/**
	 * Initiate all necessary functions for WP-Hooker
	 * @return void 
	 */
	public function init()
	{
		global $wp_filter;

		$this->settings = new HookerSettings();

		HookerPostTypes::init();

		$this->hooksInfo = $wp_filter;
		$this->hooks = array_keys($wp_filter);
		// Run only if status is set to active
		if($this->settings->getOption('hookerEnabled') == 1) {
			for ($i=0; $i < count($this->hooks); $i++) { 
				if($this->hooks[$i] !== 'init')
					add_filter( $this->hooks[$i], __CLASS__ . '::execLog', 0);
			}
			add_action( 'shutdown', __CLASS__ . '::execSave' );
		}

		// Only run if in admin
		if(is_admin())
			new HookerAdminRender();
	}
	

	/**
	 * Check if an instance of the class exists, and either creates a new one or returns the existing one
	 * @return object returns an instance of the class
	 */
	public static function getInstance() {
	  if ( ! isset( self::$_instance ) ) {
	    self::$_instance = new WPHooker();
	  }
	  return self::$_instance;
	}

	/**
	 * Logs the submitted hookname to the class variable $hookLog
	 * @param  string $hookname name of the hook to be logged
	 * @return void
	 */
	private function logger($hookName='')
	{
		
		if(!empty($hookName)) {
			
			$this->hookLog[microtime(true) . '-' . uniqid()] = 
			array(
				$hookName,
				base64_encode(serialize($this->hooksInfo[$hookName]))
			);
		}	
	}

	/**
	 * Hook function for logger
	 * @param  string $value optional return value for filters
	 * @return any           Returns any submitted parameter 
	 */
	public static function execLog($value='')
	{
		// Get current hook
		$currentHook = current_filter();
		// Log to class
		self::getInstance()->logger($currentHook);
		// Return any passed variable
		return $value;
	}

	/**
	 * Saves the session data to WP
	 * @return void
	 */
	public function saveToWP()
	{

		// Generate a unique ID for the session
		$sessionId = uniqid();
		$post = array(
			'post_name'      => $sessionId,
			'post_title'     => $_SERVER['REQUEST_URI'] . ' &mdash; [Session: ' . $sessionId . ']',
			'post_status'    => 'publish', // Default 'draft'.
			'post_type'      => 'wp_hooker'		
		);
		// Save session data as post
		$postId = wp_insert_post($post);
		add_post_meta( $postId, '_session_data', $this->hookLog, true );
		
	}

	/**
	 * Hook function for saveToWP
	 * @return void
	 */
	public static function execSave()
	{
		self::getInstance()->saveToWP();
	}

	public function enqueueScripts()
	{
		wp_enqueue_style( 'hooker-joint-style', plugins_url('assets/css/joint.min.css', __FILE__ ), array() );
		wp_enqueue_script( 'hooker-joint', plugins_url('assets/js/lib/joint.min.js', __FILE__ ), array() );
		wp_enqueue_script( 'hooker-joint-erd', plugins_url('assets/js/lib/joint.shapes.erd.min.js', __FILE__ ), array('hooker-joint') );
		wp_enqueue_script( 'hooker-joint-diagram', plugins_url('assets/js/diagram.js', __FILE__ ), array('hooker-joint', 'jquery'), '1.0', true );
		global $wp_filter;
		wp_localize_script( 'hooker-joint-diagram', 'wpHookers', $wp_filter);
	}
}
/**
 * Initiate WPHooker
 * @return void
 */
function initWPHooker()
{
	WPHooker::getInstance()->init();
}
add_action( 'init', 'initWPHooker' );
?>