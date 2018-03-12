<?php
if ( ! defined('e107_INIT')) {
	require_once __DIR__ . '/../../class2.php';
}
if ( ! e107::isInstalled('mentions') || ! USER) {
	exit;
}
require_once __DIR__ . '/MentionsAutoComplete.php';
MentionsAutoComplete::respond($_GET['mq']);
