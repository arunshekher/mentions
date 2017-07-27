<?php
if ( ! defined('e107_INIT')) {
	require_once __DIR__ . '/../../class2.php';
}
if ( ! e107::isInstalled('mentions') || ! USER) {
	exit;
}
require __DIR__ . '/MentionsAutoComplete.php';

$request = $_GET['mq'];
$ma = new MentionsAutoComplete();
$ma->respond($request);
