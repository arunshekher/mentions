<?php
require __DIR__ . '/MentionsPlugin.php';

class MentionsAutoComplete extends MentionsPlugin
{


	/**
	 *
	 */
	public static function loadLibs()
	{
		if (USER_AREA && USER) {

			$useGlobal = e107::getPlugPref('mentions', 'use_global_path');

			if ($useGlobal) {
				echo '<!-- Debug: Mentions - Global Libs Loaded or NOT? :-\ why? -->';
				e107::library('load', 'jQuery.Caret.js');
				e107::library('load', 'jQuery.At.js');
			} else {
				e107::css('mentions', 'js/jquery.atwho.css');
				e107::js('mentions', 'js/jquery.caret.js', 'jquery');
				e107::js('mentions', 'js/jquery.atwho.js', 'jquery');
			}

			$pluginPath = e_PLUGIN_ABS . 'mentions/';

			$mentionsSettings = [
				'path'     => $pluginPath,
			    'At.js'    => [
			    	'minLen' => 1,
				    'maxLen' => 15
			    ]
			];

			e107::js('footer', '{e_PLUGIN}mentions/js/mentions.js', 'jquery');
			e107::js('settings', ['mentions' => $mentionsSettings]);
		}
	}

	/**
	 *
	 */
	public function response($request)
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