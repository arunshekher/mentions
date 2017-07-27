<?php
if ( ! defined('e107_INIT')) {
	exit;
}
require __DIR__ . '/MentionsSuggestion.php';
$ma = new MentionsSuggestion;
$ma->loadLibs();

