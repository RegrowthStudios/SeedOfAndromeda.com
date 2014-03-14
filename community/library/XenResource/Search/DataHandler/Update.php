<?php

/**
 * Handles searching of resource updates.
 */
class XenResource_Search_DataHandler_Update extends XenForo_Search_DataHandler_Abstract
{
	protected $_resourceModel;

	/**
	 * Inserts into (or replaces a record) in the index.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::_insertIntoIndex()
	 */
	protected function _insertIntoIndex(XenForo_Search_Indexer $indexer, array $data, array $parentData = null)
	{
		$metadata = array();
		$metadata['resource'] = $data['resource_id'];

		if ($parentData)
		{
			$metadata['rescat'] = $parentData['resource_category_id'];
			$userId = $parentData['user_id'];

			if ($data['resource_update_id'] == $parentData['description_update_id'] || !$parentData['description_update_id'])
			{
				$data['message'] .= ' ' . $parentData['tag_line'];
				$metadata['is_resource'] = 1;
			}

			if (!empty($parentData['prefix_id']))
			{
				$metadata['resprefix'] = $parentData['prefix_id'];
			}
		}
		else
		{
			$userId = 0;
		}

		$indexer->insertIntoIndex(
			'resource_update', $data['resource_update_id'],
			$data['title'], $data['message'],
			$data['post_date'], $userId, $data['resource_id'], $metadata
		);
	}

	/**
	 * Updates a record in the index.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::_updateIndex()
	 */
	protected function _updateIndex(XenForo_Search_Indexer $indexer, array $data, array $fieldUpdates)
	{
		$indexer->updateIndex('resource_update', $data['resource_update_id'], $fieldUpdates);
	}

	/**
	 * Deletes one or more records from the index.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::_deleteFromIndex()
	 */
	protected function _deleteFromIndex(XenForo_Search_Indexer $indexer, array $dataList)
	{
		$updateIds = array();
		foreach ($dataList AS $data)
		{
			if (is_array($data))
			{
				$updateIds[] = $data['resource_update_id'];
			}
			else
			{
				$updateIds[] = $data;
			}
		}

		$indexer->deleteFromIndex('resource_update', $updateIds);
	}

	/**
	 * Rebuilds the index for a batch.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::rebuildIndex()
	 */
	public function rebuildIndex(XenForo_Search_Indexer $indexer, $lastId, $batchSize)
	{
		$updateIds = $this->_getUpdateModel()->getUpdateIdsInRange($lastId, $batchSize);
		if (!$updateIds)
		{
			return false;
		}

		$this->quickIndex($indexer, $updateIds);

		return max($updateIds);
	}

	/**
	 * Rebuilds the index for the specified content.

	 * @see XenForo_Search_DataHandler_Abstract::quickIndex()
	 */
	public function quickIndex(XenForo_Search_Indexer $indexer, array $contentIds)
	{
		$updates = $this->_getUpdateModel()->getUpdatesByIds($contentIds, array(
			'join' => XenResource_Model_Update::FETCH_RESOURCE
		));

		foreach ($updates AS $update)
		{
			$this->insertIntoIndex($indexer, $update, $update);
		}

		return true;
	}

	/**
	 * Gets the type-specific data for a collection of results of this content type.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::getDataForResults()
	 */
	public function getDataForResults(array $ids, array $viewingUser, array $resultsGrouped)
	{
		return $this->_getUpdateModel()->getUpdatesByIds($ids, array(
			'join' => XenResource_Model_Update::FETCH_RESOURCE |
				XenResource_Model_Update::FETCH_RESOURCE_VERSION |
				XenResource_Model_Update::FETCH_CATEGORY |
				XenResource_Model_Update::FETCH_USER,
			'permissionCombinationId' => $viewingUser['permission_combination_id']
		));
	}

	/**
	 * Determines if this result is viewable.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::canViewResult()
	 */
	public function canViewResult(array $result, array $viewingUser)
	{
		$categoryPermissions = XenForo_Permission::unserializePermissions($result['category_permission_cache']);

		return $this->_getUpdateModel()->canViewUpdateAndContainer(
			$result, $result, $result, $null, $viewingUser, $categoryPermissions
		);
	}

	/**
	 * Prepares a result for display.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::prepareResult()
	 */
	public function prepareResult(array $result, array $viewingUser)
	{
		return $this->_getUpdateModel()->prepareUpdate($result, $result, $result, $viewingUser);
	}

	/**
	 * Gets the date of the result (from the result's content).
	 *
	 * @see XenForo_Search_DataHandler_Abstract::getResultDate()
	 */
	public function getResultDate(array $result)
	{
		return $result['post_date'];
	}

	/**
	 * Renders a result to HTML.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::renderResult()
	 */
	public function renderResult(XenForo_View $view, array $result, array $search)
	{
		return $view->createTemplateObject('search_result_resource_update', array(
			'update' => $result,
			'resource' => $result,
			'search' => $search
		));
	}

	/**
	 * Returns an array of content types handled by this class
	 *
	 * @see XenForo_Search_DataHandler_Abstract::getSearchContentTypes()
	 */
	public function getSearchContentTypes()
	{
		return array('resource_update');
	}

