<?php defined('SYSPATH') or die('No direct script access.');

class Helper_Catalog {
	
	/**
	 * 'uri_callback' in route function for recursive categories.
	 * 'regex' used for generation links ('(/<category_uri>(/<element_uri>))(?<query>)')
	 * 
	 * @param string $uri
	 * @param Route $route
	 */
	public static function route($uri, $route)
	{
		$params = $route->defaults();
		$route_name = Route::name($route);
		$_d = strpos($route_name, '<->');
		if ($_d === FALSE) {
			return $params;
		}
		
		$page_id = (int) substr($route_name, 0, $_d);
		$url_base = Page_Route::dynamic_base_uri($page_id);
		$uri = substr($uri, strlen($url_base));
		$uri = trim($uri, '/');
		$sergments = explode('/', $uri);
		if (count($sergments) == 0) {
			return $params;
		}
		
		$_seg = array_pop($sergments);
		while ($_seg) {
			if (substr($_seg, -5) == '.html') {
				$params['element_uri'][] = substr($_seg, 0, -5);
			} else {
				$params['category_uri'][] = $_seg;
			}
			
			$_seg = array_pop($sergments);
		}

		return $params;
	}
	
	/**
	 * For catalog mode 'root'
	 * 'uri_callback' in route function for recursive categories.
	 * 'regex' used for generation links ('(/<category_uri>(/<element_uri>))(?<query>)')
	 * 
	 * @param string $uri
	 * @param Route $route
	 */
	public static function route_root($uri, $route)
	{
		$categories = category_uri_list();
		
		if ( ! in_array($uri, $categories)) {
			return FALSE;
		}
		
		$params = $route->defaults();
		$params['category_uri'] = array();
		$sergments = explode('/', $uri);
		foreach ($sergments as $_seg) {
			$params['category_uri'][] = $_seg;
		}

		return $params;
	}
	
}