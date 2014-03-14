<?php

class XenResource_Model_Category extends XenForo_Model
{
	public function getCategoryById($categoryId, array $fetchOptions = array())
	{
		$joinOptions = $this->prepareCategoryFetchOptions($fetchOptions);

		return $this->_getDb()->fetchRow('
			SELECT resource_category.*
				' . $joinOptions['selectFields'] . '
			FROM xf_resource_category AS resource_category
			' . $joinOptions['joinTables'] . '
			WHERE resource_category.resource_category_id = ?
		', $categoryId);
	}

	public function getCategoriesByIds(array $categoryIds, array $fetchOptions = array())
	{
		if (!$categoryIds)
		{
			return array();
		}

		$joinOptions = $this->prepareCategoryFetchOptions($fetchOptions);

		return $this->fetchAllKeyed('
			SELECT resource_category.*
				' . $joinOptions['selectFields'] . '
			FROM xf_resource_category AS resource_category
			' . $joinOptions['joinTables'] . '
			WHERE resource_category.resource_category_id IN (' . $this->_getDb()->quote($categoryIds) . ')
		', 'resource_category_id');
	}

	public function getAllCategories(array $fetchOptions = array())
	{
		$joinOptions = $this->prepareCategoryFetchOptions($fetchOptions);

		return $this->fetchAllKeyed('
			SELECT resource_category.*
				' . $joinOptions['selectFields'] . '
			FROM xf_resource_category AS resource_category
			' . $joinOptions['joinTables'] . '
			ORDER BY resource_category.lft
		', 'resource_category_id');
	}

	public function getViewableCategories(array $fetchOptions = array(), array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);

		if (empty($fetchOptions['permissionCombinationId']))
		{
			$fetchOptions['permissionCombinationId'] = $viewingUser['permission_combination_id'];
		}

		$categories = $this->getAllCategories($fetchOptions);
		if (!$categories)
		{
			return array();
		}

		if (!empty($fetchOptions['permissionCombinationId']))
		{
			$this->bulkSetCategoryPermCache(
				$fetchOptions['permissionCombinationId'], $categories, 'category_permission_cache'
			);
		}

		foreach ($categories AS $key => $category)
		{
			if (!$this->canViewCategory($category, $null, $viewingUser))
			{
				unset($categories[$key]);
			}
		}

		return $categories;
	}

	public function getChildrenOfCategoryId($categoryId, array $fetchOptions = array())
	{
		$joinOptions = $this->prepareCategoryFetchOptions($fetchOptions);

		return $this->fetchAllKeyed('
			SELECT resource_category.*
				' . $joinOptions['selectFields'] . '
			FROM xf_resource_category AS resource_category
			' . $joinOptions['joinTables'] . '
			WHERE resource_category.parent_category_id = ?
			ORDER BY resource_category.lft
		', 'resource_category_id', $categoryId);
	}

	public function getDescendantsOfCategory($category, $rgt = null)
	{
		if (is_array($category))
		{
			$lft = $category['lft'];
			$rgt = $category['rgt'];
		}
		else if (!is_null($rgt))
		{
			$lft = intval($category);
		}
		else if (!$category)
		{
			return $this->getAllCategories();
		}
		else
		{
			$category = $this->getCategoryById($category);
			if (!$category)
			{
				return array();
			}

			$lft = $category['lft'];
			$rgt = $category['rgt'];
		}

		if ($rgt == $lft + 1)
		{
			return array();
		}

		return $this->fetchAllKeyed('
			SELECT *
			FROM xf_resource_category
			WHERE lft > ? AND rgt < ?
			ORDER BY lft
		', 'resource_category_id', array($lft, $rgt));
	}

	public function getDescendantsOfCategoryIds(array $categoryIds)
	{
		$categories = $this->getAllCategories();

		$ranges = array();
		foreach ($categoryIds AS $categoryId)
		{
			if (isset($categories[$categoryId]))
			{
				$category = $categories[$categoryId];
				$ranges[] = array($category['lft'], $category['rgt']);
			}
		}

		$descendants = array();
		foreach ($categories AS $category)
		{
			foreach ($ranges AS $range)
			{
				if ($category['lft'] > $range[0] && $category['lft'] < $range[1])
				{
					$descendants[$category['resource_category_id']] = $category;
					break;
				}
			}
		}

		return $descendants;
	}

	public function getPossibleParentCategories(array $category, array $fetchOptions = array())
	{
		$joinOptions = $this->prepareCategoryFetchOptions($fetchOptions);

		return $this->fetchAllKeyed('
			SELECT resource_category.*
				' . $joinOptions['selectFields'] . '
			FROM xf_resource_category AS resource_category
			' . $joinOptions['joinTables'] . '
			WHERE resource_category.lft < ? OR resource_category.rgt > ?
			ORDER BY resource_category.lft
		', 'resource_category_id', array($category['lft'], $category['rgt']));
	}

	/**
	 * Prepares join-related fetch options.
	 *
	 * @param array $fetchOptions
	 *
	 * @return array Containing 'selectFields' and 'joinTables' keys.
	 */
	public function prepareCategoryFetchOptions(array $fetchOptions)
	{
		$selectFields = '';
		$joinTables = '';
		$db = $this->_getDb();

		if (!empty($fetchOptions['permissionCombinationId']))
		{
			$selectFields .= ',
				permission.cache_value AS category_permission_cache';
			$joinTables .= '
				LEFT JOIN xf_permission_cache_content AS permission
					ON (permission.permission_combination_id = ' . $db->quote($fetchOptions['permissionCombinationId']) . '
						AND permission.content_type = \'resource_category\'
						AND permission.content_id = resource_category.resource_category_id)';
		}

		if (isset($fetchOptions['watchUserId']))
		{
			if (!empty($fetchOptions['watchUserId']))
			{
				$selectFields .= ',
					IF(category_watch.user_id IS NULL, 0, 1) AS category_is_watched';
				$joinTables .= '
					LEFT JOIN xf_resource_category_watch AS category_watch
						ON (category_watch.resource_category_id = resource_category.resource_category_id
						AND category_watch.user_id = ' . $this->_getDb()->quote($fetchOptions['watchUserId']) . ')';
			}
			else
			{
				$selectFields .= ',
					0 AS forum_is_watched';
			}
		}

		return array(
			'selectFields' => $selectFields,
			'joinTables'   => $joinTables
		);
	}

	public function groupCategoriesByParent(array $categories)
	{
		$grouped = array();
		foreach ($categories AS $category)
		{
			$grouped[$category['parent_category_id']][$category['resource_category_id']] = $category;
		}

		return $grouped;
	}

	public function ungroupCategories(array $grouped, array $filterIds = null, $parentCategoryId = 0)
	{
		$output = array();

		if (!empty($grouped[$parentCategoryId]))
		{
			foreach ($grouped[$parentCategoryId] AS $category)
			{
				if ($filterIds === null || in_array($category['resource_category_id'], $filterIds))
				{
					$output[$category['resource_category_id']] = $category;
				}

				$output += $this->ungroupCategories($grouped, $filterIds, $category['resource_category_id']);
			}
		}

		return $output;
	}

	public function getDescendantCategoryIdsFromGrouped(array $grouped, $parentCategoryId = 0)
	{
		$parentIds = array($parentCategoryId);
		$output = array();
		do
		{
			$parentId = array_shift($parentIds);
			if (isset($grouped[$parentId]))
			{
				$keys = array_keys($grouped[$parentId]);
				$output = array_merge($output, $keys);
				$parentIds = array_merge($parentIds, $keys);
			}
		}
		while ($parentIds);

		return $output;
	}

	public function getCategoryBreadcrumb(array $category, $includeSelf = true)
	{
		$breadcrumbs = array();

		if (!isset($category['categoryBreadcrumb']))
		{
			$category['categoryBreadcrumb'] = unserialize($category['category_breadcrumb']);
		}

		foreach ($category['categoryBreadcrumb'] AS $catId => $breadcrumb)
		{
			$breadcrumbs[$catId] = array(
				'href' => XenForo_Link::buildPublicLink('full:resources/categories', $breadcrumb),
				'value' => $breadcrumb['category_title']
			);
		}

		if ($includeSelf)
		{
			$breadcrumbs[$category['resource_category_id']] = array(
				'href' => XenForo_Link::buildPublicLink('full:resources/categories', $category),
				'value' => $category['category_title']
			);
		}

		return $breadcrumbs;
	}

	/**
	 * Gets permission-based conditions that apply to resource fetching functions.
	 *
	 * @param array|null $category Category the resources will belong to
	 * @param array|null $viewingUser
	 * @param array|null $categoryPermissions
	 *
	 * @return array Keys: deleted (boolean), moderated (boolean or integer, if can only view single user's)
	 */
	public function getPermissionBasedFetchConditions(array $category = null, array $viewingUser = null, array $categoryPermissions = null)
	{
		if ($category)
		{
			$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);
			$viewAllModerated = XenForo_Permission::hasContentPermission($categoryPermissions, 'viewModerated');
			$viewAllDeleted = XenForo_Permission::hasContentPermission($categoryPermissions, 'viewDeleted');
		}
		else
		{
			$this->standardizeViewingUserReference($viewingUser);
			$viewAllModerated = XenForo_Permission::hasPermission($viewingUser['permissions'], 'resource', 'viewModerated');
			$viewAllDeleted = XenForo_Permission::hasPermission($viewingUser['permissions'], 'resource', 'viewDeleted');
		}

		if ($viewAllModerated)
		{
			$viewModerated = true;
		}
		else if ($viewingUser['user_id'])
		{
			$viewModerated = $viewingUser['user_id'];
		}
		else
		{
			$viewModerated = false;
		}

		$conditions = array(
			'deleted' => $viewAllDeleted,
			'moderated' => $viewModerated
		);

		return $conditions;
	}

	public function prepareCategory(array $category)
	{
		$category['categoryBreadcrumb'] = unserialize($category['category_breadcrumb']);
		$category['last_resource_title'] = XenForo_Helper_String::censorString($category['last_resource_title']);
		$category['allowResource'] = (
			$category['allow_local']
			|| $category['allow_external']
			|| $category['allow_commercial_external']
			|| $category['allow_fileless']
		);
		$category['canAdd'] = $this->canAddResource($category);

		$category['fieldCache'] = @unserialize($category['field_cache']);
		if (!is_array($category['fieldCache']))
		{
			$category['fieldCache'] = array();
		}

		return $category;
	}

	public function prepareCategories(array $categories)
	{
		foreach ($categories AS &$category)
		{
			$category = $this->prepareCategory($category);
		}

		return $categories;
	}

	public function rebuildCategoryStructure()
	{
		$grouped = $this->groupCategoriesByParent($this->fetchAllKeyed('
			SELECT *
			FROM xf_resource_category
			ORDER BY display_order
		', 'resource_category_id'));

		$db = $this->_getDb();
		XenForo_Db::beginTransaction($db);

		$changes = $this->_getStructureChanges($grouped);
		foreach ($changes AS $categoryId => $change)
		{
			$db->update('xf_resource_category', $change, 'resource_category_id = ' . $db->quote($categoryId));
		}

		XenForo_Db::commit($db);

		return $changes;
	}

	protected function _getStructureChanges(array $grouped, $parentId = 0, $depth = 0,
		$startPosition = 1, &$nextPosition = 0, array $breadcrumb = array()
	)
	{
		$nextPosition = $startPosition;

		if (!isset($grouped[$parentId]))
		{
			return array();
		}

		$changes = array();
		$serializedBreadcrumb = serialize($breadcrumb);

		foreach ($grouped[$parentId] AS $categoryId => $category)
		{
			$left = $nextPosition;
			$nextPosition++;

			$thisBreadcrumb = $breadcrumb + array(
				$categoryId => array(
					'resource_category_id' => $categoryId,
					'category_title' => $category['category_title'],
					'parent_category_id' => $category['parent_category_id'],
					'depth' => $category['depth'],
					'lft' => $category['lft'],
					'rgt' => $category['rgt'],
				)
			);

			$changes += $this->_getStructureChanges(
				$grouped, $categoryId, $depth + 1, $nextPosition, $nextPosition, $thisBreadcrumb
			);

			$catChanges = array();
			if ($category['depth'] != $depth)
			{
				$catChanges['depth'] = $depth;
			}
			if ($category['lft'] != $left)
			{
				$catChanges['lft'] = $left;
			}
			if ($category['rgt'] != $nextPosition)
			{
				$catChanges['rgt'] = $nextPosition;
			}
			if ($category['category_breadcrumb'] != $serializedBreadcrumb)
			{
				$catChanges['category_breadcrumb'] = $serializedBreadcrumb;
			}

			if ($catChanges)
			{
				$changes[$categoryId] = $catChanges;
			}

			$nextPosition++;
		}

		return $changes;
	}

	public function applyRecursiveCountsToGrouped(array $grouped, $parentCategoryId = 0)
	{
		if (!isset($grouped[$parentCategoryId]))
		{
			return array();
		}

		$this->_applyRecursiveCountsToGrouped($grouped, $parentCategoryId);
		return $grouped;
	}

	protected function _applyRecursiveCountsToGrouped(array &$grouped, $parentCategoryId)
	{
		$output = array(
			'resource_count' => 0,
			'featured_count' => 0,
			'last_update' => 0
		);

		foreach ($grouped[$parentCategoryId] AS $categoryId => &$category)
		{
			if (isset($grouped[$categoryId]))
			{
				$childCounts = $this->_applyRecursiveCountsToGrouped($grouped, $categoryId);

				$category['resource_count'] += $childCounts['resource_count'];
				$category['featured_count'] += $childCounts['featured_count'];
				if ($childCounts['last_update'] > $category['last_update'])
				{
					$category['last_update'] = $childCounts['last_update'];
					$category['last_resource_title'] = $childCounts['last_resource_title'];
					$category['last_resource_id'] = $childCounts['last_resource_id'];
				}
			}

			$output['resource_count'] += $category['resource_count'];
			$output['featured_count'] += $category['featured_count'];
			if ($category['last_update'] > $output['last_update'])
			{
				$output['last_update'] = $category['last_update'];
				$output['last_resource_title'] = $category['last_resource_title'];
				$output['last_resource_id'] = $category['last_resource_id'];
			}
		}

		return $output;
	}

	/**
	 * Determines if a user can view a given resource category.
	 *
	 * @param array $category
	 * @param string $errorPhraseKey
	 * @param array $viewingUser
	 * @param array|null $categoryPermissions
	 *
	 * @return boolean
	 */
	public function canViewCategory(array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);
		return XenForo_Permission::hasContentPermission($categoryPermissions, 'view');
	}

	/**
	 * Determines if the category can be watched with the given permissions.
	 * This does not check viewing permissions.
	 *
	 * @param array $category
	 * @param string $errorPhraseKey
	 * @param array $viewingUser
	 * @param array|null $categoryPermissions
	 *
	 * @return boolean
	 */
	public function canWatchCategory(array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);
		return ($viewingUser['user_id'] ? true : false);
	}

	/**
	 * Determines if a user can add a resource. Does not check category viewing perms.
	 *
	 * @param array|null $category May be null if no category is specified
	 * @param string $errorPhraseKey
	 * @param array $viewingUser
	 * @param array|null $categoryPermissions
	 *
	 * @return boolean
	 */
	public function canAddResource(array $category = null, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		if ($category)
		{
			$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);
		}
		else
		{
			$this->standardizeViewingUserReference($viewingUser);
		}

		if (!$viewingUser['user_id'])
		{
			return false;
		}

		if ($category)
		{
			if (!isset($category['allowResource']))
			{
				$category['allowResource'] = (
					$category['allow_local']
					|| $category['allow_external']
					|| $category['allow_commercial_external']
					|| $category['allow_fileless']
				);
			}

			if (!$category['allowResource'])
			{
				$errorPhraseKey = 'category_not_allow_new_resources';
				return false;
			}

			if (!XenForo_Permission::hasContentPermission($categoryPermissions, 'add'))
			{
				return false;
			}

			return true;
		}
		else
		{
			return XenForo_Permission::hasPermission($viewingUser['permissions'], 'resource', 'add');
		}
	}

	/**
	 * Caches resource category permissions across all models/the request
	 *
	 * @var array
	 */
	protected static $_categoryPermCache = array();

	public function setCategoryPermCache($combinationId, $categoryId, $cache)
	{
		if ($combinationId === null)
		{
			$combinationId = XenForo_Visitor::getInstance()->permission_combination_id;
		}

		if (is_string($cache))
		{
			$cache = XenForo_Permission::unserializePermissions($cache);
		}

		self::$_categoryPermCache[$combinationId][$categoryId] = $cache;
	}

	public function bulkSetCategoryPermCache($combinationId, array $dataSets, $key = null)
	{
		if ($combinationId === null)
		{
			$combinationId = XenForo_Visitor::getInstance()->permission_combination_id;
		}

		foreach ($dataSets AS $categoryId => $data)
		{
			if ($key !== null && !isset($data[$key]))
			{
				continue;
			}

			$this->setCategoryPermCache($combinationId, $categoryId,
				$key === null ? $data : $data[$key]
			);
		}
	}

	public function resetCategoryPermCache($combinationId = null, $categoryId = null)
	{
		if ($combinationId === null && $categoryId === null)
		{
			self::$_categoryPermCache = array();
		}
		else if ($categoryId === null)
		{
			unset(self::$_categoryPermCache[$combinationId]);
		}
		else
		{
			unset(self::$_categoryPermCache[$combinationId][$categoryId]);
		}
	}

	public function getCategoryPermCache($combinationId, $categoryId)
	{
		if ($combinationId === null)
		{
			$combinationId = XenForo_Visitor::getInstance()->permission_combination_id;
		}

		if (!isset(self::$_categoryPermCache[$combinationId][$categoryId]))
		{
			$permissionCacheModel = XenForo_Model::create('XenForo_Model_PermissionCache');

			self::$_categoryPermCache[$combinationId][$categoryId] = $permissionCacheModel->getContentPermissionsForItem(
				$combinationId, 'resource_category', $categoryId
			);
		}

		return self::$_categoryPermCache[$combinationId][$categoryId];
	}

	/**
	 * Standardizes the viewing user reference for the specific resource category.
	 *
	 * @param integer|array $categoryId
	 * @param array|null $viewingUser Viewing user; if null, use visitor
	 * @param array|null $categoryPermissions Permissions for this category; if null, use visitor's
	 */
	public function standardizeViewingUserReferenceForCategory($categoryId, array &$viewingUser = null, array &$categoryPermissions = null)
	{
		$this->standardizeViewingUserReference($viewingUser);

		if (!is_array($categoryPermissions))
		{
			if (is_array($categoryId))
			{
				$categoryId = $categoryId['resource_category_id'];
			}

			$categoryPermissions = $this->getCategoryPermCache($viewingUser['permission_combination_id'], $categoryId);
		}
	}
}