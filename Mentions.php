<?php
if ( ! defined('e107_INIT')) {
	exit;
}

class Mentions
{

	protected $prefs;
	protected $parse;

	protected $dataVars = [];

	/**
	 * Mentions constructor.
	 */
	public function __construct()
	{
		$this->prefs = e107::getPlugPref('mentions');
		$this->parse = e107::getParser();
	}


	/**
	 * Magic set
	 * @param $name
	 * @param $value
	 */
	protected function __set($name, $value)
	{
		$this->set($name, $value);
	}


	/**
	 * Magic get
	 * @param $name
	 *
	 * @return mixed|null
	 */
	protected function __get($name)
	{
		return $this->get($name);
	}


	/**
	 * Magic isset
	 *
	 * @param $name
	 *
	 * @return bool
	 */
	protected function __isset($name)
	{
		if (property_exists($this, $name)) {
			return isset($this->$name);
		}

		if (array_key_exists($name, $this->dataVars)) {
			return isset($this->dataVars[$name]);
		}
	}


	/**
	 * Magic unset
	 * @param $name
	 */
	protected function __unset($name)
	{
		if (property_exists($this, $name)) {
			unset($this->$name);
		} elseif (array_key_exists($name, $this->dataVars)) {
			unset($this->dataVars[$name]);
		}
	}

	/**
	 * Gets property
	 * @param $name
	 *
	 * @return mixed|null
	 */
	protected function get($name)
	{
		if (property_exists($this, $name)) {
			return $this->$name;
		}

		if (array_key_exists($name, $this->dataVars)) {
			return $this->dataVars[$name];
		}

		return null;
	}


	/**
	 * Sets property
	 * @param $name
	 * @param $value
	 */
	protected function set($name, $value)
	{
		if (property_exists($this, $name)) {
			$this->$name = $value;
		}

		$this->dataVars[$name] = $value;

	}




	/**
	 * Converts mention to user profile link if user matched exists in database
	 *
	 * @param $mention
	 *
	 * @return string
	 */
	protected function createUserLinkFrom($mention)
	{
		$data = $this->getUserData($mention);

		if ($data['user_name'] === $this->stripAtFrom($mention)) {
			$userData =
				['id' => $data['user_id'], 'name' => $data['user_name']];
			$link = e107::getUrl()->create('user/profile/view', $userData);

			return '<a href="' . $link . '">' . $mention . '</a>';
		}

		return $mention;
	}


	/**
	 * Get user data drom database
	 *
	 * @param $mention
	 *
	 * @return array
	 */
	protected function getUserData($mention)
	{
		$username = e107::getParser()->toDB($this->stripAtFrom($mention));
		$row = e107::getDb()->retrieve("user", "user_name, user_id, user_email",
			"user_name = '" . $username . "' ");

		return $row;
	}


	/**
	 * Strips '@' sign from mention
	 *
	 * @param $mention
	 *
	 * @return string
	 */
	protected function stripAtFrom($mention)
	{
		return ltrim($mention, '@');
	}


	/**
	 * Does Debug logging
	 *
	 * @param string $content
	 * @param string $logname
	 */
	protected function log($content, $logname = 'mentions')
	{
		$path = e_PLUGIN . 'mentions/' . $logname . '.txt';
		file_put_contents($path, $content . "\n", FILE_APPEND);
		unset($path, $content);
	}


}
