<?php
if ( ! defined('e107_INIT')) {
	exit;
}


abstract class Mentions
{

	/**
	 * Split text regex - v1 upgraded sites
	 * --------------------------------------
	 */

	const SPLIT_REGEX_V1 = '/(^|[\pL\pN\pM]*@\s*[\pL\pN\pM._-~#!@*]+)/u';
	/**
	 * Split text regex - v2 sites
	 * --------------------------------------
	 */

	const SPLIT_REGEX_V2 = '/(^|[\pL\pN\pM]*@\s*[\pL\pN\pM._]+)/u';
	/**
	 * Match username regex - v1 upgraded sites
	 * --------------------------------------
	 */

	const MATCH_REGEX_V1 = '/@([\pL\pN\pM._-~#!@*]+)/u';
	/**
	 * Match username regex - v2 sites
	 * --------------------------------------
	 */

	const MATCH_REGEX_V2 = '/@([\pL\pN\pM._]+)/u';
	/**
	 * Split text regex fallback - v1 upgraded sites
	 * --------------------------------------
	 */

	const SPLIT_REGEX_V1_FALLBACK = '/(^|\w*@\s*[a-z0-9._#~*]+)/mui';
	/**
	 * Split text regex fallback - v2 sites
	 * --------------------------------------
	 */

	const SPLIT_REGEX_V2_FALLBACK = '/(^|\w*@\s*[a-z0-9._]+)/mui';
	/**
	 * Match username regex fallback - v1 upgraded sites
	 * --------------------------------------
	 */

	const MATCH_REGEX_V1_FALLBACK = '/@([\w\X._-~#!@*]+)/u';
	/**
	 * Match username regex fallback - v1 upgraded sites
	 * --------------------------------------
	 */
	const MATCH_REGEX_V2_FALLBACK = '/@([\w\X._]+)/u';


	/**
	 * Plugin preference
	 *
	 * @var mixed
	 */
	protected $prefs;

	private $userId;
	private $userName;
	private $userData;


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
	 * @param array $mention
	 *  User mention match array
	 *
	 * @return string
	 *  User mention profile-link or string prepended with '@'
	 */
	protected function createUserLinkFrom($mention)
	{
		$userMention = $mention[0];
		$userName = $mention[1];

		$data = $this->getUserData($userName);

		if ($data['user_name'] === $userName) {

			$userData =
				['id' => $data['user_id'], 'name' => $data['user_name']];

			$link = e107::getUrl()->create('user/profile/view', $userData);

			return '<a href="' . $link . '">' . $userMention . '</a>';
		}

		return $userMention;
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
		$sql = e107::getDb();
		$username = e107::getParser()->toDB($mention);

		$row = $sql->retrieve('user', 'user_name, user_id, user_email',
			"user_name = '{$username}' ");

		if ($row) {
			return $row;
		}
		return $sql->retrieve('user', 'user_name, user_id, user_email',
					"user_name LIKE '%{$username}%' ");
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
	protected function obtainSplitRegEx()
	{
		if ($this->prefs['support_v1_chars']) {

			return self::SPLIT_REGEX_V1;

		}

		return self::SPLIT_REGEX_V2;
	}


	/**
	 * Returns RegEx pattern to match username including '@' symbol
	 *
	 * @return string
	 */
	protected function obtainMatchRegEx()
	{
		if ($this->prefs['support_v1_chars']) {

			return self::MATCH_REGEX_V1;

		}

		return self::MATCH_REGEX_V2;
	}


	/**
	 * @return string
	 */
	protected function obtainSplitRegExFallback()
	{
		if ($this->prefs['support_v1_chars']) {

			return self::SPLIT_REGEX_V1_FALLBACK;

		}

		return self::SPLIT_REGEX_V2_FALLBACK;
	}


	/**
	 * @return string
	 */
	protected function obtainMatchRegExFallback()
	{
		if ($this->prefs['support_v1_chars']) {

			return self::MATCH_REGEX_V1_FALLBACK;

		}

		return self::MATCH_REGEX_V2_FALLBACK;
	}


	/**
	 * Does Debug logging by writing a log file to the plugin directory
	 *
	 * @param mixed $content
	 *  The data to be logged - can be passed as string or array.
	 * @param string       $logname
	 *  The name of log that need to be written to file-system.
	 */
	protected function log($content, $logname = 'mentions')
	{
		$path = e_PLUGIN . 'mentions/logs/';

		if ( ! file_exists($path) && ! mkdir($path, 0777,
				true) && ! is_dir($path)) {
					throw new \RuntimeException(sprintf('Directory "%s" was not created',
						$path));
				}

		$fileName = $path . $logname . '.txt';

		if (is_array($content) || is_object($content)) {
			$content = var_export($content, true);
		}

		file_put_contents($fileName, $content . PHP_EOL, FILE_APPEND);

	}


}
