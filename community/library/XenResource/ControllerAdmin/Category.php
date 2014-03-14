<?php

/**
 * Controller for resource categories
 */
class XenResource_ControllerAdmin_Category extends XenForo_ControllerAdmin_Abstract
{
	protected function _preDispatch($action)
	{
		$this->assertAdminPermission('resourceManager');
	}

	/**
	 * Lists all categories.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionIndex()
	{
		$permissionSets = $this->_getPermissionModel()->getUserCombinationsWithContentPermissions('resource_category');
		$categoriesWithPerms = array();
		foreach ($permissionSets AS $set)
		{
			$categoriesWithPerms[$set['content_id']] = true;
		}

		$viewParams = array(
			'categories' => $this->_getCategoryModel()->getAllCategories(),
			'categoriesWithPerms' => $categoriesWithPerms
		);
		return $this->responseView('XenResource_ViewAdmin_Category_List', 'resource_category_list', $viewParams);
	}

	/**
	 * Gets the category add/edit form response.
	 *
	 * @param array $category
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	protected function _getCategoryAddEditResponse(array $category)
	{
		$fieldModel = $this->_getFieldModel();
		$prefixModel = $this->_getPrefixModel();

		if (!empty($category['resource_category_id']))
		{
			$categories = $this->_getCategoryModel()->getPossibleParentCategories($category);
			$selectedFields = $fieldModel->getFieldIdsInCategory($category['resource_category_id']);

			$categoryPrefixes = array_keys($prefixModel->getPrefixesInCategory($category['resource_category_id']));
		}
		else
		{
			$categories = $this->_getCategoryModel()->getAllCategories();
			$selectedFields = array();

			$categoryPrefixes = array();
		}

		if (!empty($category['thread_node_id']))
		{
			$threadPrefixes = $this->getModelFromCache('XenForo_Model_ThreadPrefix')->getPrefixOptions(array(
				'node_id' => $category['thread_node_id']
			));
		}
		else
		{
			$threadPrefixes = array();
		}

		$fields = $fieldModel->prepareResourceFields($fieldModel->getResourceFields());

		$viewParams = array(
			'category' => $category,
			'categories' => $categories,
			'nodes' => $this->getModelFromCache('XenForo_Model_Node')->getAllNodes(),
			'threadPrefixes' => $threadPrefixes,

			'fieldsGrouped' => $fieldModel->groupResourceFields($fields),
			'fieldGroups' => $fieldModel->getResourceFieldGroups(),
			'selectedFields' => $selectedFields,

			'prefixGroups' => $prefixModel->getPrefixesByGroups(),
			'prefixOptions' => $prefixModel->getPrefixOptions(),
			'categoryPrefixes' => ($categoryPrefixes ? $categoryPrefixes : array(0))
		);
		return $this->responseView('XenResource_ViewAdmin_Category_Edit', 'resource_category_edit', $viewParams);
	}

	/**
	 * Displays a form to create a new category.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionAdd()
	{
		return $this->_getCategoryAddEditResponse(array(
			'display_order' => 1,
			'allow_local' => 1,
			'allow_external' => 1,
			'allow_commercial_external' => 1,
			'allow_fileless' => 1
		));
	}

	/**
	 * Displays a form to edit an existing category.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionEdit()
	{
		$category = $this->_getCategoryOrError();

		return $this->_getCategoryAddEditResponse($category);
	}

	/**
	 * Updates an existing media site or inserts a new one.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionSave()
	{
		$this->_assertPostOnly();

		$categoryId = $this->_input->filterSingle('resource_category_id', XenForo_Input::STRING);

		$dwInput = $this->_input->filter(array(
			'category_title' => XenForo_Input::STRING,
			'category_description' => XenForo_Input::STRING,
			'parent_category_id' => XenForo_Input::UINT,
			'display_order' => XenForo_Input::UINT,
			'allow_local' => XenForo_Input::UINT,
			'allow_external' => XenForo_Input::UINT,
			'allow_commercial_external' => XenForo_Input::UINT,
			'allow_fileless' => XenForo_Input::UINT,
			'thread_node_id' => XenForo_Input::UINT,
			'thread_prefix_id' => XenForo_Input::UINT,
			'always_moderate_create' => XenForo_Input::UINT,
			'always_moderate_update' => XenForo_Input::UINT,
			'require_prefix' => XenForo_Input::BOOLEAN
		));

		$input = $this->_input->filter(array(
			'available_fields' => array(XenForo_Input::STRING, 'array' => true),
			'available_prefixes' => array(XenForo_Input::UINT, 'array' => true)
		));

		$dw = XenForo_DataWriter::create('XenResource_DataWriter_Category');
		if ($categoryId)
		{
			$dw->setExistingData($categoryId);
		}
		$dw->bulkSet($dwInput);
		$dw->setExtraData(XenResource_DataWriter_Category::DATA_FIELD_IDS, $input['available_fields']);
		$dw->save();

		$this->_getPrefixModel()->updatePrefixCategoryAssociationByCategory(
			$dw->get('resource_category_id'), $input['available_prefixes']
		);

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildAdminLink('resource-categories') . $this->getLastHash($dw->get('resource_category_id'))
		);
	}

	/**
	 * Deletes the specified category
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionDelete()
	{
		if ($this->isConfirmedPost())
		{
			return $this->_deleteData(
				'XenResource_DataWriter_Category', 'resource_category_id',
				XenForo_Link::buildAdminLink('resource-categories/delete-clean-up', null, array(
					'resource_category_id' => $this->_input->filterSingle('resource_category_id', XenForo_Input::UINT),
					'_xfToken' => XenForo_Visitor::getInstance()->csrf_token_page
				))
			);
		}
		else // show confirmation dialog
		{
			$viewParams = array(
				'category' => $this->_getCategoryOrError()
			);
			return $this->responseView('XenResource_ViewAdmin_Category_Delete', 'resource_category_delete', $viewParams);
		}
	}

	public function actionDeleteCleanUp()
	{
		$this->_checkCsrfFromToken($this->_input->filterSingle('_xfToken', XenForo_Input::STRING));

		$id = $this->_input->filterSingle('resource_category_id', XenForo_Input::UINT);

		$info = $this->_getCategoryModel()->getCategoryById($id);
		if (!$id || $info)
		{
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
				XenForo_Link::buildAdminLink('resource-categories')
			);
		}

		$resources = $this->_getResourceModel()->getResources(array('resource_category_id' => $id), array('limit' => 100));
		if (!$resources)
		{
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
				XenForo_Link::buildAdminLink('resource-categories')
			);
		}

		$start = microtime(true);
		$limit = 10;

		foreach ($resources AS $resource)
		{
			$dw = XenForo_DataWriter::create('XenResource_DataWriter_Resource', XenForo_DataWriter::ERROR_SILENT);
			$dw->setExistingData($resource);
			$dw->delete();

			if ($limit && microtime(true) - $start > $limit)
			{
				break;
			}
		}

		return $this->responseView('XenResource_ViewAdmin_Category_DeleteCleanUp', 'resource_category_delete_clean_up', array(
			'resource_category_id' => $id
		));
	}

	/**
	 * Gets the specified record or errors.
	 *
	 * @param string $id
	 *
	 * @return array
	 */
	protected function _getCategoryOrError($id = null)
	{
		if ($id === null)
		{
			$id = $this->_input->filterSingle('resource_category_id', XenForo_Input::UINT);
		}

		$info = $this->_getCategoryModel()->getCategoryById($id);
		if (!$info)
		{
			throw $this->responseException($this->responseError(new XenForo_Phrase('requested_category_not_found'), 404));
		}

		return $info;
	}

	/**
	 * @return XenResource_Model_Resource
	 */
	protected function _getResourceModel()
	{
		return $this->getModelFromCache('XenResource_Model_Resource');
	}

	/**
	 * @return XenResource_Model_Category
	 */
	protected function _getCategoryModel()
	{
		return $this->getModelFromCache('XenResource_Model_Category');
	}

	/**
	 * @return XenResource_Model_ResourceField
	 */
	protected function _getFieldModel()
	{
		return $this->getModelFromCache('XenResource_Model_ResourceField');
	}

	/**
	 * @return XenResource_Model_Prefix
	 */
	protected function _getPrefixModel()
	{
		return $this->getModelFromCache('XenResource_Model_Prefix');
	}

	/**
	 * @return XenForo_Model_Permission
	 */
	protected function _getPermissionModel()
	{
		return $this->getModelFromCache('XenForo_Model_Permission');
	}
}