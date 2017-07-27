<?php
if ( ! defined('e107_INIT')) {
	exit;
}

// plugin preferences
$mentionsPref = e107::getPlugPref('mentions');

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
			'entryLimit' => $mentionsPref['atwho_item_limit'],
			'hiFirst' => $mentionsPref['atwho_highlight_first']
		]
	];

	// Footer - settings + script
	e107::js('settings', ['mentions' => $jsSettings]);
	e107::js('footer', '{e_PLUGIN}mentions/js/mentions.js', 'jquery');
}