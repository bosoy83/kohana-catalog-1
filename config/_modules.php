<?php defined('SYSPATH') or die('No direct access allowed.');

return array
(
	'catalog' => array(
		'alias' => 'greor-catalog',
		'name' => 'Catalog module',
		'type' => Helper_Module::MODULE_SINGLE,
		'controller' => 'catalog_category'
	),
);