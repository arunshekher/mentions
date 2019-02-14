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

	const SPLIT_REGEX_V1 = '/(^|[\pL\pN\pM]*@\s*[\pL\pN\pM._-~#!@\*]+)/u';

	/**
	 * Split text regex - v2 sites
	 * --------------------------------------
	 */

	const SPLIT_REGEX_V2 = '/(^|[\pL\pN\pM]*@\s*[\pL\pN\pM._]+)/u';

	/**
	 * Match username regex - v1 upgraded sites
	 * --------------------------------------
	 */

	const MATCH_REGEX_V1 = '/@([\pL\pN\pM._-~#!@\*]+)/u';

	/**
	 * Match username regex - v2 sites
	 * --------------------------------------
	 */

	const MATCH_REGEX_V2 = '/@([\pL\pN\pM._]+)/u';

	/**
	 * Split text regex fallback - v1 upgraded sites
	 * --------------------------------------
	 */

	const SPLIT_REGEX_V1_FALLBACK = '/(^|\w*@\s*[a-z0-9._#~\*]+)/mui';

	/**
	 * Split text regex fallback - v2 sites
	 * --------------------------------------
	 */

	const SPLIT_REGEX_V2_FALLBACK = '/(^|\w*@\s*[a-z0-9._]+)/mui';

	/**
	 * Match username regex fallback - v1 upgraded sites
	 * --------------------------------------
	 */

	const MATCH_REGEX_V1_FALLBACK = '/@([\w\X._-~#!@\*]+)/ui';

	/**
	 * Match username regex fallback - v1 upgraded sites
	 * --------------------------------------
	 */
	const MATCH_REGEX_V2_FALLBACK = '/@([\w\X._]+)/ui';


	/**
	 * Plugin preference
	 *
	 * @var mixed
	 */
	protected $prefs;

	private $pcreCompatibility = false;


	/**
	 * Mentions constructor.
	 */
	public function __construct()
	{
		$this->setPrefs()->setPcreCompatibility();
	}


	private function setPcreCompatibility()
	{
		// Need to check PCRE version because some environments are
		// running older versions of the PCRE library
		// (run in *nix environment `pcretest -C`)

		if (defined('PCRE_VERSION')) {
			if ((int)PCRE_VERSION >= 7) { // constant available since PHP 5.2.4
				$this->pcreCompatibility = true;
			}
		}

		return $this;
	}


	/**
	 * @param mixed $prefs
	 *
	 * @return Mentions
	 */
	public function setPrefs()
	{
		$this->prefs = e107::getPlugPref('mentions');

		return $this;
	}


	/**
	 * Does Debug logging by writing a log file to the plugin directory
	 *
	 * @param mixed  $content
	 *  The data to be logged - can be passed as string or array.
	 * @param string $logname
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

		list($userMention, $userName) = $mention;

		$data = $this->getUserData($userName);

		if ($data['user_name'] === $userName) {

			$userData =
				['id' => $data['user_id'], 'name' => $data['user_name']];

			$link = e107::getUrl()->create('user/profile/view', $userData);

			return '<a href="' . $link . '" class="mentions-user-link">' . $userMention . '</a>';
		}

		return $userMention;
	}


	/**
	 * Get user data (user_id, user_name, user_email) from user table
	 *
	 * @param string $userName
	 *
	 * @return array
	 *      User details from 'user' table
	 * @todo make regex parsing of spaces in username work so that
	 *      v1 support for data retrieval can be implemented
	 */
	protected function getUserData($userName)
	{
		$sql = e107::getDb();
		$tp = e107::getParser();

		$userName = $tp->toDB($userName);

		if ($this->prefs['support_v1_chars']) {
			return $sql->retrieve('user', 'user_name, user_id, user_email',
				"user_name = '{$userName}' OR REPLACE(user_name, ' ', '-') = '{$userName}' ");
		}

		return $sql->retrieve('user', 'user_name, user_id, user_email',
			"user_name = '{$userName}' ");

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


}
