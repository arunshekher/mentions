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


	protected $libLocation = [
		'plugin_dir' => 'Plugin Directory',
		'weblib_dir' => 'Third Party Libraries Directory',
	];

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
		'lib_location' => [
			'title' => 'Where to load \'mentions\' Auto-complete libraries from:',
			'tab'   => 0,
			'type'  => 'dropdown',
			'size'  => 'xxlarge',
			'data'  => 'str',
			'help'  => 'Location of \'mentions\' auto-complete libraries.',
		],
	];

	protected $fieldpref = [];


	public function init()
	{
		$this->prefs['lib_location']['writeParms'] = $this->libLocation;
		$this->prefs['mentions_contexts']['writeParms'] =
			$this->mentionsContexts;
		$this->libLocationWarning();
	}


	private function libLocationWarning()
	{
		$libLocation = e107::pref('mentions', 'lib_location');
		if ($libLocation === 'weblib_dir') {
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
