<?php
//require __DIR__ . '/Mentions.php';

class MentionsSuggestion extends Mentions
{


	/**
	 * Load suggestions popup javascript libs.
	 */
	public function loadLibs()
	{
		// plugin preferences - shared
		$mentionsPref = $this->prefs;

		if ($mentionsPref['mentions_active'] && USER_AREA && USER) {

			$libGlobal = $mentionsPref['use_global_path'];


			if ($libGlobal) {
				e107::library('load', 'ichord.caret');
				e107::library('load', 'ichord.atwho');
			} else {
				e107::css('mentions', 'js/ichord.atwho/dist/css/jquery.atwho.min.css');
				e107::js('footer', e_PLUGIN . 'mentions/js/ichord.caret/dist/jquery.caret.min.js', 'jquery', 1);
				e107::js('footer', e_PLUGIN . 'mentions/js/ichord.atwho/dist/js/jquery.atwho.min.js', 'jquery', 2);
			}

			// Mentions Autocomplete/suggestion API path
			$apiPath = e_PLUGIN_ABS . 'mentions/index.php';

			$jsSettings = [
				'api_endpoint'  => $apiPath,
				'suggestions'    => [
					'minChar' => $mentionsPref['atwho_min_char'],
					'maxChar' => $mentionsPref['atwho_max_char'],
					'entryLimit' => $mentionsPref['atwho_item_limit']
				]
			];

			// Footer - settings + script
			e107::js('settings', ['mentions' => $jsSettings]);
			e107::js('footer', '{e_PLUGIN}mentions/js/mentions.js', 'jquery');
		}
	}

	/**
	 * Responds to suggestions API requests, returns JSON formatted response.
	 */
	public function respond($request)
	{

		if (e_AJAX_REQUEST && USER && vartrue($request)) {

			$db = e107::getDb();
			$tp = e107::getParser();

			$mq =
				$tp->filter($request); // TODO --> ? Shouldn't it be mysql escaping (->toDB) - but will it cause server overhead??
			$where = "user_name LIKE '" . $mq . "%' ";

			if ($db->select('user', 'user_name, user_image, user_login',
				$where . ' ORDER BY user_name')
			) {

				$data = [];
				while ($row = $db->fetch()) {
					$data[] = [
						'image' => $row['user_image'],
						'username' => $row['user_name'],
						'name'     => $row['user_login'],
					];
				}

				if (count($data)) {
					//TODO -  ?? $data need filter_var_array as its going to be parsed as html on frontend, ?? xss csrf
					$ajax = e107::getAjax();
					$ajax->response($data);
				}
			} else {

				$msg = [
					'error' => [
						'msg' => 'No user found!',
						'code' => '4',
					]
				];

				e107::getAjax()->response($msg);
			}

		}
		die;

	}

}