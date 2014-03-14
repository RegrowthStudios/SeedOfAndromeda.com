<?php

class XenResource_ContentPermission_Category implements XenForo_ContentPermission_Interface
{
	/**
	 * Tracks whether we've initialized the data. Many calls to
	 * {@link rebuildContentPermissions()} may happen on one object.
	 *
	 * @var boolean
	 */
	protected $_initialized = false;

	/**
	 * Permission model
	 *
	 * @var XenForo_Model_Permission
	 */
	protected $_permissionModel = null;

	/**
	 * Global perms that apply to this call to rebuild the permissions. These permissions
	 * can be manipulated if necessary and the global permissions will actually be modified.
	 *
	 * @var array
	 */
	protected $_globalPerms = array();

	/**
	 * The tree hierarchy. This data is traversed to build permissions.
	 *
	 * @var array Format: [parent category id][category id] => info
	 */
	protected $_categoryTree = array();

	/**
	 * @var array [category id] => info
	 */
	protected $_categories = array();

	/**
	 * All permission entries for the tree, grouped by system, user group, and user.
	 *
	 * @var array
	 */
	protected $_permissionEntries = array();

	/**
	 * Builds the permissions for a user collection.
	 *
	 * @param XenForo_Model_Permission $permissionModel Permission model that called this.
	 * @param array $userGroupIds List of user groups for the collection
	 * @param integer $userId User ID for the collection, if there are custom permissions
	 * @param array $permissionsGrouped List of all valid permissions, grouped
	 * @param array $globalPerms The global permissions that apply to this combination
	 *
	 * @return array
	 */
	public function rebuildContentPermissions(
		$permissionModel, array $userGroupIds, $userId, array $permissionsGrouped, array &$globalPerms
	)
	{
		$this->_permissionModel = $permissionModel;
		$this->_globalPerms = $globalPerms;

		$this->_setup();

		$finalPermissions = $this->_buildTreePermissions($userId, $userGroupIds, $globalPerms, $permissionsGrouped);

		$globalPerms = $this->_globalPerms;
		return $finalPermissions;
	}

	/**
	 * Sets up the necessary information about the tree, existing permission entries,
	 * etc. Only runs if not initialized.
	 */
	protected function _setup()
	{
		if ($this->_initialized)
		{
			return;
		}

		$categoryModel = $this->_getCategoryModel();

		$this->_categories = $categoryModel->getAllCategories();
		$this->_categoryTree = $categoryModel->groupCategoriesByParent($this->_categories);
		$this->_permissionEntries = $this->_permissionModel->getAllContentPermissionEntriesByTypeGrouped('resource_category');

		$this->_initialized = true;
	}

	/**
	 * Allows the data to be injected manually. Generally only needed for testing.
	 *
	 * @param array $categories
	 * @param array $permissionEntries
	 */
	public function setCategoryDataManually(array $categories, array $permissionEntries)
	{
		$categoryModel = $this->_getCategoryModel();

		$this->_categories = $categories;
		$this->_categoryTree = $categoryModel->groupCategoriesByParent($categories);

		$this->_permissionEntries = $permissionEntries;

		$this->_initialized = true;
	}

	public function getContentPermissionDetails(
		$permissionModel, array $userGroupIds, $userId, $contentId, array $permission,
		array $permissionsGrouped, array $globalPerms
	)
	{
		$this->_globalPerms = $globalPerms;
		$this->_permissionModel = $permissionModel;
		$this->_setup();

		$path = array();
		$categoryId = $contentId;
		while ($categoryId && isset($this->_categories[$categoryId]))
		{
			array_unshift($path, $categoryId);
			$categoryId = $this->_categories[$categoryId]['parent_category_id'];
		}

		$output = array();
		$permissionId = $permission['permission_id'];
		$permissionGroupId = $permission['permission_group_id'];

		$categoryTree = array();
		foreach ($path AS $categoryId)
		{
			$category = $this->_categories[$categoryId];
			$categoryTree[$category['parent_category_id']][$categoryId] = $category;
		}

		$oldCategoryTree = $this->_categoryTree;
		$this->_categoryTree = $categoryTree;

		$finalPermissions = $this->_buildTreePermissions($userId, $userGroupIds, $globalPerms, $permissionsGrouped);
		$this->_categoryTree = $oldCategoryTree;

		foreach ($path AS $categoryId)
		{
			$category = $this->_categories[$categoryId];

			$groupEntries = $this->_getUserGroupPermissionEntries($categoryId, $userGroupIds);
			$userEntries = $this->_getUserPermissionEntries($categoryId, $userId);
			$categoryWideEntries = $this->_getCategoryWideEntries($categoryId);

			$groups = array();
			foreach ($userGroupIds AS $userGroupId)
			{
				$groups[$userGroupId] =
					isset($groupEntries[$userGroupId][$permissionGroupId][$permissionId])
					? $groupEntries[$userGroupId][$permissionGroupId][$permissionId]
					: false;
			}

			$final = false;
			if ($permissionGroupId == 'resource')
			{
				$final = $finalPermissions[$categoryId][$permissionId];
			}

			$output[$categoryId] = array(
				'title' => $category['title'],
				'user' => isset($userEntries[$permissionGroupId][$permissionId]) ? $userEntries[$permissionGroupId][$permissionId] : false,
				'content' => isset($categoryWideEntries[$permissionGroupId][$permissionId]) ? $categoryWideEntries[$permissionGroupId][$permissionId] : false,
				'groups' => $groups,
				'final' => $final
			);
		}

		return $output;
	}

