<?php
if ( ! defined('e107_INIT')) {
	exit;
}


class MentionsAutoComplete extends Mentions
{
	private $db;
	private $ajax;


	/**
	 * MentionsAutoComplete constructor.
	 */
	public function __construct()
	{
		Mentions::__construct();
		$this->db = e107::getDb();
		$this->ajax = e107::getAjax();
	}


	/**
	 * Static alias for MentionsAutoComplete::getResponse()
	 *
	 * @param string $input
	 *  _GET param to respond to.
	 * @see MentionsAutoComplete::getResponse()
	 */
	public static function query($input)
	{
		$autoComplete = new MentionsAutoComplete;
		$autoComplete->getResponse($input);
	}


	/**
	 * Responds to auto-completion API HTTP requests,
	 * returns JSON formatted response.
	 *
	 * @param string $queryParam
	 *  XHR _GET query param to give response for.
	 */
	public function getResponse($queryParam)
	{

		if (e_AJAX_REQUEST && USER && vartrue($queryParam)) {

			$db = $this->db;
			$tp = $this->parse;
			$ajax = $this->ajax;

			$mq = $tp->filter($queryParam);
			$where = "user_name LIKE '" . $mq . "%' ";

			$result =
				$db->select('user', 'user_name, user_image, user_login',
				$where . ' ORDER BY user_name');

			if ($result) {

				$data = [];
				while ($row = $db->fetch()) {
					$data[] = [
						'image'    => $row['user_image'], // todo: process avatar image (crop size) and send html rather.
						'username' => $row['user_name'],
						'name'     => $row['user_login'],
					];
				}

				$ajax->response($data);

			} else {

				$msg = [
					'error' => [
						'msg'  => 'No user found!',
						'code' => '4',
					],
				];

				$ajax->response($msg);

			}

		}
		die;
	}


	/**
	 * Static alias for MentionsAutoComplete::loadLibs()
	 * @see MentionsAutoComplete::loadLibs()
	 */
	public static function libs()
	{
		$autoComplete = new MentionsAutoComplete;
		$autoComplete->loadLibs();
	}


	/**
	 * Loads mentions auto-complete Javascript libraries based on the plugin
	 *  - preference as load it using local or global path. Only loaded if
	 *  - the plugin is active, its a user area and the user is not a guest.
	 */
	public function loadLibs()
	{
		// plugin preferences
		$mentionsPref = $this->prefs;

		if ($mentionsPref['mentions_active'] && USER_AREA && USER) {

			$libGlobal = $mentionsPref['use_global_path'];

			if ($libGlobal) {
				$this->loadLibsUsingGlobalPath();
			} else {
				$this->loadLibsUsingLocalPath();
			}

			$this->setLibOptions($mentionsPref);

			e107::js('footer', '{e_PLUGIN}mentions/js/mentions.js', 'jquery');
		}
	}


	/**
	 * Loads Javascript libraries from the global path
	 */
	protected function loadLibsUsingGlobalPath()
	{
		e107::library('load', 'ichord.caret', 'minified');
		e107::library('load', 'ichord.atwho', 'minified');
	}


	/**
	 * Loads Javascript libraries from the local path
	 */
	protected function loadLibsUsingLocalPath()
	{
		e107::css('mentions', 'js/ichord.atwho/dist/css/jquery.atwho.min.css');
		e107::js('footer',
			e_PLUGIN . 'mentions/js/ichord.caret/dist/jquery.caret.min.js',
			'jquery', 1);
		e107::js('footer',
			e_PLUGIN . 'mentions/js/ichord.atwho/dist/js/jquery.atwho.min.js',
			'jquery', 2);
	}


	/**
	 * Lay-down auto-complete Javascript library settings
	 *
	 * @param array $mentionsPref
	 *  'mention' plugin preference data array.
	 */
	private function setLibOptions($mentionsPref)
	{
		// Mentions auto-complete API endpoint
		$apiPath = e_PLUGIN_ABS . 'mentions/index.php';

		$jsSettings = [
			'api_endpoint' => $apiPath,
			'suggestions'  => [
				'minChar'    => $mentionsPref['atwho_min_char'],
				'maxChar'    => $mentionsPref['atwho_max_char'],
				'entryLimit' => $mentionsPref['atwho_item_limit'],
			],
			'inputFields' => ['activeOnes' => $this->obtainFields($mentionsPref)]
		];

		// Footer - settings + script
		e107::js('settings', ['mentions' => $jsSettings]);
	}


	/**
	 * Returns all e107 'texarea' form fields that need to have auto-complete
	 *  - based on 'mentions_contexts'  plugin preference.
	 * @param array $pref
	 *
	 * @return string
	 *  comma separated string of form field ids that require auto-complete
	 */
	private function obtainFields($pref)
	{
		if ($pref['mentions_contexts'] === 1) {
			return '#cmessage, #forum-quickreply-text, #post';
		}
		
		return '#cmessage, #comment, #forum-quickreply-text, #post';
	}

}
