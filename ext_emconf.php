<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "rkw_geolocation"
 *
 * Auto generated by Extension Builder 2015-08-27
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'RKW Geolocation',
	'description' => 'Locates addresses and / or zip codes',
	'category' => 'plugin',
    'author' => 'Maximilian Fäßler, Steffen Kroggel',
    'author_email' => 'faesslerweb@web.de, developer@steffenkroggel.de',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '7.6.12',
	'constraints' => array(
		'depends' => array(
			'extbase' => '7.6.0-7.6.99',
			'fluid' => '7.6.0-7.6.99',
			'typo3' => '7.6.0-7.6.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);