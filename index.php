<?php
if ( ! defined('e107_INIT')) {
	require_once __DIR__ . '/../../class2.php';
}
if ( ! e107::isInstalled('mentions')) {
	exit;
}

if (e_AJAX_REQUEST && strtolower($_SERVER['REQUEST_METHOD']) === 'get' && vartrue($_GET['mq'])) {

	$db = e107::getDb();
	$tp = e107::getParser();

	$mq =
		$tp->filter($_GET['mq']); // TODO --> ? should it be mysql escaping - but what about server resource overhead??
	$where = "user_name LIKE '" . $mq . "%' ";

	if ($db->select('user', 'user_name, user_login',
		$where . ' ORDER BY user_name')
	) {

		$data = [];
		while ($row = $db->fetch()) {
			$data[] = [
				'username' => $row['user_name'],
				'name'     => $row['user_login'],
			];
		}

		if (count($data)) {
			//TODO -  ?? $data need filter_var_array as its going to be parsed as html on frontend, ?? xss csrf
			$ajax = e107::getAjax();
			$ajax->response($data);
		}
	}

}
exit;