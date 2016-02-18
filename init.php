<?php defined('SYSPATH') or die('No direct script access.');

Route::set('catalog_basket', 'basket(?<query>)')
	->defaults(array(
		'directory' => 'modules',
		'controller' => 'catalog_basket',
		'action' => 'index',
	));
Route::set('catalog_favorites', 'favorites(?<query>)')
	->defaults(array(
		'directory' =>	'modules',
		'controller' => 'catalog_favorites',
		'action' => 'index',
	));
Route::set('catalog_order', 'order(/<action>)(?<query>)')
	->defaults(array(
		'directory' =>	'modules',
		'controller' => 'catalog_order',
		'action' => 'index',
	));