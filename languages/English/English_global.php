<?php
define('LAN_MENTIONS_PLUGIN_NAME', 'Mentions');
define('LAN_MENTIONS_PLUGIN_SUMMARY', 'A @username mention plugin that in-turn sends the mentioned user a notification email.');
define('LAN_MENTIONS_PLUGIN_DESCRIPTION', 'It brings Twitter or GitHub like \'@username\' mentioning functionality to e107. Mentioned users in-turn receives an email letting them know that they were mentioned in your post.');
define('LAN_MENTIONS_PLUGIN_CONFIGURE', 'Configure Mentions Plugin');

define('LAN_MENTIONS_TAG_CHATBOX', 'chatbox');
define('LAN_MENTIONS_TAG_COMMENT', 'comment');
define('LAN_MENTIONS_TAG_FORUM', 'forum');

define('LAN_MENTIONS_EMAIL_SUBJECTLINE', 'You were mentioned by ');
define('LAN_MENTIONS_EMAIL_HELLO', 'Hello');

define('LAN_MENTIONS_EMAIL_VERSE_CHATBOX', '[user] mentioned you in a [tag] post on [date].');
define('LAN_MENTIONS_EMAIL_VERSE_CHATBOX_NEW', '[user] mentioned you in a [link] post on [date].');
define('LAN_MENTIONS_EMAIL_VERSE_COMMENT', '[user] mentioned you in a [tag] post for the [type] item titled &lsquo;[title]&rsquo; on [date].');
define('LAN_MENTIONS_EMAIL_VERSE_COMMENT_NEW', '[user] mentioned you in a [tag] post for the [type] item titled [link] on [date].');
define('LAN_MENTIONS_EMAIL_VERSE_FORUM', '[user] mentioned you in a [tag] post titled \'[title]\' on [date].');
define('LAN_MENTIONS_EMAIL_VERSE_FORUM_NEW', '[user] mentioned you in a [tag] post titled [link] on [date].');
define('LAN_MENTIONS_EMAIL_VERSE_UNRESOLVED', '[x] mentioned you in an un-resolvable post!');

define('LAN_MENTIONS_COMMENT_NEWS', 'news');
define('LAN_MENTIONS_COMMENT_POLL', 'poll');
define('LAN_MENTIONS_COMMENT_DOWNLOADS', 'downloads');
define('LAN_MENTIONS_COMMENT_UNKNOWN', 'unknown');


define('LAN_PLUGIN_MENTIONS_EMAIL_TEXT_COMMENT', 'mentioned you in a [-tag-] post for the [-type-] item titled [-link-] on [-date-].');
