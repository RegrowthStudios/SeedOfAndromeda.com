<?php

class EWRporta_ControllerAdmin_Categories extends XenForo_ControllerAdmin_Abstract
{
	protected function _preDispatch($action)
	{
		$this->assertAdminPermission('node');
	}

	public function actionIndex()
	{
		$viewParams = array(
			'categories' => $this->getModelFromCache('EWRporta_Model_Categories')->getCategories()
		);

		return $this->responseView('EWRporta_ViewAdmin_Categories', 'EWRporta_Categories', $viewParams);
	}

	public function actionAdd()
	{
		$styles = $this->getModelFromCache('XenForo_Model_Style')->getStylesForOptionsTag();
		
		foreach ($styles AS &$style)
		{
			$style['indent'] = '';
			
			for ($i = 0; $i < $style['depth']; $i++)
			{
				$style['indent'] .= '&nbsp; &nbsp; ';
			}
		}
		
		$viewParams = array(
			'category' => array(),
			'styles' => $styles,
		);

		return $this->responseView('EWRporta_ViewAdmin_EditCategory', 'EWRporta_EditCategory', $viewParams);
	}

	public function actionEdit()
	{
		$categorySlug = $this->_input->filterSingle('category_slug', XenForo_Input::STRING);

		if (!$category = $this->getModelFromCache('EWRporta_Model_Categories')->getCategoryBySlug($categorySlug))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/categories'));
		}
		
		$styles = $this->getModelFromCache('XenForo_Model_Style')->getStylesForOptionsTag($category['style_id']);
		
		foreach ($styles AS &$style)
		{
			$style['indent'] = '';
			
			for ($i = 0; $i < $style['depth']; $i++)
			{
				$style['indent'] .= '&nbsp; &nbsp; ';
			}
		}

		$viewParams = array(
			'category' => $category,
			'styles' => $styles,
		);

		return $this->responseView('EWRporta_ViewAdmin_EditCategory', 'EWRporta_EditCategory', $viewParams);
	}

	public function actionSave()
	{
		$this->_assertPostOnly();

		$input = $this->_input->filter(array(
			'style_id' => XenForo_Input::UINT,
			'category_id' => XenForo_Input::UINT,
			'category_name' => XenForo_Input::STRING,
			'category_slug' => XenForo_Input::STRING,
			'category_type' => XenForo_Input::STRING,
		));

		$this->getModelFromCache('EWRporta_Model_Categories')->updateCategory($input);

		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/categories'));
	}

	public function actionDelete()
	{
		$categorySlug = $this->_input->filterSingle('category_slug', XenForo_Input::STRING);

		if (!$category = $this->getModelFromCache('EWRporta_Model_Categories')->getCategoryBySlug($categorySlug))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/categories'));
		}

		if ($this->isConfirmedPost())
		{
			$this->getModelFromCache('EWRporta_Model_Categories')->deleteCategory($category);
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/categories'));
		}
		else
		{
			$viewParams = array(
				'category' => $category
			);

			return $this->responseView('EWRporta_ViewAdmin_DeleteCategory', 'EWRporta_DeleteCategory', $viewParams);
		}
	}
}