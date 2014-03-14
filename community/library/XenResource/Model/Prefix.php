<?php

class XenResource_Model_Prefix extends XenForo_Model
{
	const FETCH_CATEGORY_PREFIX = 0x01;
	const FETCH_PREFIX_GROUP = 0x02;

	/**
	 * Fetches a single prefix, as defined by its unique prefix ID
	 *
	 * @param integer $prefixId
	 *
	 * @return array
	 */
	public function getPrefixById($prefixId)
	{
		if (!$prefixId)
		{
			return array();
		}

		return $this->_getDb()->fetchRow('
			SELECT *
			FROM xf_resource_prefix
			WHERE prefix_id = ?
		', $prefixId);
	}

	/**
	 * Get prefixes as defined by conditions and fetch options
	 *
	 * @param array $conditions
	 * @param array $fetchOptions
	 *
	 * @return array key: continuous or prefix id, value: array of prefix info
	 */
	public function getPrefixes(array $conditions = array(), array $fetchOptions = array())
	{
		$whereConditions = $this->preparePrefixConditions($conditions, $fetchOptions);

		$orderClause = $this->preparePrefixOrderOptions($fetchOptions, 'prefix.materialized_order');
		$joinOptions = $this->preparePrefixFetchOptions($fetchOptions);
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

		$fetchAll = (!empty($fetchOptions['join']) && ($fetchOptions['join'] & self::FETCH_CATEGORY_PREFIX));

		$query = $this->limitQueryResults('
			SELECT prefix.*
				' . $joinOptions['selectFields'] . '
			FROM xf_resource_prefix AS prefix
				' . $joinOptions['joinTables'] . '
			WHERE ' . $whereConditions . '
			' . $orderClause . '
			', $limitOptions['limit'], $limitOptions['offset']
		);

		return ($fetchAll ? $this->_getDb()->fetchAll($query) : $this->fetchAllKeyed($query, 'prefix_id'));
	}

	/**
	 * Prepares a set of conditions against which to select prefixes.
	 *
	 * @param array $conditions List of conditions.
	 * @param array $fetchOptions The fetch options that have been provided. May be edited if criteria requires.
	 *
	 * @return string Criteria as SQL for where clause
	 */
	public function preparePrefixConditions(array $conditions, array &$fetchOptions)
	{
		$sqlConditions = array();

		$db = $this->_getDb();

		if (isset($conditions['prefix_ids']))
		{
			$sqlConditions[] = 'prefix.prefix_id IN(' . $db->quote($conditions['prefix_ids']) . ')';
		}

		if (isset($conditions['resource_category_id']))
		{
			if (is_array($conditions['resource_category_id']))
			{
				$sqlConditions[] = 'cp.resource_category_id IN(' . $db->quote($conditions['resource_category_id']) . ')';
			}
			else
			{
				$sqlConditions[] = 'cp.resource_category_id = ' . $db->quote($conditions['resource_category_id']);
			}
			$this->addFetchOptionJoin($fetchOptions, self::FETCH_CATEGORY_PREFIX);
		}

		if (!empty($conditions['resource_category_ids']))
		{
			$sqlConditions[] = 'cp.resource_category_id IN(' . $db->quote($conditions['resource_category_ids']) . ')';
			$this->addFetchOptionJoin($fetchOptions, self::FETCH_CATEGORY_PREFIX);
		}

		return $this->getConditionsForClause($sqlConditions);
	}

	/**
	 * Prepares join-related fetch options.
	 *
	 * @param array $fetchOptions
	 *
	 * @return array Containing 'selectFields' and 'joinTables' keys.
	 */
	public function preparePrefixFetchOptions(array $fetchOptions)
	{
		$selectFields = '';
		$joinTables = '';

		if (!empty($fetchOptions['join']))
		{
			if ($fetchOptions['join'] & self::FETCH_CATEGORY_PREFIX)
			{
				$selectFields .= ',
					cp.*';
				$joinTables .= '
					INNER JOIN xf_resource_category_prefix AS cp ON
						(cp.prefix_id = prefix.prefix_id)';
			}

			if ($fetchOptions['join'] & self::FETCH_PREFIX_GROUP)
			{
				$selectFields .= ',
					prefix_group.display_order AS group_display_order';
				$joinTables .= '
					LEFT JOIN xf_resource_prefix_group AS prefix_group ON
						(prefix_group.prefix_group_id = prefix.prefix_group_id)';
			}
		}

		return array(
			'selectFields' => $selectFields,
			'joinTables'   => $joinTables
		);
	}

	/**
	 * Construct 'ORDER BY' clause
	 *
	 * @param array $fetchOptions (uses 'order' key)
	 * @param string $defaultOrderSql Default order SQL
	 *
	 * @return string
	 */
	public function preparePrefixOrderOptions(array &$fetchOptions, $defaultOrderSql = '')
	{
		$choices = array(
			'materialized_order' => 'prefix.materialized_order',
			'canonical_order' => 'prefix_group.display_order, prefix.display_order',
		);

		if (!empty($fetchOptions['order']) && $fetchOptions['order'] == 'canonical_order')
		{
			$this->addFetchOptionJoin($fetchOptions, self::FETCH_PREFIX_GROUP);
		}

		return $this->getOrderByClause($choices, $fetchOptions, $defaultOrderSql);
	}

	/**
	 * Fetches all prefixes, regardless of category or user group associations
	 *
	 * @return array
	 */
	public function getAllPrefixes()
	{
		return $this->getPrefixes();
	}

	/**
	 * Fetches prefixes in prefix groups
	 *
	 * @param array $conditions
	 * @param array $fetchOptions
	 * @param integer $prefixCount Reference: counts the total number of prefixes
	 *
	 * @return [group ID => [title, prefixes => prefix]]
	 */
	public function getPrefixesByGroups(array $conditions = array(), array $fetchOptions = array(), &$prefixCount = 0)
	{
		$prefixes = $this->getPrefixes($conditions, $fetchOptions);

		$prefixGroups = array();
		foreach ($prefixes AS $prefix)
		{
			$prefixGroups[$prefix['prefix_group_id']][$prefix['prefix_id']] = $this->preparePrefix($prefix);
		}

		$prefixCount = count($prefixes);

		return $prefixGroups;
	}

	/**
	 * Fetches all prefixes available in the specified categories
	 *
	 * @param integer|array $categoryIds
	 *
	 * @return array
	 */
	public function getPrefixesInCategories($categoryIds)
	{
		return $this->getPrefixes(is_array($categoryIds)
			? array('resource_category_ids' => $categoryIds)
			: array('resource_category_id' => $categoryIds)
		);
	}

	/**
	 * Fetches all prefixes available in the specified category
	 *
	 * @param integer|array $categoryId
	 *
	 * @return array
	 */
	public function getPrefixesInCategory($categoryId)
	{
		$output = array();
		foreach ($this->getPrefixes(array('resource_category_id' => $categoryId)) AS $prefix)
		{
			$output[$prefix['prefix_id']] = $prefix;
		}

		return $output;
	}

	/**
	 * Fetches all prefixes usable by the visiting user in the specified category/categories
	 *
	 * @param integer|array $categoryIds
	 * @param array|null $viewingUser
	 * @param boolean $verifyUsability
	 *
	 * @return array
	 */
	public function getUsablePrefixesInCategories($categoryIds, array $viewingUser = null, $verifyUsability = true)
	{
		$this->standardizeViewingUserReference($viewingUser);

		$prefixes = $this->getPrefixesInCategories($categoryIds);

		$prefixGroups = array();
		foreach ($prefixes AS $prefix)
		{
			if (!$verifyUsability || $this->_verifyPrefixIsUsableInternal($prefix, $viewingUser))
			{
				$prefixId = $prefix['prefix_id'];
				$prefixGroupId = $prefix['prefix_group_id'];

				if (!isset($prefixGroups[$prefixGroupId]))
				{
					$prefixGroups[$prefixGroupId] = array();

					if ($prefixGroupId)
					{
						$prefixGroups[$prefixGroupId]['title'] = new XenForo_Phrase(
							$this->getPrefixGroupTitlePhraseName($prefixGroupId));
					}

				}

				$prefixGroups[$prefixGroupId]['prefixes'][$prefixId] = $prefix;
			}
		}

		return $prefixGroups;
	}

	public function getPrefixIfInCategory($prefixId, $categoryId)
	{
		return $this->_getDb()->fetchRow('
			SELECT prefix.*
			FROM xf_resource_prefix AS prefix
			INNER JOIN xf_resource_category_prefix AS cp ON (cp.prefix_id = prefix.prefix_id AND cp.resource_category_id = ?)
			WHERE prefix.prefix_id = ?
		', array($categoryId, $prefixId));
	}

	public function getCategoryAssociationsByPrefix($prefixId)
	{
		return $this->_getDb()->fetchCol('
			SELECT resource_category_id
			FROM xf_resource_category_prefix
			WHERE prefix_id = ?
		', $prefixId);
	}

	public function getVisiblePrefixIds(array $viewingUser = null, array $categoryIds = null)
	{
		$this->standardizeViewingUserReference($viewingUser);

		$prefixes = array();

		/** @var $categoryModel XenResource_Model_Category */
		$categoryModel = $this->getModelFromCache('XenResource_Model_Category');

		if ($categoryIds === null)
		{
			$categoryLimit = '';
		}
		else
		{
			if (!$categoryIds)
			{
				return array();
			}

			$categoryLimit = " AND resource_category.resource_category_id IN (" . $this->_getDb()->quote($categoryIds) . ")";
		}

		$results = $this->_getDb()->query("
			SELECT prefix.prefix_id, resource_category.*, cache.cache_value AS category_permission_cache
			FROM xf_resource_prefix AS prefix
			INNER JOIN xf_resource_category_prefix AS cp ON (cp.prefix_id = prefix.prefix_id)
			INNER JOIN xf_resource_category AS resource_category ON (cp.resource_category_id = resource_category.resource_category_id " . $categoryLimit . ")
			INNER JOIN xf_permission_cache_content AS cache ON
				(cache.content_type = 'resource_category' AND cache.content_id = resource_category.resource_category_id AND cache.permission_combination_id = ?)
			ORDER BY prefix.materialized_order
		", $viewingUser['permission_combination_id']);
		while ($result = $results->fetch())
		{
			if (isset($prefixes[$result['prefix_id']]))
			{
				continue;
			}

			$permissions = XenForo_Permission::unserializePermissions($result['category_permission_cache']);
			if ($categoryModel->canViewCategory($result, $null, $viewingUser, $permissions))
			{
				$prefixes[$result['prefix_id']] = $result['prefix_id'];
			}
		}

		return $prefixes;
	}

	public function preparePrefix(array $prefix)
	{
		$prefix['title'] = new XenForo_Phrase($this->getPrefixTitlePhraseName($prefix['prefix_id']));

		return $prefix;
	}

	public function preparePrefixes(array $prefixes)
	{
		foreach ($prefixes AS &$prefix)
		{
			$prefix = $this->preparePrefix($prefix);
		}

		return $prefixes;
	}

	public function getPrefixTitlePhraseName($prefixId)
	{
		return 'resource_prefix_' . $prefixId;
	}

	public function updatePrefixCategoryAssociationByPrefix($prefixId, array $categoryIds)
	{
		$emptyCategoryKey = array_search(0, $categoryIds);
		if ($emptyCategoryKey !== false)
		{
			unset($categoryIds[$emptyCategoryKey]);
		}

		$categoryIds = array_unique($categoryIds);

		$existingCategoryIds = $this->getCategoryAssociationsByPrefix($prefixId);
		if (!$categoryIds && !$existingCategoryIds)
		{
			return; // nothing to do
		}

		$db = $this->_getDb();
		XenForo_Db::beginTransaction($db);

		$db->delete('xf_resource_category_prefix', 'prefix_id = ' . $db->quote($prefixId));

		foreach ($categoryIds AS $categoryId)
		{
			$db->insert('xf_resource_category_prefix', array(
				'resource_category_id' => $categoryId,
				'prefix_id' => $prefixId
			));
		}

		$rebuildCategoryIds = array_unique(array_merge($categoryIds, $existingCategoryIds));
		$this->rebuildPrefixCategoryAssociationCache($rebuildCategoryIds);

		XenForo_Db::commit($db);
	}

	public function updatePrefixCategoryAssociationByCategory($categoryId, array $prefixIds)
	{
		$emptyPrefixKey = array_search(0, $prefixIds);
		if ($emptyPrefixKey !== false)
		{
			unset($prefixIds[$emptyPrefixKey]);
		}

		$prefixIds = array_unique($prefixIds);

		$db = $this->_getDb();

		XenForo_Db::beginTransaction($db);

		$db->delete('xf_resource_category_prefix', 'resource_category_id = ' . $db->quote($categoryId));

		foreach ($prefixIds AS $prefixId)
		{
			$db->insert('xf_resource_category_prefix', array(
				'resource_category_id' => $categoryId,
				'prefix_id' => $prefixId
			));
		}

		$this->rebuildPrefixCategoryAssociationCache($categoryId);

		XenForo_Db::commit($db);
	}

	public function rebuildPrefixCategoryAssociationCache($categoryIds = null)
	{
		if ($categoryIds === null)
		{
			$categoryIds = $this->_getDb()->fetchCol('
				SELECT resource_category_id FROM xf_resource_category
			');
		}
		if (!is_array($categoryIds))
		{
			$categoryIds = array($categoryIds);
		}
		if (!$categoryIds)
		{
			return;
		}

		$db = $this->_getDb();

		$newCache = array();

		foreach ($this->getPrefixesInCategories($categoryIds) AS $prefix)
		{
			$prefixGroupId = $prefix['prefix_group_id'];
			$newCache[$prefix['resource_category_id']][$prefixGroupId][$prefix['prefix_id']] = $prefix['prefix_id'];
		}

		XenForo_Db::beginTransaction($db);

		foreach ($categoryIds AS $categoryId)
		{
			$update = (isset($newCache[$categoryId]) ? serialize($newCache[$categoryId]) : '');

			$db->update('xf_resource_category', array(
				'prefix_cache' => $update
			), 'resource_category_id = ' . $db->quote($categoryId));
		}

		XenForo_Db::commit($db);
	}

	/**
	 * Fetches an array of prefixes including prefix group info, for use in <xen:options source />
	 *
	 * @param array $conditions
	 * @param array $fetchOptions
	 *
	 * @return array
	 */
	public function getPrefixOptions(array $conditions = array(), array $fetchOptions = array())
	{
		$prefixGroups = $this->getPrefixesByGroups($conditions, $fetchOptions);

		$options = array();

		foreach ($prefixGroups AS $prefixGroupId => $prefixes)
		{
			if ($prefixes)
			{
				if ($prefixGroupId)
				{
					$groupTitle = new XenForo_Phrase($this->getPrefixGroupTitlePhraseName($prefixGroupId));
					$groupTitle = (string)$groupTitle;
				}
				else
				{
					$groupTitle = new XenForo_Phrase('ungrouped');
					$groupTitle = '(' . $groupTitle . ')';
				}

				foreach ($prefixes AS $prefixId => $prefix)
				{
					$options[$groupTitle][$prefixId] = array(
						'value' => $prefixId,
						'label' => (string)$prefix['title'],
						'_data' => array('css' => $prefix['css_class'])
					);
				}
			}
		}

		return $options;
	}

	/**
	 * Returns an array with default values for a new prefix
	 *
	 * @return array
	 */
	public function getDefaultPrefixValues()
	{
		return array(
			'prefix_group_id' => 0,
			'display_order' => 1,
			'css_class' => 'prefix prefixPrimary'
		);
	}

	/**
	 * Fetches the data for the prefix cache
	 *
	 * @return array
	 */
	public function getPrefixCache()
	{
		return $this->_getDb()->fetchPairs('
			SELECT prefix_id, css_class
			FROM xf_resource_prefix
			ORDER BY materialized_order
		');
	}

	/**
	 * Rebuilds the 'resourcePrefixes' cache
	 *
	 * @return array
	 */
	public function rebuildPrefixCache()
	{
		$prefixes = $this->getPrefixCache();
		$this->_getDataRegistryModel()->set('resourcePrefixes', $prefixes);
		XenForo_Application::setSimpleCacheData('resourcePrefixes', $prefixes);

		return $prefixes;
	}

	/**
	 * Rebuilds the 'materialized_order' field in the prefix table,
	 * based on the canonical display_order data in the prefix and prefix_group tables.
	 */
	public function rebuildPrefixMaterializedOrder()
	{
		$prefixes = $this->getPrefixes(array(), array('order' => 'canonical_order'));

		$db = $this->_getDb();
		$ungroupedPrefixes = array();
		$updates = array();
		$i = 0;

		foreach ($prefixes AS $prefixId => $prefix)
		{
			if ($prefix['prefix_group_id'])
			{
				if (++$i != $prefix['materialized_order'])
				{
					$updates[$prefixId] = 'WHEN ' . $db->quote($prefixId) . ' THEN ' . $db->quote($i);
				}
			}
			else
			{
				$ungroupedPrefixes[$prefixId] = $prefix;
			}
		}

		foreach ($ungroupedPrefixes AS $prefixId => $prefix)
		{
			if (++$i != $prefix['materialized_order'])
			{
				$updates[$prefixId] = 'WHEN ' . $db->quote($prefixId) . ' THEN ' . $db->quote($i);
			}
		}

		if (!empty($updates))
		{
			$db->query('
				UPDATE xf_resource_prefix SET materialized_order = CASE prefix_id
				' . implode(' ', $updates) . '
				END
				WHERE prefix_id IN(' . $db->quote(array_keys($updates)) . ')
			');
		}
	}

	public function verifyPrefixIsUsable($prefixId, $categoryId, array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);

		if (!$prefixId)
		{
			return true; // not picking one, always ok
		}

		$prefix = $this->getPrefixIfInCategory($prefixId, $categoryId);
		if (!$prefix)
		{
			return false; // bad prefix or bad category
		}

		return $this->_verifyPrefixIsUsableInternal($prefix, $viewingUser);
	}

	protected function _verifyPrefixIsUsableInternal(array $prefix, array $viewingUser)
	{
		$userGroups = explode(',', $prefix['allowed_user_group_ids']);
		if (in_array(-1, $userGroups) || in_array($viewingUser['user_group_id'], $userGroups))
		{
			return true; // available to all groups or the primary group
		}

		if ($viewingUser['secondary_group_ids'])
		{
			foreach (explode(',', $viewingUser['secondary_group_ids']) AS $userGroupId)
			{
				if (in_array($userGroupId, $userGroups))
				{
					return true; // available to one secondary group
				}
			}
		}

		return false; // not available to any groups
	}

	// prefix groups ---------------------------------------------------------

	/**
	 * Fetches a single prefix group, as defined by its unique prefix group ID
	 *
	 * @param integer $prefixGroupId
	 *
	 * @return array
	 */
	public function getPrefixGroupById($prefixGroupId)
	{
		if (!$prefixGroupId)
		{
			return array();
		}

		return $this->_getDb()->fetchRow('
			SELECT *
			FROM xf_resource_prefix_group
			WHERE prefix_group_id = ?
		', $prefixGroupId);
	}

	public function getAllPrefixGroups()
	{
		return $this->fetchAllKeyed('
			SELECT *
			FROM xf_resource_prefix_group
			ORDER BY display_order
		', 'prefix_group_id');
	}

	public function getPrefixGroupOptions($selectedGroupId)
	{
		$prefixGroups = $this->getAllPrefixGroups();
		$prefixGroups = $this->preparePrefixGroups($prefixGroups);

		$options = array();

		foreach ($prefixGroups AS $prefixGroupId => $prefixGroup)
		{
			$options[$prefixGroupId] = $prefixGroup['title'];
		}

		return $options;
	}

	public function mergePrefixesIntoGroups(array $prefixes, array $prefixGroups)
	{
		$merge = array();

		foreach ($prefixGroups AS $prefixGroupId => $prefixGroup)
		{
			if (isset($prefixes[$prefixGroupId]))
			{
				$merge[$prefixGroupId] = $prefixes[$prefixGroupId];
				unset($prefixes[$prefixGroupId]);
			}
			else
			{
				$merge[$prefixGroupId] = array();
			}
		}

		if (!empty($prefixes))
		{
			foreach ($prefixes AS $prefixGroupId => $_prefixes)
			{
				$merge[$prefixGroupId] = $_prefixes;
			}
		}

		return $merge;
	}

	public function getPrefixGroupTitlePhraseName($prefixGroupId)
	{
		return 'resource_prefix_group_' . $prefixGroupId;
	}

	public function preparePrefixGroups(array $prefixGroups)
	{
		return array_map(array($this, 'preparePrefixGroup'), $prefixGroups);
	}

	public function preparePrefixGroup(array $prefixGroup)
	{
		$prefixGroup['title'] = new XenForo_Phrase($this->getPrefixGroupTitlePhraseName($prefixGroup['prefix_group_id']));

		return $prefixGroup;
	}
}