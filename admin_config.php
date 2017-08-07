<?php
require_once('../../class2.php');
if ( ! getperms('P') || ! e107::isInstalled('mentions')) {
	e107::redirect('admin');
	exit;
}

e107::lan('mentions', 'admin', true);
e107::lan('mentions', 'global', true);


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

	protected $menuTitle = LAN_MENTIONS_PLUGIN_NAME;

}


class mentions_ui extends e_admin_ui
{
	protected $pluginTitle = LAN_MENTIONS_PLUGIN_NAME;

	protected $pluginName = 'mentions';


	protected $mentionsContexts = [
		1 => LAN_MENTIONS_PREF_VAL_CONTEXT_01,
		2 => LAN_MENTIONS_PREF_VAL_CONTEXT_02,
		3 => LAN_MENTIONS_PREF_VAL_CONTEXT_03,
	];

	protected $preftabs = [
		LAN_MENTIONS_PREF_TAB_MAIN,
		LAN_MENTIONS_PREF_TAB_ATWHO,
		'Notification'
	];

	protected $prefs = [
		'mentions_active'   => [
			'title' => LAN_MENTIONS_PREF_LBL_ACTIVE,
			'tab'   => 0,
			'type'  => 'boolean',
			'data'  => 'int',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_ACTIVATION,
		],
		'mentions_contexts' => [
			'title' => LAN_MENTIONS_PREF_LBL_CONTEXTS,
			'tab'   => 0,
			'type'  => 'dropdown',
			'size'  => 'xxxlarge',
			'data'  => 'int',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_CONTEXT,
		],
		'use_global_path' => [
			'title' => LAN_MENTIONS_PREF_LBL_GLOBAL_LIBS,
			'tab'   => 0,
			'type'  => 'boolean',
			'data'  => 'int',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_GLOBAL_LIBS,
		],
		'atwho_min_char'   => [
			'title' => LAN_MENTIONS_PREF_LBL_ATWHO_MINCHARS,
			'tab'   => 1,
			'type'  => 'number',
			'data'  => 'int',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_ATWHO_MINCHAR,
		],
		'atwho_max_char'   => [
			'title' => LAN_MENTIONS_PREF_LBL_ATWHO_MAXCHARS,
			'tab'   => 1,
			'type'  => 'number',
			'data'  => 'int',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_ATWHO_MAXCHAR,
		],
		'atwho_item_limit' => [
			'title' => LAN_MENTIONS_PREF_LBL_ATWHO_LIMIT,
			'tab' => 1,
			'type' => 'number',
			'data' => 'int',
			'help' => LAN_MENTIONS_PREF_LBL_HINT_ATWHO_LIMIT,
		],
		'atwho_highlight_first' => [
			'title' => LAN_MENTIONS_PREF_LBL_ATWHO_HIGHLIGHT,
			'tab'   => 1,
			'type'  => 'boolean',
			'data'  => 'boolean',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_ATWHO_HIGHLIGHT,
		],
		'notify_chatbox_mentions' => [
			'title' => 'Email notification for chatbox mentions',
			'tab'   => 2,
			'type'  => 'boolean',
			'data'  => 'boolean',
			'help'  => 'Turn on notification for chatbox mentions.',
		],
		'notify_comment_mentions' => [
			'title' => 'Email notification for comment mentions',
			'tab'   => 2,
			'type'  => 'boolean',
			'data'  => 'boolean',
			'help'  => 'Turn on notification for comment mentions.',
		],
		'notify_forum_topic_mentions' => [
			'title' => 'Email notification for forum new topic mentions',
			'tab'   => 2,
			'type'  => 'boolean',
			'data'  => 'boolean',
			'help'  => 'Turn on notification for forum new topic mentions.',
		],
		'notify_forum_reply_mentions' => [
			'title' => 'Email notification for forum reply mentions',
			'tab'   => 2,
			'type'  => 'boolean',
			'data'  => 'boolean',
			'help'  => 'Turn on notification for forum reply mentions.',
		],
		'email_subject_line' => array(
			'title' => 'Email Subject Line',
			'tab'   => 2,
			'type' 	=> 'text',
			'data' 	=> 'str',
			'help' 	=>'Email Subject Line - in place of {MENTIONER} mentioner\'s username will appear.',
		),

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
