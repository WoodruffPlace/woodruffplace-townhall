<?php

class Settings
{
	private $settings;
	private $db;

	public function __construct()
	{
		$this->settings = $this->settings_get_all();
	}

	// Retrieve settings by name
	public function get($name)
	{
		$setting_value = array_find($this->settings, function($setting) use ($name)
		{
			return $setting["name"] === $name;
		});
		return($setting_value['setting']);
	}

	// Retrieve all settings
	public function settings_get_all()
	{
		global $db;
		$query = "SELECT * FROM settings";
		$result = $db->fetch_assoc($db->query($query));
		return $result;
	}
	// Retrieve all settings
	public function settings_retrieve()
	{
		return $this->settings;

	}

} // end class
