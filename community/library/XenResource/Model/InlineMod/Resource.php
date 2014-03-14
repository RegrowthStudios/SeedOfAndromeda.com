<?php

class XenResource_Model_InlineMod_Resource extends XenForo_Model
{
	public $enableLogging = true;

	/**
	 * Determines if the selected resource IDs can be deleted.
	 *
	 * @param array $resourceIds List of IDs check
	 * @param string $deleteType The type of deletion being requested (soft or hard)
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean
	 */
	public function canDeleteResources(array $resourceIds, $deleteType = 'soft', &$errorKey = '', array $viewingUser = null)
	{
		list($resources, $categories) = $this->getResourcesAndParentData($resourceIds);
		return $this->canDeleteResourcesData($resources, $deleteType, $categories, $errorKey, $viewingUser);
	}

	/**
	 * Determines if the selected resource data can be deleted.
	 *
	 * @param array $resources List of data to be deleted
	 * @param string $deleteType Type of deletion (soft or hard)
	 * @param array $categories List of categories the resources are in
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean
	 */
	public function canDeleteResourcesData(array $resources, $deleteType, array $categories, &$errorKey = '', array $viewingUser = null)
	{
		if (!$resources)
		{
			return true;
		}

		$this->standardizeViewingUserReference($viewingUser);
		$resourceModel = $this->_getResourceModel();

		foreach ($resources AS $resource)
		{
			$category = $categories[$resource['resource_category_id']];
			if ($resource['resource_state'] != 'deleted' && !$resourceModel->canDeleteResource($resource, $category, $deleteType, $errorKey, $viewingUser))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Deletes the specified resources if permissions are sufficient.
	 *
	 * @param array $resourceIds List of IDs to delete
	 * @param array $options Options that control the delete. Supports deleteType (soft or hard).
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean True if permissions were ok
	 */
	public function deleteResources(array $resourceIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
	{
		$options = array_merge(
			array(
				'deleteType' => '',
				'reason' => ''
			), $options
		);

		if (!$options['deleteType'])
		{
			throw new XenForo_Exception('No deletion type specified.');
		}

		list($resources, $categories) = $this->getResourcesAndParentData($resourceIds);

		if (empty($options['skipPermissions']) && !$this->canDeleteResourcesData($resources, $options['deleteType'], $categories, $errorKey, $viewingUser))
		{
			return false;
		}

		foreach ($resources AS $resource)
		{
			$dw = XenForo_DataWriter::create('XenResource_DataWriter_Resource', XenForo_DataWriter::ERROR_SILENT);
			$dw->setExistingData($resource);
			if (!$dw->get('resource_id'))
			{
				continue;
			}

			if ($options['deleteType'] == 'hard')
			{
				$dw->delete();
			}
			else
			{
				$dw->setExtraData(XenForo_DataWriter_DiscussionMessage::DATA_DELETE_REASON, $options['reason']);
				$dw->set('resource_state', 'deleted');
				$dw->save();
			}

			if ($this->enableLogging)
			{
				XenForo_Model_Log::logModeratorAction(
					'resource', $resource, 'delete_' . $options['deleteType'], array('reason' => $options['reason'])
				);
			}
		}

		return true;
	}

	/**
	 * Determines if the selected resource IDs can be undeleted.
	 *
	 * @param array $resourceIds List of IDs to check
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean
	 */
	public function canUndeleteResources(array $resourceIds, &$errorKey = '', array $viewingUser = null)
	{
		list($resources, $categories) = $this->getResourcesAndParentData($resourceIds);
		return $this->canUndeleteResourcesData($resources, $categories, $errorKey, $viewingUser);
	}

	/**
	 * Determines if the selected resource data can be undeleted.
	 *
	 * @param array $resources List of data to be checked
	 * @param array $categories List of categories the resources are in
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean
	 */
	public function canUndeleteResourcesData(array $resources, array $categories, &$errorKey = '', array $viewingUser = null)
	{
		if (!$resources)
		{
			return true;
		}

		$this->standardizeViewingUserReference($viewingUser);
		$resourceModel = $this->_getResourceModel();

		foreach ($resources AS $resource)
		{
			$category = $categories[$resource['resource_category_id']];
			if ($resource['resource_state'] == 'deleted' && !$resourceModel->canUndeleteResource($resource, $category, $errorKey, $viewingUser))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Undeletes the specified resources if permissions are sufficient.
	 *
	 * @param array $resourceIds List of IDs to undelete
	 * @param array $options Options that control the action. Nothing supported at this time.
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean True if permissions were ok
	 */
	public function undeleteResources(array $resourceIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
	{
		list($resources, $categories) = $this->getResourcesAndParentData($resourceIds);

		if (empty($options['skipPermissions']) && !$this->canUndeleteResourcesData($resources, $categories, $errorKey, $viewingUser))
		{
			return false;
		}

		$this->_updateResourcesResourceState($resources, $categories, 'visible', 'deleted');

		return true;
	}

	/**
	 * Determines if the selected resource IDs can be approved.
	 *
	 * @param array $resourceIds List of IDs to check
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean
	 */
	public function canApproveResources(array $resourceIds, &$errorKey = '', array $viewingUser = null)
	{
		list($resources, $categories) = $this->getResourcesAndParentData($resourceIds);
		return $this->canApproveResourcesData($resources, $categories, $errorKey, $viewingUser);
	}

	/**
	 * Determines if the selected resource data can be approved.
	 *
	 * @param array $resources List of data to be checked
	 * @param array $categories List of categories the resources are in
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean
	 */
	public function canApproveResourcesData(array $resources, array $categories, &$errorKey = '', array $viewingUser = null)
	{
		if (!$resources)
		{
			return true;
		}

		$this->standardizeViewingUserReference($viewingUser);
		$resourceModel = $this->_getResourceModel();

		foreach ($resources AS $resource)
		{
			$category = $categories[$resource['resource_category_id']];
			if ($resource['resource_state'] == 'moderated' && !$resourceModel->canApproveResource($resource, $category, $errorKey, $viewingUser))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Approves the specified resources if permissions are sufficient.
	 *
	 * @param array $resourceIds List of IDs to approve
	 * @param array $options Options that control the action. Nothing supported at this time.
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean True if permissions were ok
	 */
	public function approveResources(array $resourceIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
	{
		list($resources, $categories) = $this->getResourcesAndParentData($resourceIds);

		if (empty($options['skipPermissions']) && !$this->canApproveResourcesData($resources, $categories, $errorKey, $viewingUser))
		{
			return false;
		}

		$this->_updateResourcesResourceState($resources, $categories, 'visible', 'moderated');

		return true;
	}

	/**
	 * Determines if the selected resource IDs can be unapproved.
	 *
	 * @param array $resourceIds List of IDs to check
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean
	 */
	public function canUnapproveResources(array $resourceIds, &$errorKey = '', array $viewingUser = null)
	{
		list($resources, $categories) = $this->getResourcesAndParentData($resourceIds);
		return $this->canUnapproveResourcesData($resources, $categories, $errorKey, $viewingUser);
	}

	/**
	 * Determines if the selected resource data can be unapproved.
	 *
	 * @param array $resources List of data to be checked
	 * @param array $categories List of categories the resources are in
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean
	 */
	public function canUnapproveResourcesData(array $resources, array $categories, &$errorKey = '', array $viewingUser = null)
	{
		if (!$resources)
		{
			return true;
		}

		$this->standardizeViewingUserReference($viewingUser);
		$resourceModel = $this->_getResourceModel();

		foreach ($resources AS $resource)
		{
			$category = $categories[$resource['resource_category_id']];
			if ($resource['resource_state'] == 'visible' && !$resourceModel->canUnapproveResource($resource, $category, $errorKey, $viewingUser))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Unapproves the specified resources if permissions are sufficient.
	 *
	 * @param array $resourceIds List of IDs to unapprove
	 * @param array $options Options that control the action. Nothing supported at this time.
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean True if permissions were ok
	 */
	public function unapproveResources(array $resourceIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
	{
		list($resources, $categories) = $this->getResourcesAndParentData($resourceIds);

		if (empty($options['skipPermissions']) && !$this->canUnapproveResourcesData($resources, $categories, $errorKey, $viewingUser))
		{
			return false;
		}

		$this->_updateResourcesResourceState($resources, $categories, 'moderated', 'visible');

		return true;
	}

	/**
	 * Determines if the selected resource IDs can be featured.
	 *
	 * @param array $resourceIds List of IDs to check
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean
	 */
	public function canFeatureResources(array $resourceIds, &$errorKey = '', array $viewingUser = null)
	{
		list($resources, $categories) = $this->getResourcesAndParentData($resourceIds);
		return $this->canFeatureResourcesData($resources, $categories, $errorKey, $viewingUser);
	}

	/**
	 * Determines if the selected resource data can be featured.
	 *
	 * @param array $resources List of data to be checked
	 * @param array $categories List of categories the resources are in
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean
	 */
	public function canFeatureResourcesData(array $resources, array $categories, &$errorKey = '', array $viewingUser = null)
	{
		if (!$resources)
		{
			return true;
		}

		$this->standardizeViewingUserReference($viewingUser);
		$resourceModel = $this->_getResourceModel();

		foreach ($resources AS $resource)
		{
			$category = $categories[$resource['resource_category_id']];
			if (!$resourceModel->canFeatureUnfeatureResource($resource, $category, $errorKey, $viewingUser))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Features the specified resources if permissions are sufficient.
	 *
	 * @param array $resourceIds List of IDs to approve
	 * @param array $options Options that control the action. Nothing supported at this time.
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean True if permissions were ok
	 */
	public function featureResources(array $resourceIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
	{
		list($resources, $categories) = $this->getResourcesAndParentData($resourceIds);

		if (empty($options['skipPermissions']) && !$this->canFeatureResourcesData($resources, $categories, $errorKey, $viewingUser))
		{
			return false;
		}

		foreach ($resources AS $resource)
		{
			$this->_getResourceModel()->featureResource($resource);
		}

		return true;
	}

	/**
	 * Determines if the selected resource IDs can be unfeatured.
	 *
	 * @param array $resourceIds List of IDs to check
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean
	 */
	public function canUnfeatureResources(array $resourceIds, &$errorKey = '', array $viewingUser = null)
	{
		list($resources, $categories) = $this->getResourcesAndParentData($resourceIds);
		return $this->canUnfeatureResourcesData($resources, $categories, $errorKey, $viewingUser);
	}

	/**
	 * Determines if the selected resource data can be unfeatured.
	 *
	 * @param array $resources List of data to be checked
	 * @param array $categories List of categories the resources are in
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean
	 */
	public function canUnfeatureResourcesData(array $resources, array $categories, &$errorKey = '', array $viewingUser = null)
	{
		if (!$resources)
		{
			return true;
		}

		$this->standardizeViewingUserReference($viewingUser);
		$resourceModel = $this->_getResourceModel();

		foreach ($resources AS $resource)
		{
			$category = $categories[$resource['resource_category_id']];
			if (!$resourceModel->canFeatureUnfeatureResource($resource, $category, $errorKey, $viewingUser))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Unfeatures the specified resources if permissions are sufficient.
	 *
	 * @param array $resourceIds List of IDs to approve
	 * @param array $options Options that control the action. Nothing supported at this time.
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean True if permissions were ok
	 */
	public function unfeatureResources(array $resourceIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
	{
		list($resources, $categories) = $this->getResourcesAndParentData($resourceIds);

		if (empty($options['skipPermissions']) && !$this->canUnfeatureResourcesData($resources, $categories, $errorKey, $viewingUser))
		{
			return false;
		}

		foreach ($resources AS $resource)
		{
			$this->_getResourceModel()->unfeatureResource($resource);
		}

		return true;
	}

	/**
	 * Determines if the selected resource IDs can be reassigned.
	 *
	 * @param array $resourceIds List of IDs to check
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean
	 */
	public function canReassignResources(array $resourceIds, &$errorKey = '', array $viewingUser = null)
	{
		list($resources, $categories) = $this->getResourcesAndParentData($resourceIds);
		return $this->canReassignResourcesData($resources, $categories, $errorKey, $viewingUser);
	}

	/**
	 * Determines if the selected resource data can be reassigned.
	 *
	 * @param array $resources List of data to be checked
	 * @param array $categories List of categories the resources are in
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean
	 */
	public function canReassignResourcesData(array $resources, array $categories, &$errorKey = '', array $viewingUser = null)
	{
		if (!$resources)
		{
			return true;
		}

		$this->standardizeViewingUserReference($viewingUser);
		$resourceModel = $this->_getResourceModel();

		foreach ($resources AS $resource)
		{
			$category = $categories[$resource['resource_category_id']];
			if (!$resourceModel->canReassignResource($resource, $category, $errorKey, $viewingUser))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Reassigns the specified resources if permissions are sufficient.
	 *
	 * @param array $resourceIds List of IDs to unapprove
	 * @param array $options Options that control the action. Nothing supported at this time.
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean True if permissions were ok
	 */
	public function reassignResources(array $resourceIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
	{
		$options = array_merge(
			array(
				'userId' => '',
				'username' => ''
			), $options
		);

		if (!$options['userId'] || !$options['username'])
		{
			throw new XenForo_Exception('No user ID/username specified.');
		}

		list($resources, $categories) = $this->getResourcesAndParentData($resourceIds);

		if (empty($options['skipPermissions']) && !$this->canReassignResourcesData($resources, $categories, $errorKey, $viewingUser))
		{
			return false;
		}

		foreach ($resources AS $resource)
		{
			$dw = XenForo_DataWriter::create('XenResource_DataWriter_Resource', XenForo_DataWriter::ERROR_SILENT);
			$dw->setExistingData($resource);
			if (!$dw->get('resource_id'))
			{
				continue;
			}

			$dw->bulkSet(array(
				'user_id' => $options['userId'],
				'username' => $options['username']
			));

			if ($dw->save() && $this->enableLogging)
			{
				XenForo_Model_Log::logModeratorAction(
					'resource', $resource, 'reassign', array('from' => $dw->getExisting('username'))
				);
			}
		}

		return true;
	}

	/**
	 * Determines if the selected resource IDs can be moved.
	 *
	 * @param array $resourceIds List of IDs to check
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean
	 */
	public function canMoveResources(array $resourceIds, &$errorKey = '', array $viewingUser = null)
	{
		list($resources, $categories) = $this->getResourcesAndParentData($resourceIds);
		return $this->canReassignResourcesData($resources, $categories, $errorKey, $viewingUser);
	}

	/**
	 * Determines if the selected resource data can be moved.
	 *
	 * @param array $resources List of data to be checked
	 * @param array $categories List of categories the resources are in
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean
	 */
	public function canMoveResourcesData(array $resources, array $categories, &$errorKey = '', array $viewingUser = null)
	{
		if (!$resources)
		{
			return true;
		}

		$this->standardizeViewingUserReference($viewingUser);
		$resourceModel = $this->_getResourceModel();

		foreach ($resources AS $resource)
		{
			$category = $categories[$resource['resource_category_id']];
			if (!$resourceModel->canEditResource($resource, $category, $errorKey, $viewingUser))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Moves the specified resources if permissions are sufficient.
	 *
	 * @param array $resourceIds List of IDs to move
	 * @param array $options Options that control the action. Nothing supported at this time.
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean True if permissions were ok
	 */
	public function moveResources(array $resourceIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
	{
		$options = array_merge(
			array(
				'categoryId' => 0
			), $options
		);

		if (!$options['categoryId'] )
		{
			throw new XenForo_Exception('No category ID specified.');
		}

		list($resources, $categories) = $this->getResourcesAndParentData($resourceIds);

		if (empty($options['skipPermissions']) && !$this->canMoveResourcesData($resources, $categories, $errorKey, $viewingUser))
		{
			return false;
		}

		foreach ($resources AS $resource)
		{
			$dw = XenForo_DataWriter::create('XenResource_DataWriter_Resource', XenForo_DataWriter::ERROR_SILENT);
			$dw->setExistingData($resource);
			if (!$dw->get('resource_id'))
			{
				continue;
			}

			$dw->set('resource_category_id', $options['categoryId']);

			if ($dw->save() && $this->enableLogging)
			{
				XenForo_Model_Log::logModeratorAction(
					'resource', $resource, 'edit', array('resource_category_id' => $dw->getExisting('resource_category_id'))
				);
			}
		}

		return true;
	}


	/**
	 * Checks a standard permission against a collection of resources.
	 * True is returned only if the action is possible on all resources.
	 *
	 * @param string $permissionMethod Name of the permission method to call in the  model
	 * @param array $resources List of resources to check
	 * @param array $categories List of categories the resources are in
	 * @param string $errorKey Modified by reference. If no permission, may include a key of a phrase that gives more info
	 * @param array|null $viewingUser Viewing user reference
	 *
	 * @return boolean
	 */
	protected function _checkPermissionOnResources($permissionMethod, array $resources, array $categories, &$errorKey = '', array $viewingUser = null)
	{
		if (!$resources)
		{
			return true;
		}

		$this->standardizeViewingUserReference($viewingUser);
		$resourceModel = $this->_getResourceModel();

		foreach ($resources AS $resource)
		{
			$category = $categories[$resource['resource_category_id']];
			if (!$resourceModel->$permissionMethod($resource, $category, $errorKey, $viewingUser))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Internal helper to update the resource_state of a collection of resources.
	 *
	 * @param array $resources Information about the resources to update
	 * @param array $categories List of categories the resources are in
	 * @param string $newState New message state (visible, moderated, deleted)
	 * @param string|bool $expectedOldState If specified, only updates if the old state matches
	 */
	protected function _updateResourcesResourceState(array $resources, array $categories, $newState, $expectedOldState = false)
	{
		switch ($newState)
		{
			case 'visible':
				switch (strval($expectedOldState))
				{
					case 'visible': return;
					case 'moderated': $logAction = 'approve'; break;
					case 'deleted': $logAction = 'undelete'; break;
					default: $logAction = 'undelete'; break;
				}
				break;

			case 'moderated':
				switch (strval($expectedOldState))
				{
					case 'visible': $logAction = 'unapprove'; break;
					case 'moderated': return;
					case 'deleted': $logAction = 'unapprove'; break;
					default: $logAction = 'unapprove'; break;
				}
				break;

			case 'deleted':
				switch (strval($expectedOldState))
				{
					case 'visible': $logAction = 'delete_soft'; break;
					case 'moderated': $logAction = 'delete_soft'; break;
					case 'deleted': return;
					default: $logAction = 'delete_soft'; break;
				}
				break;

			default: return;
		}

		foreach ($resources AS $resource)
		{
			if ($expectedOldState && $resource['resource_state'] != $expectedOldState)
			{
				continue;
			}

			$dw = XenForo_DataWriter::create('XenResource_DataWriter_Resource', XenForo_DataWriter::ERROR_SILENT);
			$dw->setExistingData($resource);
			$dw->set('resource_state', $newState);
			$dw->save();

			if ($this->enableLogging)
			{
				XenForo_Model_Log::logModeratorAction('resource', $resource, $logAction, array());
			}
		}
	}

	/**
	 * Gets information about the category a where resource has been posted.
	 *
	 * @param array $resource Info about the resource
	 * @param array $categories List of categories the resource could be in
	 *
	 * @return array User info
	 */
	protected function _getCategoryFromResource(array $resource, array $categories)
	{
		return $categories[$resource['resource_category_id']];
	}

	/**
	 * From a List of IDs, gets info about the resources and the categories they are in.
	 *
	 * @param array $resourceIds List of IDs
	 *
	 * @return array Format: [0] => list of resources, [1] => list of categories
	 */
	public function getResourcesAndParentData(array $resourceIds)
	{
		$resources = $this->_getResourceModel()->getResourcesByIds($resourceIds);

		$categoryIds = array();
		foreach ($resources AS $resource)
		{
			$categoryIds[$resource['resource_category_id']] = true;
		}
		$categories = $this->_getCategoryModel()->getCategoriesByIds(array_keys($categoryIds), array(
			'permissionCombinationId' => XenForo_Visitor::getInstance()->permission_combination_id
		));
		$this->_getCategoryModel()->bulkSetCategoryPermCache(null, $categories, 'category_permission_cache');

		return array($resources, $categories);
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
}