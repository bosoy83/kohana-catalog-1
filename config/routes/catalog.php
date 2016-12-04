<?php defined('SYSPATH') or die('No direct script access.');

return array (
	'catalog_element' => array(
		'uri_callback' => '/<element_uri>-<element_id>.html(?<query>)',
		'regex' => array(
			'element_id' => '[0-9]++'
		),
		'defaults' => array(
			'directory' => 'modules',
			'controller' => 'catalog',
			'action' => 'detail',
		)
	),
	'catalog' => array(
		'uri_callback' => array('Helper_Catalog', 'route'), 
		'regex' => '(/<category_uri>)(?<query>)',
		'defaults' => array(
			'directory' => 'modules',
			'controller' => 'catalog',
			'action' => 'index',
		)
	),
);

