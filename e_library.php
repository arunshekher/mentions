<?php
class mentions_library
{

	function config()
	{
		$libraries['jQuery.Caret.js'] = array(
			// Only used in administrative UI of Libraries API.
			'name'              => 'Caret.js',
			'vendor_url'        => 'https://github.com/ichord/Caret.js/',
			'download_url'      => 'https://github.com/ichord/Caret.js/archive/master.zip',
			'version_arguments' => array(
				'file'    => 'dist/jquery.caret.js',
			),
			'files' => array(
				'js'  => array(
					'dist/jquery.caret.js' => array(
						'type' => 'footer',
					),
				),
			),
		);

		$libraries['jQuery.At.js'] = array(
			// Only used in administrative UI of Libraries API.
			'name'              => 'At.js',
			'vendor_url'        => 'https://github.com/ichord/At.js',
			'download_url'      => 'https://github.com/ichord/At.js/archive/master.zip',
			'version_arguments' => array(
				'file'    => 'dist/js/jquery.atwho.js',
				//  * at.js - 1.5.4
				'pattern' => '/at.js - (\d+\.\d+\.\d+)/',
				'lines'   => 6,
			),
			'files' => array(
				'js' => array(
					'dist/js/jquery.atwho.js' => array(
						'type' => 'footer',
					),
				'css' => array(
					'dist/css/jquery.atwho.css',
				),
				),
			),
		);

		return $libraries;
	}

}