	/**
	 * Recursively builds tree permissions for the specified combination.
	 *
	 * @param integer $userId
	 * @param array $userGroupIds
	 * @param array $basePermissions Base permissions, coming from global or parent; [group][permission] => allow/unset/etc
	 * @param array $permissionsGrouped List of all valid permissions, grouped
	 * @param integer $parentId ID of the parent.
	 *
	 * @return array Final permissions (true/false), format: [id][permission] => value
	 */
	protected function _buildTreePermissions(
		$userId, array $userGroupIds, array $basePermissions, array $permissionsGrouped, $parentId = 0
	)
	{
		if (!isset($this->_categoryTree[$parentId]))
		{
			return array();
		}

		if (!isset($basePermissions['resource']['view']))
		{
			if (isset($this->_globalPerms['resource']['view']))
			{
				$basePermissions['resource']['view'] = $this->_globalPerms['resource']['view'];
			}
			else
			{
				$basePermissions['resource']['view'] = 'unset';
			}
		}

		$basePermissions = $this->_adjustBasePermissionAllows($basePermissions);

		$finalPermissions = array();

		foreach ($this->_categoryTree[$parentId] AS $category)
		{
			$categoryId = $category['resource_category_id'];

			$groupEntries = $this->_getUserGroupPermissionEntries($categoryId, $userGroupIds);
			$userEntries = $this->_getUserPermissionEntries($categoryId, $userId);
			$categoryWideEntries = $this->_getCategoryWideEntries($categoryId);

			$categoryPermissions = $this->_permissionModel->buildPermissionCacheForCombination(
				$permissionsGrouped, $categoryWideEntries, $groupEntries, $userEntries,
				$basePermissions, $passPermissions
			);

			if (!isset($categoryPermissions['resource']['view']))
			{
				$categoryPermissions['resource']['view'] = 'unset';
			}

			$finalCategoryPermissions = $this->_permissionModel->canonicalizePermissionCache(
				$categoryPermissions['resource']
			);

			if (isset($finalCategoryPermissions['view']) && !$finalCategoryPermissions['view'])
			{
				// forcable deny viewing perms to children if this isn't viewable
				$passPermissions['resource']['view'] = 'deny';
			}

			$finalPermissions[$categoryId] = $finalCategoryPermissions;

			$finalPermissions += $this->_buildTreePermissions(
				$userId, $userGroupIds, $passPermissions, $permissionsGrouped, $categoryId
			);
		}

		return $finalPermissions;
	}

	/**
	 * Get all user group entries that apply to this category for the specified user groups.
	 *
	 * @param integer $categoryId
	 * @param array $userGroupIds
	 *
	 * @return array
	 */
	protected function _getUserGroupPermissionEntries($categoryId, array $userGroupIds)
	{
		$rawUgEntries = $this->_permissionEntries['userGroups'];
		$groupEntries = array();
		foreach ($userGroupIds AS $userGroupId)
		{
			if (isset($rawUgEntries[$userGroupId], $rawUgEntries[$userGroupId][$categoryId]))
			{
				$groupEntries[$userGroupId] = $rawUgEntries[$userGroupId][$categoryId];
			}
		}

		return $groupEntries;
	}

	/**
	 * Gets all user entries that apply to this category for the specified user ID.
	 *
	 * @param $categoryId
	 * @param $userId
	 *
	 * @return array
	 */
	protected function _getUserPermissionEntries($categoryId, $userId)
	{
		$rawUserEntries = $this->_permissionEntries['users'];
		if ($userId && isset($rawUserEntries[$userId], $rawUserEntries[$userId][$categoryId]))
		{
			return $rawUserEntries[$userId][$categoryId];
		}
		else
		{
			return array();
		}
	}

	/**
	 * Get category-wide permissions for this category.
	 *
	 * @param $categoryId
	 *
	 * @return array
	 */
	protected function _getCategoryWideEntries($categoryId)
	{
		if (isset($this->_permissionEntries['system'][$categoryId]))
		{
			return $this->_permissionEntries['system'][$categoryId];
		}
		else
		{
			return array();
		}
	}

	/**
	 * Adjusts base (inherited) content_allow values to allow only. This
	 * allows them to be revoked.
	 *
	 * @param array $basePermissions
	 *
	 * @return array Adjusted base perms
	 */
	protected function _adjustBasePermissionAllows(array $basePermissions)
	{
		foreach ($basePermissions AS $group => $p)
		{
			foreach ($p AS $id => $value)
			{
				if ($value === 'content_allow')
				{
					$basePermissions[$group][$id] = 'allow';
				}
			}
		}

		return $basePermissions;
	}

	/**
	 * @return XenResource_Model_Category
	 */
	protected function _getCategoryModel()
	{
		return XenForo_Model::create('XenResource_Model_Category');
	}
}