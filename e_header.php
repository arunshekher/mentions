<?php
if ( ! defined('e107_INIT')) {
	exit;
}
require __DIR__ . '/MentionsSuggestion.php';
$suggestions = new MentionsSuggestion;
$suggestions->loadLibs();

