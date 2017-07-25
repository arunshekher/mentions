<?php
class mentions_library
{

	function config()
	{
		$libraries['ichord.caret'] = [
			// Only used in administrative UI of Libraries API.
			'name'              => 'Caret.js',
			'vendor_url'        => 'https://github.com/ichord/Caret.js',
			'download_url'      => 'https://github.com/ichord/Caret.js/archive/master.zip',
			'library_path'      => '{e_PLUGIN}mentions/js/ichord.caret/',
			'version_arguments' => [
				'file'    => 'dist/jquery.caret.js',
				'pattern' => '/@version\s(\d+\.\d+\.\d+)/',
				'lines'   => 25,
			],

			'files' => [

				'js' => [
					'dist/jquery.caret.js' => [
						'type' => 'footer'
					]
				]
			],

			'variants' => [
				'minified' => [
					'js' => [
						'dist/jquery.caret.min.js' => [
							'type' => 'footer'
						]

					]
				]
			]

		];

		$libraries['ichord.atwho'] = [
			// Only used in administrative UI of Libraries API.
			'name'              => 'At.js',
			'vendor_url'        => 'https://github.com/ichord/At.js',
			'download_url'      => 'https://github.com/ichord/At.js/archive/master.zip',
			'library_path'      => '{e_PLUGIN}mentions/js/ichord.atwho/',
			'version_arguments' => [
				'file'    => 'dist/js/jquery.atwho.js',
				//  * at.js - 1.5.4
				'pattern' => '/.*(\d+\.\d+\.\d+)/',
				'lines'   => 6,
			],

		    'files' => [
			    'css' => [
				    'dist/css/jquery.atwho.css' => [
					    'zone' => 2
				    ]
			    ],

		    	'js' => [
		    		'dist/js/jquery.atwho.js' => [
					    'type' => 'footer'
				    ]
			    ],


		    ],

		    'variants' => [
		    	'minified' => [
		    		'css' => [
		    			'dist/css/jquery.atwho.min.css' => [
						    'zone' => 2
					    ]
				    ],
			        'js' => [
			        	'dist/js/jquery.atwho.min.js' => [
					        'type' => 'footer'
				        ]
			        ]
			    ]
		    ]

		];



		return $libraries;
	}

}
