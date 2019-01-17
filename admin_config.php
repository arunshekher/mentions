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

	protected $maxNotificationEmails = [
		5  => LAN_MENTIONS_PREF_VAL_MAX_EMAIL_5,
		10 => LAN_MENTIONS_PREF_VAL_MAX_EMAIL_10,
		15 => LAN_MENTIONS_PREF_VAL_MAX_EMAIL_15,
		20 => LAN_MENTIONS_PREF_VAL_MAX_EMAIL_20,
		25 => LAN_MENTIONS_PREF_VAL_MAX_EMAIL_25,
	];

	protected $avatarSizesList = [
		16 => LAN_MENTIONS_PREF_VAL_AVATAR_SIZE_16,
		24 => LAN_MENTIONS_PREF_VAL_AVATAR_SIZE_24,
		32 => LAN_MENTIONS_PREF_VAL_AVATAR_SIZE_32,
	];

	protected $avatarBorderList = [
		'circle'  => LAN_MENTIONS_PREF_VAL_AVATAR_BORDER_CIRCLE,
		'rounded' => LAN_MENTIONS_PREF_VAL_AVATAR_BORDER_ROUNDED,
		'none'    => LAN_MENTIONS_PREF_VAL_AVATAR_BORDER_SQUARE,
	];

	protected $preftabs = [
		LAN_MENTIONS_PREF_TAB_MAIN,
		LAN_MENTIONS_PREF_TAB_ATWHO,
		LAN_MENTIONS_PREF_TAB_NOTIFICATION,
	];


	protected $prefs = [
		'mentions_active'         => [
			'title' => MENTIONS_ADMIN_ACTIVE_TITLE,
			'tab'   => 0,
			'type'  => 'boolean',
			'data'  => 'int',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_ACTIVATION,
		],
		'mentions_contexts'       => [
			'title' => MENTIONS_ADMIN_CONTEXT_TITLE,
			'tab'   => 0,
			'type'  => 'dropdown',
			'size'  => 'xxxlarge',
			'data'  => 'int',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_CONTEXT,
		],
		'use_global_path'         => [
			'title' => MENTIONS_ADMIN_JSPATH_TITLE,
			'tab'   => 0,
			'type'  => 'boolean',
			'data'  => 'int',
			'help'  => MENTIONS_ADMIN_JSPATH_HINT,
		],
		'support_v1_chars'         => [
			'title' => MENTIONS_ADMIN_V1CHARSUPPORT_TITLE,
			'tab'   => 0,
			'type'  => 'boolean',
			'data'  => 'int',
			'help'  => LAN_MENTIONS_PREF_LBL_V1_CHAR_SUPPORT,
		],
		'atwho_min_char'          => [
			'title' => MENTIONS_ADMIN_MINCHAR_TITLE,
			'tab'   => 1,
			'type'  => 'number',
			'data'  => 'int',
			'help'  => MENTIONS_ADMIN_MINCHAR_TITLE,
		],
		'atwho_max_char'          => [
			'title' => MENTIONS_ADMIN_MAXCHAR_TITLE,
			'tab'   => 1,
			'type'  => 'number',
			'data'  => 'int',
			'help'  => MENTIONS_ADMIN_MAXCHAR_TITLE,
		],
		'atwho_item_limit'        => [
			'title' => MENTIONS_ADMIN_ITEMLIMIT_TITLE,
			'tab'   => 1,
			'type'  => 'number',
			'data'  => 'int',
			'help'  => MENTIONS_ADMIN_ITEMLIMIT_TITLE,
		],
		'atwho_highlight_first'   => [
			'title' => LAN_MENTIONS_PREF_LBL_ATWHO_HIGHLIGHT,
			'tab'   => 1,
			'type'  => 'boolean',
			'data'  => 'int',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_ATWHO_HIGHLIGHT,
		],
		'atwho_avatar'            => [
			'title' => LAN_MENTIONS_PREF_LBL_ATWHO_AVATAR,
			'tab'   => 1,
			'type'  => 'boolean',
			'data'  => 'int',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_ATWHO_AVATAR,
		],
		'notify_chatbox_mentions' => [
			'title' => LAN_MENTIONS_PREF_LBL_CHATBOX_EMAIL,
			'tab'   => 2,
			'type'  => 'boolean',
			'data'  => 'int',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_CHATBOX_EMAIL,
		],
		'notify_comment_mentions' => [
			'title' => LAN_MENTIONS_PREF_LBL_COMMENT_EMAIL,
			'tab'   => 2,
			'type'  => 'boolean',
			'data'  => 'int',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_COMMENT_EMAIL,
		],
		'notify_forum_mentions'   => [
			'title' => LAN_MENTIONS_PREF_LBL_FORUM_EMAIL,
			'tab'   => 2,
			'type'  => 'boolean',
			'data'  => 'int',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_FORUM_EMAIL,
		],
		'email_subject_line'      => [
			'title' => MENTIONS_ADMIN_SUBJECTLINE,
			'tab'   => 2,
			'type'  => 'text',
			'data'  => 'str',
			'size'  => 'xxlarge',
			'help'  => MENTIONS_ADMIN_SUBJECTLINE,
		],
		'max_emails'              => [
			'title' => MENTIONS_ADMIN_MAXEMAILS_TITLE,
			'tab'   => 2,
			'type'  => 'dropdown',
			'data'  => 'int',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_MAX_EMAILS_1,
		],
		'avatar_size'             => [
			'title' => LAN_MENTIONS_PREF_LBL_AVATAR_SIZE,
			'tab'   => 1,
			'type'  => 'dropdown',
			'data'  => 'int',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_AVATAR_SIZE,
		],
		'avatar_border'           => [
			'title' => LAN_MENTIONS_PREF_LBL_AVATAR_BORDER,
			'tab'   => 1,
			'type'  => 'dropdown',
			'data'  => 'str',
			'help'  => LAN_MENTIONS_PREF_LBL_HINT_AVATAR_BORDER,
		],

	];

	protected $fieldpref = [];

	public function init()
	{
		$this->prefs['mentions_contexts']['writeParms'] =
			$this->mentionsContexts;
		$this->prefs['max_emails']['writeParms'] = $this->maxNotificationEmails;
		$this->prefs['avatar_size']['writeParms'] = $this->avatarSizesList;
		$this->prefs['avatar_border']['writeParms'] = $this->avatarBorderList;

		$this->concatDefineConstants();
	}


	private function concatDefineConstants()
	{
		define('MENTIONS_ADMIN_ACTIVE_TITLE', '<p>' . LAN_MENTIONS_PREF_LBL_ACTIVE
			. '</p><small>' . LAN_MENTIONS_PREF_LBL_HINT_ACTIVATION . '</small>');
		define('MENTIONS_ADMIN_CONTEXT_TITLE', '<p>' . LAN_MENTIONS_PREF_LBL_CONTEXTS
			. '</p><small>' . LAN_MENTIONS_PREF_LBL_HINT_CONTEXT . '</small>');
		define('MENTIONS_ADMIN_JSPATH_TITLE', '<p>' . LAN_MENTIONS_PREF_LBL_GLOBAL_LIBS . '</p><small>'
			. LAN_MENTIONS_PREF_LBL_HINT_GLOBAL_LIBS_1
			. '<kbd><a href="https://github.com/ichord/Caret.js" target="_blank">Caret.js</a></kbd>'
			. LAN_MENTIONS_PREF_LBL_HINT_GLOBAL_LIBS_2
			. '<kbd><a href="https://github.com/ichord/At.js" target="_blank">At.js</a></kbd>'
			. LAN_MENTIONS_PREF_LBL_HINT_GLOBAL_LIBS_3 . '</small>');
		define('MENTIONS_ADMIN_JSPATH_HINT', LAN_MENTIONS_PREF_LBL_HINT_GLOBAL_LIBS_1
			. '<kbd><a href="https://github.com/ichord/Caret.js" target="_blank">Caret.js</a></kbd>'
			. LAN_MENTIONS_PREF_LBL_HINT_GLOBAL_LIBS_2
			. '<kbd><a href="https://github.com/ichord/At.js" target="_blank">At.js</a></kbd>'
			. LAN_MENTIONS_PREF_LBL_HINT_GLOBAL_LIBS_3);
		define('MENTIONS_ADMIN_MINCHAR_TITLE', '<p>' . LAN_MENTIONS_PREF_LBL_ATWHO_MINCHARS_1
			. '<kbd>@</kbd>' . LAN_MENTIONS_PREF_LBL_ATWHO_MINCHARS_2
			. '</p><kbd>' . LAN_MENTIONS_PREF_LBL_ATWHO_MINCHARS_3 . '</kbd>');
		define('MENTIONS_ADMIN_MAXCHAR_TITLE', '<p>' . LAN_MENTIONS_PREF_LBL_ATWHO_MAXCHARS_1
			. '<kbd>@</kbd>' . LAN_MENTIONS_PREF_LBL_ATWHO_MAXCHARS_2
			. '</p><kbd>' . LAN_MENTIONS_PREF_LBL_ATWHO_MAXCHARS_3 . '</kbd>');
		define('MENTIONS_ADMIN_SUBJECTLINE', '<p>' . LAN_MENTIONS_PREF_LBL_EMAIL_SUBJECT
			. '</p><small>' . LAN_MENTIONS_PREF_LBL_HINT_EMAIL_SUBJECT_1
			. '<kbd>{MENTIONER}</kbd>' . LAN_MENTIONS_PREF_LBL_HINT_EMAIL_SUBJECT_2 . '</small>');
		define('MENTIONS_ADMIN_ITEMLIMIT_TITLE', '<p>' . LAN_MENTIONS_PREF_LBL_ATWHO_LIMIT_1
			. '</p><kbd>' . LAN_MENTIONS_PREF_LBL_ATWHO_LIMIT_2 . '</kbd>');
		define('MENTIONS_ADMIN_MAXEMAILS_TITLE', '<p>' . LAN_MENTIONS_PREF_LBL_MAX_EMAILS . '</p><small>'
			. LAN_MENTIONS_PREF_LBL_HINT_MAX_EMAILS_1 . '<br><br>'
			. LAN_MENTIONS_PREF_LBL_HINT_MAX_EMAILS_2 . '</small>');
		define('MENTIONS_ADMIN_V1CHARSUPPORT_TITLE', '<p>'
			. LAN_MENTIONS_PREF_LBL_V1_CHAR_SUPPORT . '</p><small>'
			. LAN_MENTIONS_PREF_LBL_HINT_V1_CHAR_SUPPORT . '</small>' );
	}

	public function renderHelp()
	{
		$template = e107::getTemplate('mentions', 'project_menu');

		return [
			'caption' => LAN_MENTIONS_INFO_MENU_TITLE,
			'text'    => $template,
		];

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
