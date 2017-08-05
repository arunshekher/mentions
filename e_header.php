<?php
if ( ! defined('e107_INIT')) {
	exit;
}
//require __DIR__ . '/MentionsNotification.php';
require __DIR__ . '/MentionsSuggestion.php';

// load suggestion popup libs
MentionsSuggestion::libs();

// perform mentions notification
//MentionsNotification::execute();

