<?php
require_once('../../class2.php');
if ( ! getperms('P') || ! e107::isInstalled('mentions')) {
	e107::redirect('admin');
	exit;
}

e107::lan('mentions', 'admin', true);


class mentions_adminArea extends e_admin_dispatcher
{
	protected $modes = [
		'main' => [
			'controller' => 'mentions_ui',
			'path'       => null,
			'ui'         => 'mentions_form_ui',
			'uipath'     => null,
		],
	];

	protected $adminMenu = [

		'main/prefs' => ['caption' => LAN_PREFS, 'perm' => 'P'],

	];

	protected $menuTitle = 'Mentions';

}


class mentions_ui extends e_admin_ui
{
	protected $pluginTitle = 'Mentions';

	protected $pluginName = 'mentions';


	protected $mentionsContexts = [
		1 => LAN_MENTIONS_PREF_VAL_CONTEXT_01,
		2 => LAN_MENTIONS_PREF_VAL_CONTEXT_02,
		3 => LAN_MENTIONS_PREF_VAL_CONTEXT_03,
	];

	protected $preftabs = [
		'Main',
		'Suggestion Pop-up'
	];

	protected $prefs = [
		'mentions_active'   => [
			'title' => LAN_MENTIONS_PREF_LBL_ACTIVE,
			'tab'   => 0,
			'type'  => 'boolean',
			'data'  => 'int',
			'help'  => 'Turn On/Off Mentions Globally',
		],
		'mentions_contexts' => [
			'title' => LAN_MENTIONS_PREF_LBL_CONTEXTS,
			'tab'   => 0,
			'type'  => 'dropdown',
			'size'  => 'xxxlarge',
			'data'  => 'int',
			'help'  => '\'mentions\' is called in what text parse context.',
		],
		'use_global_path' => [
			'title' => LAN_MENTIONS_PREF_LBL_GLOBAL_LIBS,
			'tab'   => 0,
			'type'  => 'boolean',
			'data'  => 'int',
			'help'  => 'Use global path (\'e107_web/lib/\')to load jQuery auto-complete libraries from.',
		],
		'atwho_min_char'   => [
			'title' => LAN_MENTIONS_PREF_LBL_ATWHO_MINCHARS,
			'tab'   => 1,
			'type'  => 'number',
			'data'  => 'int',
			'help'  => 'Minimum number of characters required to input after `@` sign to show suggestion popup-list (0 - 20):',
		],
		'atwho_max_char'   => [
			'title' => LAN_MENTIONS_PREF_LBL_ATWHO_MAXCHARS,
			'tab'   => 1,
			'type'  => 'number',
			'data'  => 'int',
			'help'  => 'Max number of characters after `@` that would be matched to populate suggestion.',
		],
		'atwho_item_limit' => [
			'title' => LAN_MENTIONS_PREF_LBL_ATWHO_LIMIT,
			'tab' => 1,
			'type' => 'number',
			'data' => 'int',
			'help' => 'Number of username entries to show in suggestion popup-list',
		],
		'atwho_highlight_first' => [
			'title' => LAN_MENTIONS_PREF_LBL_ATWHO_HIGHLIGHT,
			'tab'   => 1,
			'type'  => 'boolean',
			'data'  => 'boolean',
			'help'  => 'Toggle highlight on/off for the first entry in popup-list.',
		],

	];

	protected $fieldpref = [];


	public function init()
	{
		$this->prefs['mentions_contexts']['writeParms'] =
			$this->mentionsContexts;
	}

	public function renderHelp()
	{
		$caption = LAN_MENTIONS_INFO_MENU_TITLE;
		$text = LAN_MENTIONS_INFO_MENU_LOGO;
		$text .= LAN_MENTIONS_INFO_MENU_SUBTITLE;
		$text .= LAN_MENTIONS_INFO_MENU_REPO_URL;
		$text .= LAN_MENTIONS_INFO_MENU_REPO_BUTTON_WATCH;
		$text .= LAN_MENTIONS_INFO_MENU_REPO_BUTTON_STAR;
		$text .= LAN_MENTIONS_INFO_MENU_REPO_BUTTON_ISSUE;
		$text .= LAN_MENTIONS_INFO_MENU_SUBTITLE_DEV;
		$text .= LAN_MENTIONS_INFO_MENU_DEV;
		$text .= LAN_MENTIONS_INFO_MENU_REPO_BUTTON_FOLLOW;
		$text .= LAN_MENTIONS_INFO_MENU_GITHUB_BUTTONS_SCRIPT;

		return ['caption' => $caption, 'text' => $text];

	}

}


class mentions_form_ui extends e_admin_form_ui
{
}


new mentions_adminArea();

require_once(e_ADMIN . "auth.php");

e107::getAdminUI()->runPage();

require_once(e_ADMIN . "footer.php");
exit;
