<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Modules_Catalog_Category extends Controller_Admin_Modules_Catalog {

	private $not_deleted_categories = array();
	
	public function action_index()
	{
		$orm = ORM::factory('catalog_Category')
			->where('category_id', '=', $this->category_id);
		
		$paginator_orm = clone $orm;
		$paginator = new Paginator('admin/layout/paginator');
		$paginator
			->per_page(20)
			->count($paginator_orm->count_all());
		unset($paginator_orm);
		
		$categories = $orm
			->paginator($paginator)
			->find_all();
		
		$this->template
			->set_filename('modules/catalog/category/list')
			->set('list', $categories)
			->set('paginator', $paginator)
			->set('not_deleted_categories', $this->not_deleted_categories);
			
		$this->left_menu_category_list();
		$this->left_menu_category_add($orm);
		$this->left_menu_category_fix($orm);
		
		if ($this->category_id) {
			$category_orm = ORM::factory('catalog_Category', $this->category_id);
			if ( ! $category_orm->loaded()) {
				throw new HTTP_Exception_404();
			}
			$this->title = $category_orm->title;
			
			$this->left_menu_element_list($category_orm);
		} else {
			$this->title = __('Catalog');
		}
		$this->sub_title = __('Categories');
	}
	
	public function action_edit()
	{
		$request = $this->request->current();
		$id = (int) $request->param('id');
		$helper_orm = ORM_Helper::factory('catalog_Category');
		$orm = $helper_orm->orm();
		if ( (bool) $id) {
			$orm
				->and_where('id', '=', $id)
				->find();
			if ( ! $orm->loaded() OR ! $this->acl->is_allowed($this->user, $orm, 'edit')) {
				throw new HTTP_Exception_404();
			}
			$this->title = __('Edit category');
		} else {
			$this->title = __('Add category');
		}
		
		if (empty($this->back_url)) {
			$query_array = array(
				'category' => $this->category_id
			);
			$query_array = Paginator::query($request, $query_array);
			$this->back_url = Route::url('modules', array(
				'controller' => $this->_controller_name['category'],
				'query' => Helper_Page::make_query_string($query_array),
			));
		}
		
		if ($this->is_cancel) {
			$requset->redirect($this->back_url);
		}
	
		$errors = array();
		$submit = $request->post('submit');
		if ($submit) {
			try {
				if ( (bool) $id) {
					$orm->updater_id = $this->user->id;
					$orm->updated = date('Y-m-d H:i:s');
					$reload = FALSE;
				} else {
					$orm->creator_id = $this->user->id;
					$reload = TRUE;
				}
	
				$values = $this->meta_seo_reset(
					$request->post(),
					'meta_tags'
				);
				
				if (empty($values['uri'])) {
					$values['uri'] = transliterate_unique($values['title'], $orm, 'uri');
				}
				$values['level'] = $this->_get_level($values['category_id']);
				
				$helper_orm->save($values + $_FILES);
				
				if ($reload) {
					if ($submit != 'save_and_exit') {
						$this->back_url = Route::url('modules', array(
							'controller' => $request->controller(),
							'action' => $request->action(),
							'id' => $orm->id,
							'query' => Helper_Page::make_query_string($request->query()),
						));
					}
				
					$request
						->redirect($this->back_url);
				}
				
			} catch (ORM_Validation_Exception $e) {
				$errors = $this->errors_extract($e);
			}
		}
	
		if ( ! empty($errors) OR $submit != 'save_and_exit') {
			$categories = array(
				0 => __('-- Root category --')
			) + $this->_get_categories_list();
			
			$properties = $helper_orm->property_list();
			$this->template
				->set_filename('modules/catalog/category/edit')
				->set('errors', $errors)
				->set('helper_orm', $helper_orm)
				->set('categories', $categories)
				->set('not_deleted_categories', $this->not_deleted_categories)
				->set('properties', $properties);
			
			$this->left_menu_category_list();
			$this->left_menu_category_add($orm);
			$this->left_menu_element_list($orm);
		} else {
			$request
				->redirect($this->back_url);
		}
	}
	
	private function _get_level($category_id)
	{
		$level = 0;
		if ($category_id > 0) {
			$orm = ORM::factory('catalog_Category', $category_id);
			if ($orm->loaded()) {
				$level = (int) $orm->level + 1;
			}
		}
		return $level;
	}
	
	public function action_delete()
	{
		$request = $this->request->current();
		$id = (int) $request->param('id');
	
		$helper_orm = ORM_Helper::factory('catalog_Category');
		$orm = $helper_orm->orm();
		$orm
			->and_where('id', '=', $id)
			->find();
	
		if ( ! $orm->loaded() OR ! $this->acl->is_allowed($this->user, $orm, 'edit')) {
			throw new HTTP_Exception_404();
		}
		if (in_array($orm->id, $this->not_deleted_categories)) {
			throw new HTTP_Exception_404();
		}
	
		if ($this->element_delete($helper_orm)) {
			if (empty($this->back_url)) {
				$query_array = array(
					'category' => $this->category_id
				);
				$this->back_url = Route::url('modules', array(
					'controller' => $this->_controller_name['category'],
					'query' => Helper_Page::make_query_string($query_array),
				));
			}
			
			$request
				->redirect($this->back_url);
		}
	}
	
	public function action_position()
	{
		$request = $this->request->current();
		$id = (int) $request->param('id');
		$mode = $request->query('mode');
		$errors = array();
		$helper_orm = ORM_Helper::factory('catalog_Category');
		
		try {
			$this->element_position($helper_orm, $id, $mode);
		} catch (ORM_Validation_Exception $e) {
			$errors = $this->errors_extract($e);
		}
		
		if (empty($errors)) {
				
			if (empty($this->back_url)) {
				$query_array = array(
					'category' => $this->category_id
				);
	
				if ($mode != 'fix') {
					$query_array = Paginator::query($request, $query_array);
					$this->back_url = Route::url('modules', array(
						'controller' => $this->_controller_name['category'],
						'query' => Helper_Page::make_query_string($query_array),
					));
				} else {
					$this->back_url = Route::url('modules', array(
						'controller' => $this->_controller_name['category'],
						'query' => Helper_Page::make_query_string($query_array),
					));
				}
			}
				
			$request
				->redirect($this->back_url);
		}
	}
	
	protected function _get_breadcrumbs()
	{
		$breadcrumbs = parent::_get_breadcrumbs();
		
		$request = $this->request->current();
		if (in_array($request->action(), array('edit'))) {
			$id = (int) $request->param('id');
			$category_orm = ORM::factory('catalog_Category')
				->where('id', '=', $id)
				->find();
			if ($category_orm->loaded()) {
				$breadcrumbs[] = array(
					'title' => $category_orm->title.' ['.__('category edition').']',
				);
			} else {
				$breadcrumbs[] = array(
					'title' => ' ['.__('new category').']',
				);
			}
		}
		
		return $breadcrumbs;
	}
} 