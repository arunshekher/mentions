<?php
if ( ! defined('e107_INIT')) {
	exit;
}
require_once __DIR__ . '/MentionsNotification.php';

MentionsNotification::execute();
