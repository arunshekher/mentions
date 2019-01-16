<?php
define('LAN_MENTIONS_PREF_TAB_MAIN', 'Main');
define('LAN_MENTIONS_PREF_TAB_ATWHO', 'Auto-complete popup');
define('LAN_MENTIONS_PREF_TAB_NOTIFICATION', 'Email notification');
define('LAN_MENTIONS_PREF_LBL_ACTIVE', 'Enable/Disable');
define('LAN_MENTIONS_PREF_LBL_CONTEXTS', 'Parse \'mentions\' in these contexts:');
define('LAN_MENTIONS_PREF_LBL_GLOBAL_LIBS', 'Use global path for JS libraries:');

define('LAN_MENTIONS_PREF_LBL_ATWHO_MINCHARS_1', 'Min. number of characters to input after ');
define('LAN_MENTIONS_PREF_LBL_ATWHO_MINCHARS_2', 'to trigger the display of auto-complete popup-list:');
define('LAN_MENTIONS_PREF_LBL_ATWHO_MINCHARS_3', 'Range: 0 - 20, Recommended: 2');
define('LAN_MENTIONS_PREF_LBL_ATWHO_MAXCHARS_1', 'Max number of char. after ');
define('LAN_MENTIONS_PREF_LBL_ATWHO_MAXCHARS_2', ' that would be matched to populate auto-complete suggestion:');
define('LAN_MENTIONS_PREF_LBL_ATWHO_MAXCHARS_3', 'Upto: 20');
define('LAN_MENTIONS_PREF_LBL_ATWHO_LIMIT_1', 'Number of username entries to show in popup-list:');
define('LAN_MENTIONS_PREF_LBL_ATWHO_LIMIT_2', 'Recommended: 5');
define('LAN_MENTIONS_PREF_LBL_ATWHO_HIGHLIGHT', 'Highlight first entry in popup-list:');
define('LAN_MENTIONS_PREF_LBL_ATWHO_AVATAR', 'Show user avatar in popup-list:');
define('LAN_MENTIONS_PREF_LBL_CHATBOX_EMAIL', 'Chatbox mentions email notification:');
define('LAN_MENTIONS_PREF_LBL_COMMENT_EMAIL', 'Comment mentions email notification:');
define('LAN_MENTIONS_PREF_LBL_FORUMTOPIC_EMAIL', 'Forum topic mentions email notification:');
define('LAN_MENTIONS_PREF_LBL_FORUM_EMAIL', 'Forum mentions email notification:');
define('LAN_MENTIONS_PREF_LBL_FORUMREPLY_EMAIL', 'Forum reply mentions email notification:');
define('LAN_MENTIONS_PREF_LBL_EMAIL_SUBJECT', 'Email subject-line text:');
define('LAN_MENTIONS_PREF_LBL_MAX_EMAILS', 'Max. number of mention emails allowed per post.');
define('LAN_MENTIONS_PREF_LBL_AVATAR_SIZE',  'Choose avatar size:');
define('LAN_MENTIONS_PREF_LBL_AVATAR_BORDER', 'Choose avatar border style:');