	/**
	* Get type-specific constraints from input.
	*
	* @param XenForo_Input $input
	*
	* @return array
	*/
	public function getTypeConstraintsFromInput(XenForo_Input $input)
	{
		$constraints = array();

		$categories = $input->filterSingle('categories', XenForo_Input::UINT, array('array' => true));
		if ($categories && !in_array(0, $categories))
		{
			if ($input->inRequest('child_categories'))
			{
				$includeChildren = $input->filterSingle('child_categories', XenForo_Input::UINT);
			}
			else
			{
				$includeChildren = true;
			}

			if ($includeChildren)
			{
				$descendants = array_keys(XenForo_Model::create('XenResource_Model_Category')->getDescendantsOfCategoryIds($categories));
				$categories = array_merge($categories, $descendants);
			}

			$categories = array_unique($categories);
			$constraints['rescat'] = implode(' ', $categories);
			if (!$constraints['rescat'])
			{
				unset($constraints['rescat']); // just 0
			}
		}

		$prefixes = $input->filterSingle('prefixes', XenForo_Input::UINT, array('array' => true));
		if ($prefixes && reset($prefixes))
		{
			$prefixes = array_unique($prefixes);
			$constraints['resprefix'] = implode(' ', $prefixes);
			if (!$constraints['resprefix'])
			{
				unset($constraints['resprefix']); // just 0
			}
		}

		if ($input->filterSingle('is_resource', XenForo_Input::UINT)) {
			$constraints['is_resource'] = true;
		}

		return $constraints;
	}

	/**
	 * Process a type-specific constraint.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::processConstraint()
	 */
	public function processConstraint(XenForo_Search_SourceHandler_Abstract $sourceHandler, $constraint, $constraintInfo, array $constraints)
	{
		switch ($constraint)
		{
			case 'rescat':
				if ($constraintInfo)
				{
					return array(
						'metadata' => array('rescat', preg_split('/\D+/', strval($constraintInfo))),
					);
				}
				break;

			case 'resprefix':
				if ($constraintInfo)
				{
					return array(
						'metadata' => array('resprefix', preg_split('/\D+/', strval($constraintInfo))),
					);
				}

			case 'is_resource':
				return array(
					'metadata' => array('is_resource', array(1))
				);
		}

		return false;
	}

	/**
	 * Gets the search form controller response for this type.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::getSearchFormControllerResponse()
	 */
	public function getSearchFormControllerResponse(XenForo_ControllerPublic_Abstract $controller, XenForo_Input $input, array $viewParams)
	{
		/** @var $resourceModel XenResource_Model_Resource */
		$resourceModel = XenForo_Model::create('XenResource_Model_Resource');
		if (!$resourceModel->canViewResources($error))
		{
			return $controller->responseNoPermission();
		}

		$params = $input->filterSingle('c', XenForo_Input::ARRAY_SIMPLE);

		if (!empty($params['rescat']))
		{
			$viewParams['search']['categories'] = array_fill_keys(explode(' ', $params['rescat']), true);
		}
		else
		{
			$viewParams['search']['categories'] = array();
		}

		$viewParams['search']['child_categories'] = true;
		$viewParams['categories'] = XenForo_Model::create('XenResource_Model_Category')->getViewableCategories();

		if (!empty($params['prefix']))
		{
			$viewParams['search']['prefixes'] = array_fill_keys(explode(' ', $params['prefix']), true);
		}
		else
		{
			$viewParams['search']['prefixes'] = array();
		}

		/** @var $prefixModel XenResource_Model_Prefix */
		$prefixModel = XenForo_Model::create('XenResource_Model_Prefix');

		$viewParams['prefixes'] = $prefixModel->getPrefixesByGroups();
		if ($viewParams['prefixes'])
		{
			$visiblePrefixes = $prefixModel->getVisiblePrefixIds();
			foreach ($viewParams['prefixes'] AS $key => $prefixes)
			{
				foreach ($prefixes AS $prefixId => $prefix)
				{
					if (!isset($visiblePrefixes[$prefixId]))
					{
						unset($prefixes[$prefixId]);
					}
				}

				if (!count($prefixes))
				{
					unset($viewParams['prefixes'][$key]);
				}
			}
		}

		$viewParams['search']['is_resource'] = !empty($params['is_resource']);

		return $controller->responseView('XenResource_ViewPublic_Search_Form_ResourceUpdate', 'search_form_resource_update', $viewParams);
	}

	/**
	 * @return XenResource_Model_Resource
	 */
	protected function _getResourceModel()
	{
		if (!$this->_resourceModel)
		{
			$this->_resourceModel = XenForo_Model::create('XenResource_Model_Resource');
		}

		return $this->_resourceModel;
	}

	/**
	* @return XenResource_Model_Update
	*/
	protected function _getUpdateModel()
	{
		if (!$this->_resourceModel)
		{
			$this->_resourceModel = XenForo_Model::create('XenResource_Model_Update');
		}

		return $this->_resourceModel;
	}
}