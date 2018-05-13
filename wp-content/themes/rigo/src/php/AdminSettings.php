<?php

namespace Rigo;
use \WPAS\Settings\WPASThemeSettingsBuilder;
use TF\Types\CoursePostType;
use TF\Types\WorkshopPostType;
class AdminSettings {
	
	private $wpts;
	function __construct() {
		$generalFields = [
				[
				    'type' => 'select', 
				    'label' => 'Mantainance Mode',
				    'options' => [
				    	'active' => 'Active',
				    	'innactive' => 'Innactive'
				    ],
				    'text' => 'Active',
				    'name' => 'mantainance-mode',
					'description' => 'Will block the calendar and display a mantainance mode screen'
				]
			];
		
		/*
		* WPTS
		*/
		$this->wpts = new WPASThemeSettingsBuilder(
			array(
				'general' => array(
					'description' => 'Poker Society Options',
					'menu_slug' => 'ps_theme_options',
					'menu_title' => 'Theme Settings'
				),
				'settingsID' => 'wp_theme_settings',
				'settingFields' => array('wp_theme_settings_title'), 
				'tabs' => array(
					'general' => array('text' => 'General', 'dashicon' => 'dashicons-admin-page', 'tabFields' => $generalFields)
				),
			)
		);
		
	}

}