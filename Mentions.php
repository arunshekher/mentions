<?php
if ( ! defined('e107_INIT')) {
	exit;
}


abstract class Mentions
{

	protected $prefs;
	protected $parse; // todo: can be removed

	protected $splitRegEx;
	protected $matchRegEx;


	/**
	 * Mentions constructor.
	 */
	public function __construct()
	{
		$this->prefs = e107::getPlugPref('mentions');
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

		$row = e107::getDb()->retrieve('user', 'user_name, user_id, user_email',
			"user_name = '{$username}' ");

		return $row;
	}


	/**
	 * Strips '@' sign from mention string
	 *
	 * @param string $mention
	 *  String prepended with '@'.
	 *
	 * @return string
	 *  String striped clean of '@'
	 */
	protected function stripAtFrom($mention)
	{
		return ltrim($mention, '@');
	}


	/**
	 * Returns RegEx pattern to split text in a consumable manner
	 *
	 * @return string
	 */
	protected function obtainSplitPattern()
	{
		if ($this->prefs['support_v1_chars']) {

			return '/(^|\w*@\s*[\p{L}\p{N}._#~*]+)/mu';

		}

		return '/(^|\p{L}*@\s*[\p{L}\p{N}._]+)/mu';
	}


	/**
	 * Returns RegEx pattern to match username including '@' symbol
	 *
	 * @return string
	 */
	protected function obtainMatchPattern()
	{
		if ($this->prefs['support_v1_chars']) {

			return '/@([\p{L}\p{N}_.#~*@!]{2,100})/u';

		}

		return '/(@[\p{L}\p{N}_.]{2,100})/u';
	}


	/**
	 * Does Debug logging by writing a log file to the plugin directory
	 *
	 * @param string|array $content
	 *  The data to be logged - can be passed as string or array.
	 * @param string       $logname
	 *  The name of log that need to be written to file-system.
	 */
	protected function log($content, $logname = 'mentions')
	{
		$path = e_PLUGIN . 'mentions/logs/';

		if ( ! file_exists($path)) {
			mkdir($path, 0777, true);
		}

		$fileName = $path . $logname . '.txt';

		if (is_array($content)) {
			$content = var_export($content, true);
		}

		file_put_contents($fileName, $content . PHP_EOL, FILE_APPEND);
	}


}
