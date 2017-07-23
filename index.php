<?php
if ( ! defined('e107_INIT')) {
	require_once __DIR__ . '/../../class2.php';
}
if ( ! e107::isInstalled('mentions')) {
	exit;
}

require __DIR__ . '/MentionsAutoComplete.php';

$ma = new MentionsAutoComplete();

$request = $_GET['mq'];

$ma->response($request);