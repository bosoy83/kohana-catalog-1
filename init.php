<?php defined('SYSPATH') or die('No direct script access.');

/*
 * TODO: добавить кеширование
 */
function category_uri_list() {
	$list = ORM::factory('Catalog_Category')
		->order_by('level', 'asc')
		->find_all();
	
	$result = array();
	foreach ($list as $_orm) {
		if ($_orm->level > 0 AND array_key_exists($_orm->category_id, $result)) {
			$result[$_orm->id] = $result[$_orm->category_id].'/'.$_orm->uri;
		} elseif ($_orm->level == 0) {
			$result[$_orm->id] = $_orm->uri;
		}
	}
	return $result;
}

$module_type = Kohana::$config->load('catalog.mode');

if ($module_type === 'root') {
	
	$routes = Kohana::$config->load('routes/catalog')
		->as_array();
	
	foreach ($routes as $_name => $_config) {
		Route::set($_name, Arr::get($_config, 'uri_callback'), Arr::get($_config, 'regex'))
			->defaults(Arr::get($_config, 'defaults'));
	}
}
