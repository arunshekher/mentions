<?php


class MentionsSuggestion extends Mentions
{
	private $db;
	private $ajax;

	/**
	 * MentionsSuggestion constructor.
	 */
	public function __construct()
	{
		Mentions::__construct();
		$this->db = e107::getDb();
		$this->ajax = e107::getAjax();
	}


	/**
	 * Trigger response for the query
	 *
	 * @param $request
	 */
	public static function triggerResponse($request)
	{
		$suggestion = new MentionsSuggestion;
		$suggestion->respond($request);
	}

	/**
	 * Load libraries
	 */
	public static function libs()
	{
		$suggestion = new MentionsSuggestion;
		$suggestion->loadLibs();
	}


	/**
	 * Responds to suggestions API requests,
	 * returns JSON formatted response.
	 *
	 * @param $request
	 */
	public function respond($request)
	{

		if (e_AJAX_REQUEST && USER && vartrue($request)) {

			$db = $this->db;
			$tp = $this->parse;
			$ajax = $this->ajax;

			$mq = $tp->filter($request);
			$where = "user_name LIKE '" . $mq . "%' ";

			if ($db->select('user', 'user_name, user_image, user_login',
				$where . ' ORDER BY user_name')
			) {

				$data = [];
				while ($row = $db->fetch()) {
					$data[] = [
						'image'    => $row['user_image'],
						'username' => $row['user_name'],
						'name'     => $row['user_login'],
					];
				}

				if (count($data)) {
					//$ajax = e107::getAjax();
					$ajax->response($data);
				}
			} else {

				$msg = [
					'error' => [
						'msg'  => 'No user found!',
						'code' => '4',
					],
				];

				//e107::getAjax()->response($msg);
				$ajax->response($msg);

			}

		}
		die;
	}



	/**
	 * Load javascript libraries.
	 */
	public function loadLibs()
	{
		// plugin preferences
		$mentionsPref = $this->prefs;

		if ($mentionsPref['mentions_active'] && USER_AREA && USER) {

			$libGlobal = $mentionsPref['use_global_path'];

			if ($libGlobal) {
				$this->loadLibsGlobally();
			} else {
				$this->loadLibsLocally();
			}

			$this->setLibOptions($mentionsPref);

			e107::js('footer', '{e_PLUGIN}mentions/js/mentions.js', 'jquery');
		}
	}


	/**
	 * Loads libraries from the global path
	 */
	protected function loadLibsGlobally()
	{
		e107::library('load', 'ichord.caret', 'minified');
		e107::library('load', 'ichord.atwho', 'minified');
	}


	/**
	 * Loads libraries from the local path
	 */
	protected function loadLibsLocally()
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
	 * Javascript settings
	 * @param $mentionsPref
	 */
	private function setLibOptions($mentionsPref)
	{
		// Mentions Autocomplete/suggestion API path
		$apiPath = e_PLUGIN_ABS . 'mentions/index.php';

		$jsSettings = [
			'api_endpoint' => $apiPath,
			'suggestions'  => [
				'minChar'    => $mentionsPref['atwho_min_char'],
				'maxChar'    => $mentionsPref['atwho_max_char'],
				'entryLimit' => $mentionsPref['atwho_item_limit'],
			],
		];

		// Footer - settings + script
		e107::js('settings', ['mentions' => $jsSettings]);
	}

}
