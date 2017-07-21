<?php
require_once('../../class2.php');
if ( ! getperms('P')) {
	e107::redirect('admin');
	exit;
}

e107::lan('mentions', 'admin', true);


class mentions_adminArea extends e_admin_dispatcher
{
	protected $modes = [
		'main' => [
			'controller' => 'mentions_ui',
			'path'       => null,
			'ui'         => 'mentions_form_ui',
			'uipath'     => null,
		],
	];

	protected $adminMenu = [

		'main/prefs' => ['caption' => LAN_PREFS, 'perm' => 'P'],

	];

	protected $menuTitle = 'Mentions';

}


class mentions_ui extends e_admin_ui
{
	protected $pluginTitle = 'Mentions';

	protected $pluginName = 'mentions';


	protected $mentionsContexts = [
		1 => 'Forum-And-Chatbox',
		2 => 'Forum-Chatbox-And-Comments',
		3 => 'Forum-Chatbox-Comments-And-News',
	];


	protected $prefs = [
		'mentions_active'   => [
			'title' => 'Enable/Disable',
			'tab'   => 0,
			'type'  => 'boolean',
			'data'  => 'int',
			'help'  => 'Turn On/Off Mentions Globally',
		],
		'mentions_contexts' => [
			'title' => 'Parse \'mentions\' in these contexts:',
			'tab'   => 0,
			'type'  => 'dropdown',
			'size'  => 'xxxlarge',
			'data'  => 'int',
			'help'  => '\'mentions\' is called in what text parse context.',
		],
		'use_global_path' => [
			'title' => 'Use global path for JS libraries:',
			'tab'   => 0,
			'type'  => 'boolean',
			'data'  => 'int',
			'help'  => 'Use global path (\'e107_web/lib/\')to load jQuery auto-complete libraries from.',
		],
	];

	protected $fieldpref = [];


	public function init()
	{
		$this->prefs['mentions_contexts']['writeParms'] =
			$this->mentionsContexts;
		$this->libLocationWarning();
	}


	private function libLocationWarning()
	{
		$libGlobal = e107::getPlugPref('mentions', 'use_global_path');
		if ($libGlobal) {
			e107::getMessage()
				->addWarning('You need to place Caret.js and At.js 
				auto-complete libraries under /e107_web/lib/ directory according to their 
				paths declared in e_library.php to use the libraries from this location.');
		}
	}


}


class mentions_form_ui extends e_admin_form_ui
{
}


new mentions_adminArea();

require_once(e_ADMIN . "auth.php");

e107::getAdminUI()->runPage();

require_once(e_ADMIN . "footer.php");
exit;
