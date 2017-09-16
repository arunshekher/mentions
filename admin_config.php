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
		LAN_MENTIONS_PREF_TAB_NOTIFICATION,
	];

	protected $prefs = [
		'mentions_active'             => [
			'title' => '<p>' . LAN_MENTIONS_PREF_LBL_ACTIVE
				. '</p><small>' . LAN_MENTIONS_PREF_LBL_HINT_ACTIVATION . '</small>',
			'tab'   => 0,
			'type'  => 'boolean',
			'data'  => 'int',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_ACTIVATION,
		],
		'mentions_contexts'           => [
			'title' => '<p>' . LAN_MENTIONS_PREF_LBL_CONTEXTS
				. '</p><small>' . LAN_MENTIONS_PREF_LBL_HINT_CONTEXT . '</small>',
			'tab'   => 0,
			'type'  => 'dropdown',
			'size'  => 'xxxlarge',
			'data'  => 'int',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_CONTEXT,
		],
		'use_global_path'             => [
			'title' => '<p>' . LAN_MENTIONS_PREF_LBL_GLOBAL_LIBS
				. '</p><small>' . LAN_MENTIONS_PREF_LBL_HINT_GLOBAL_LIBS . '</small>',
			'tab'   => 0,
			'type'  => 'boolean',
			'data'  => 'int',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_GLOBAL_LIBS,
		],
		'atwho_min_char'              => [
			'title' => LAN_MENTIONS_PREF_LBL_ATWHO_MINCHARS,
			'tab'   => 1,
			'type'  => 'number',
			'data'  => 'int',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_ATWHO_MINCHAR,
		],
		'atwho_max_char'              => [
			'title' => LAN_MENTIONS_PREF_LBL_ATWHO_MAXCHARS,
			'tab'   => 1,
			'type'  => 'number',
			'data'  => 'int',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_ATWHO_MAXCHAR,
		],
		'atwho_item_limit'            => [
			'title' => LAN_MENTIONS_PREF_LBL_ATWHO_LIMIT,
			'tab'   => 1,
			'type'  => 'number',
			'data'  => 'int',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_ATWHO_LIMIT,
		],
		'atwho_highlight_first'       => [
			'title' => LAN_MENTIONS_PREF_LBL_ATWHO_HIGHLIGHT,
			'tab'   => 1,
			'type'  => 'boolean',
			'data'  => 'boolean',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_ATWHO_HIGHLIGHT,
		],
		'notify_chatbox_mentions'     => [
			'title' => LAN_MENTIONS_PREF_LBL_CHATBOX_EMAIL,
			'tab'   => 2,
			'type'  => 'boolean',
			'data'  => 'boolean',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_CHATBOX_EMAIL,
		],
		'notify_comment_mentions'     => [
			'title' => LAN_MENTIONS_PREF_LBL_COMMENT_EMAIL,
			'tab'   => 2,
			'type'  => 'boolean',
			'data'  => 'boolean',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_COMMENT_EMAIL,
		],
		'notify_forum_mentions' => [
			'title' => LAN_MENTIONS_PREF_LBL_FORUM_EMAIL,
			'tab'   => 2,
			'type'  => 'boolean',
			'data'  => 'boolean',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_FORUM_EMAIL,
		],
		'email_subject_line'          => [
			'title' => '<p>' . LAN_MENTIONS_PREF_LBL_EMAIL_SUBJECT
				. '</p><small>' . LAN_MENTIONS_PREF_LBL_HINT_EMAIL_SUBJECT . '</small>',
			'tab'   => 2,
			'type'  => 'text',
			'data'  => 'str',
			'size'  => 'xxlarge',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_EMAIL_SUBJECT,
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
		$text = '';
		$text .= LAN_MENTIONS_INFO_MENU_LOGO;
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
