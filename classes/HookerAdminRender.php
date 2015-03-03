<?php
namespace WPHooker\Classes;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
* Class for rendering Hooker Admin pages, extends Admin Page framework class
*/
class HookerAdminRender
{  
	/**
	 * Hook all necessary hooks on construct
	 */
	function __construct()
   {
   		add_action( 'edit_form_after_title', array($this, 'outputList') );
   		add_action('do_meta_boxes' , array($this, 'removeMetaBoxes'));
		add_filter('post_row_actions', array($this, 'removeOptions'),10,2);
   }
   /**
    * Removes post unneeded actions for the wp_hooker post type
    * @param  array $actions Wordpress post actions etc.
    * @return array          Return the changed array
    */
   public function removeOptions($actions)
   {
   		global $post;
	    if( $post->post_type == 'wp_hooker' ) {
			unset($actions['inline hide-if-no-js']);
			unset($actions['edit']);
			unset($actions['view']);
		}
	    return $actions;
   }
   /**
    * Removes the submitdiv meta box (the box with the publish button etc.)
    * @return void
    */
   public function removeMetaBoxes()
   {
   		global $post;
	    if( isset($post->post_type) && $post->post_type == 'wp_hooker' ) {
   			remove_meta_box( 'submitdiv','wp_hooker','side' );
   		}
   }
   /**
    * Outputs the session data in a structured table on the edit page
    * @return void
    */
   public function outputList()
   {
   		global $post;
   		if($post->post_type !== 'wp_hooker')
   			return;

   		$sessionData = get_post_meta( $post->ID, '_session_data', true);
   			
   		if(!empty($sessionData)) {
   			?>
   			<table class="widefat">
				<thead>
					<tr>
						<th class="row-title"><?php echo __('Time From Start', 'wp_hooker'); ?></th>
						<th><?php echo __('Hook Name', 'wp_hooker'); ?></th>
						<th><?php echo __('Hooked Functions', 'wp_hooker'); ?></th>
					</tr>
				</thead>
				<tbody>
   			<?php
   			$order = 1;
   			$startTime = current(array_keys($sessionData));
			$startTime = substr($startTime, 0, strpos($startTime, '-'));

   			foreach ($sessionData as $time => $hook) :
   				$funcData = unserialize(base64_decode($hook[1]));
   				?>
   					<tr <?php if($order % 2 == 0) echo "class='alternate'"; ?> >
   						<td class="row-title"><?php echo floor((substr($time, 0, strpos($time, '-')) - $startTime) * 1000) . ' ms'; ?></td>
						<td><?php echo $hook[0] ?></td>
						<td>
							<ul>
							<?php 
							if(!empty($funcData)) {
								foreach($funcData as $priority => $func) { 
									$funcData = array_values($func)[0]['function'];
									
									if(is_array($funcData)) {
										$convert = (array) $funcData[0];
										printf("<li> %s [Object] (%d)</li>", get_class((object) $funcData[0]), $priority); 
										if(count($convert) > 0) {
											echo "---- START OF OBJECT ----";
											echo "<ul>";
											foreach ($convert as $key => $value) {
												$value = (!is_array($value)) ? $value : '[Array]';

												if(is_object($value))
													$value = '[Object]';
												
												printf("<li>%s => %s </li>", $key, $value);
											}
											echo "</ul>";
											echo "---- END OF OBJECT ----";
										}
										
									} else {
										printf("<li>%s (%d)</li>",  $funcData, $priority);
									} 
								}
							}
							?>
							</ul>
						</td>
					</tr>
   				<?php
   				$order++;
   			endforeach;
   			?>
   				</tbody>
   				<tfoot>
					<tr>
						<th class="row-title"><?php echo __('Time From Start', 'wp_hooker'); ?></th>
						<th><?php echo __('Hook Name', 'wp_hooker'); ?></th>
						<th><?php echo __('Hooked Functions', 'wp_hooker'); ?></th>
					</tr>
				</tfoot>
			</table>
   			<?php
   		} else {
   			echo __('<h2>No data was found!</h2>', 'wp_hooker');
   		}

   }
}
?>