define('LAN_MENTIONS_PREF_LBL_HINT_ACTIVATION', 'Turn On/Off Mentions Globally');
define('LAN_MENTIONS_PREF_LBL_HINT_CONTEXT', 'All content contexts that require parsing of \'mentions\'.');
define('LAN_MENTIONS_PREF_LBL_HINT_GLOBAL_LIBS_1', 'Turn this on if you wish to share ');
define('LAN_MENTIONS_PREF_LBL_HINT_GLOBAL_LIBS_2', ' and ');
define('LAN_MENTIONS_PREF_LBL_HINT_GLOBAL_LIBS_3', ' libraries with other e107 plugins and/or themes. This plugin uses these libraries.');
define('LAN_MENTIONS_PREF_LBL_HINT_ATWHO_MINCHAR', 'Minimum number of characters required to input after `@` sign to show suggestion popup-list (0 - 20):');
define('LAN_MENTIONS_PREF_LBL_HINT_ATWHO_MAXCHAR', 'Max number of characters after `@` that would be matched to populate suggestion.');
define('LAN_MENTIONS_PREF_LBL_HINT_ATWHO_LIMIT', 'Number of username entries to show in suggestion popup-list');
define('LAN_MENTIONS_PREF_LBL_HINT_ATWHO_HIGHLIGHT', 'Toggle highlight on/off for the first entry in popup-list.');
define('LAN_MENTIONS_PREF_LBL_HINT_ATWHO_AVATAR', 'Show user avatar image in auto-complete popup-list.');
define('LAN_MENTIONS_PREF_LBL_HINT_CHATBOX_EMAIL', 'Turn on email notification for chatbox mentions.');
define('LAN_MENTIONS_PREF_LBL_HINT_COMMENT_EMAIL', 'Turn on email notification for comment mentions.');
define('LAN_MENTIONS_PREF_LBL_HINT_FORUMTOPIC_EMAIL', 'Turn on email notification for new forum topic mentions.');
define('LAN_MENTIONS_PREF_LBL_HINT_FORUM_EMAIL', 'Turn on email notification for forum mentions.');
define('LAN_MENTIONS_PREF_LBL_HINT_FORUMREPLY_EMAIL', 'Turn on email notification for forum reply mentions.');
define('LAN_MENTIONS_PREF_LBL_HINT_EMAIL_SUBJECT_1', 'You can use the placeholder variable ');
define('LAN_MENTIONS_PREF_LBL_HINT_EMAIL_SUBJECT_2', ' in subject-line text, and it will be substituted with actual mentioner\'s username in the email subject field.');
define('LAN_MENTIONS_PREF_LBL_HINT_MAX_EMAILS_1',  'Maximum allowed number of notification emails that would be sent-out per comment/chatbox-post/forum-post.');
define('LAN_MENTIONS_PREF_LBL_HINT_MAX_EMAILS_2',  'Any more number of mentioned users than this limit (in one post) will not get email notification about their mentions.');
define('LAN_MENTIONS_PREF_LBL_HINT_AVATAR_SIZE', 'Choose the avatar size');
define('LAN_MENTIONS_PREF_LBL_HINT_AVATAR_BORDER', 'Choose avatar border style');


define('LAN_MENTIONS_PREF_VAL_CONTEXT_01', 'Forum + Chatbox');
define('LAN_MENTIONS_PREF_VAL_CONTEXT_02', 'Forum + Chatbox + Comments');
define('LAN_MENTIONS_PREF_VAL_CONTEXT_03', 'Forum + Chatbox + Comments + News');
define('LAN_MENTIONS_PREF_VAL_AVATAR_SIZE_16', '16x16px');
define('LAN_MENTIONS_PREF_VAL_AVATAR_SIZE_24', '24x24px');
define('LAN_MENTIONS_PREF_VAL_AVATAR_SIZE_32', '32x32px');
define('LAN_MENTIONS_PREF_VAL_AVATAR_BORDER_CIRCLE', 'Circle');
define('LAN_MENTIONS_PREF_VAL_AVATAR_BORDER_ROUNDED', 'Rounded');
define('LAN_MENTIONS_PREF_VAL_AVATAR_BORDER_SQUARE', 'Square');
define('LAN_MENTIONS_PREF_VAL_MAX_EMAIL_5', '5');
define('LAN_MENTIONS_PREF_VAL_MAX_EMAIL_10', '10');
define('LAN_MENTIONS_PREF_VAL_MAX_EMAIL_15', '15');
define('LAN_MENTIONS_PREF_VAL_MAX_EMAIL_20', '20');
define('LAN_MENTIONS_PREF_VAL_MAX_EMAIL_25', '25');

// Project Info Menu
define('LAN_MENTIONS_INFO_MENU_TITLE', 'Project Info');
define('LAN_MENTIONS_INFO_MENU_SUBTITLE', 'Project on GitHub:');
define('LAN_MENTIONS_INFO_MENU_SUBTITLE_ISSUES', 'Report Issues:');
define('LAN_MENTIONS_INFO_MENU_SUBTITLE_DEV', 'Show your Appreciation!');
define('LAN_MENTIONS_INFO_MENU_SUPPORT_DEV_TEXT', 'If you enjoy this plugin, please consider supporting what I do.');
define('LAN_MENTIONS_INFO_MENU_SUPPORT_DEV_TEXT_SIGN', 'However, if it breaks something please don\'t blame me! &mdash; Arun');
