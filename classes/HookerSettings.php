<?php
namespace WPHooker\Classes;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
* Settings class for WPHooker 
*/
class HookerSettings
{
	/**
	 * Container for currently active settings for WP Hooker
	 * @var object
	 */
	private $currentSettings;
	function __construct()
	{
		$this->currentSettings = get_option( 'hooker_settings' ); 

		add_action( 'admin_menu', array($this, 'hookerAddAdminMenu') );
		add_action( 'admin_init', array($this, 'hookerSettingsInit') );	
	}
	/**
	 * Return the specified if it exists, or false it not
	 * @param  string $option Name of the wanted option
	 * @return any         
	 */
	public function getOption($option='')
	{
		if( !empty($this->currentSettings[$option]) )
			return $this->currentSettings[$option];
		return false;
	}

	public function hookerAddAdminMenu(  ) { 

		add_menu_page( 'WP Hooker', 'WP Hooker Settings', 'manage_options', 'wp_hooker', array($this, 'hookerSettingsPage') );

	}


	public function hookerSettingsInit(  ) { 

		register_setting( 'HookerSettings', 'hooker_settings' );

		add_settings_section(
			'hooker_HookerSettings_section', 
			__( 'Unleash the hooker!', 'wp_hooker' ), 
			array($this, 'hookerSectionCallback'), 
			'HookerSettings'
		);

		add_settings_field( 
			'hookerEnabled', 
			__( 'Turn on/off the Hooker?', 'wp_hooker' ), 
			array($this, 'hookerEnabled'), 
			'HookerSettings', 
			'hooker_HookerSettings_section' 
		);


	}


	public function hookerEnabled(  ) { 

		$options = get_option( 'hooker_settings' );
		
		?>
		<input type='checkbox' name='hooker_settings[hookerEnabled]' <?php if(isset($options['hookerEnabled'])) checked( $options['hookerEnabled'], 1 ); ?> value='1'>
		<?php

	}


	public function hookerSectionCallback(  ) { 

	}


	public function hookerSettingsPage(  ) { 

		?>
		<form action='options.php' method='post'>
			
			<h2>WP Hooker</h2>
			
			<?php
			settings_fields( 'HookerSettings' );
			do_settings_sections( 'HookerSettings' );
			submit_button();
			?>
			
		</form>
		<?php

	}
}
?>