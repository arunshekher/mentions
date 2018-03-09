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
	 * Converts valid user mention to user profile-link
	 *
	 * @param string $mention
	 *  User mention string
	 *
	 * @return string
	 *  User mention profile-link or string prepended with '@'
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
	 * Get user data from database
	 *
	 * @param string $mention
	 *  String prepended with '@' which the parsing logic captured.
	 *
	 * @return array
	 *  User details from 'user' table - user_id, user_name, user_email
	 */
	protected function getUserData($mention)
	{
		$username = e107::getParser()->toDB($this->stripAtFrom($mention));
		$row = e107::getDb()->retrieve("user", "user_name, user_id, user_email",
			"user_name = '" . $username . "' ");

		return $row;
	}


	/**
	 * Strips '@' sign from mention string
	 *
	 * @param string $mention
	 *  String prepended with '@'.
	 * @return string
	 *  String striped clean of '@'
	 */
	protected function stripAtFrom($mention)
	{
		return ltrim($mention, '@');
	}


	/**
	 * Does Debug logging
	 *
	 * @param string|array $content
	 *  The data to be logged - can be passed as string or array.
	 * @param string $logname
	 *  The name of log that need to be written to file-system.
	 */
	protected function log($content, $logname = 'mentions')
	{
		$path = e_PLUGIN . 'mentions/' . $logname . '.txt';

		if (is_array($content)) {
			$content = var_export($content, true);
		}

		file_put_contents($path, $content . "\n", FILE_APPEND);
		unset($path, $content);
	}


}
