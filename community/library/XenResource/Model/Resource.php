<?php

class XenResource_Model_Resource extends XenForo_Model
{
	const FETCH_USER        = 0x01;
	const FETCH_USER_OPTION = 0x02;
	const FETCH_DESCRIPTION = 0x04;
	const FETCH_VERSION     = 0x08;
	const FETCH_CATEGORY    = 0x10;
	const FETCH_ATTACHMENT  = 0x20;
	const FETCH_DELETION_LOG = 0x40;
	const FETCH_FEATURED    = 0x80;

	// TODO: let these be tunable or dynamic
	public static $voteThreshold = 10;
	public static $averageVote = 3;

	public static $iconSize = 96;
	public static $iconQuality = 85;

	/**
	 * Gets a single resource record specified by its ID
	 *
	 * @param integer $resourceId
	 *
	 * @return array
	 */
	public function getResourceById($resourceId, array $fetchOptions = array())
	{
		$joinOptions = $this->prepareResourceFetchOptions($fetchOptions);

		return $this->_getDb()->fetchRow('
			SELECT resource.*
				' . $joinOptions['selectFields'] . '
			FROM xf_resource AS resource
				' . $joinOptions['joinTables'] . '
			WHERE resource.resource_id = ?
		', $resourceId);
	}

	/**
	* Gets a single resource record specified by its discussion ID
	*
	* @param integer $resourceId
	*
	* @return array
	*/
	public function getResourceByDiscussionId($discussionId, array $fetchOptions = array())
	{
		$joinOptions = $this->prepareResourceFetchOptions($fetchOptions);

		return $this->_getDb()->fetchRow($this->limitQueryResults('
			SELECT resource.*
				' . $joinOptions['selectFields'] . '
			FROM xf_resource AS resource
				' . $joinOptions['joinTables'] . '
			WHERE resource.discussion_thread_id = ?

		', 1), $discussionId);
	}

	/**
	* Gets resource records specified by their IDs
	*
	* @param integer $resourceIds
	*
	* @return array
	*/
	public function getResourcesByIds(array $resourceIds, array $fetchOptions = array())
	{
		if (!$resourceIds)
		{
			return array();
		}

		$joinOptions = $this->prepareResourceFetchOptions($fetchOptions);

		return $this->fetchAllKeyed('
			SELECT resource.*
				' . $joinOptions['selectFields'] . '
			FROM xf_resource AS resource
				' . $joinOptions['joinTables'] . '
			WHERE resource.resource_id IN (' . $this->_getDb()->quote($resourceIds) . ')
		', 'resource_id');
	}

	/**
	 * Fetch resources based on the conditions and options specified
	 *
	 * @param array $conditions
	 * @param array $fetchOptions
	 *
	 * @return array
	 */
	public function getResources(array $conditions = array(), array $fetchOptions = array())
	{
		$whereClause = $this->prepareResourceConditions($conditions, $fetchOptions);

		$orderClause = $this->prepareResourceOrderOptions($fetchOptions, 'resource.last_update DESC');
		$joinOptions = $this->prepareResourceFetchOptions($fetchOptions);
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

		return $this->fetchAllKeyed($this->limitQueryResults(
			'
				SELECT resource.*
					' . $joinOptions['selectFields'] . '
				FROM xf_resource AS resource
				' . $joinOptions['joinTables'] . '
				WHERE ' . $whereClause . '
				' . $orderClause . '
			', $limitOptions['limit'], $limitOptions['offset']
		), 'resource_id');
	}

	/**
	 * Count the number of resources that meet the given criteria.
	 *
	 * @param array $conditions
	 *
	 * @return integer
	 */
	public function countResources(array $conditions = array())
	{
		$fetchOptions = array();

		$whereClause = $this->prepareResourceConditions($conditions, $fetchOptions);
		$joinOptions = $this->prepareResourceFetchOptions($fetchOptions);

		return $this->_getDb()->fetchOne('
			SELECT COUNT(*)
			FROM xf_resource AS resource
			' . $joinOptions['joinTables'] . '
			WHERE ' . $whereClause
		);
	}

	public function getAggregateResourceData(array $conditions = array())
	{
		$fetchOptions = array();

		$whereClause = $this->prepareResourceConditions($conditions, $fetchOptions);
		$joinOptions = $this->prepareResourceFetchOptions($fetchOptions);

		return $this->_getDb()->fetchRow('
			SELECT COUNT(*) AS total_resources, SUM(resource.rating_sum) AS rating_sum,
				SUM(resource.rating_count) AS rating_count,
				MAX(resource.last_update) AS last_update
			FROM xf_resource AS resource
			' . $joinOptions['joinTables'] . '
			WHERE ' . $whereClause
		);
	}

	public function getMostActiveAuthors($limit, $offset = 0)
	{
		return $this->fetchAllKeyed($this->limitQueryResults(
			'
				SELECT user.*, user_profile.*
				FROM xf_user AS user
				LEFT JOIN xf_user_profile AS user_profile ON
					(user.user_id = user_profile.user_id)
				WHERE user.resource_count > 0
				ORDER BY user.resource_count DESC
			', $limit, $offset
		), 'user_id');
	}

	/**
	 * Gets resource IDs in the specified range. The IDs returned will be those immediately
	 * after the "start" value (not including the start), up to the specified limit.
	 *
	 * @param integer $start IDs greater than this will be returned
	 * @param integer $limit Number of posts to return
	 *
	 * @return array List of IDs
	 */
	public function getResourceIdsInRange($start, $limit)
	{
		$db = $this->_getDb();

		return $db->fetchCol($db->limit('
				SELECT resource_id
				FROM xf_resource
				WHERE resource_id > ?
				ORDER BY resource_id
			', $limit), $start);
	}

	/**
	 * Prepares a set of conditions against which to select resources.
	 *
	 * @param array $conditions List of conditions.
	 * @param array $fetchOptions The fetch options that have been provided. May be edited if criteria requires.
	 *
	 * @return string Criteria as SQL for where clause
	 */
	public function prepareResourceConditions(array $conditions, array &$fetchOptions)
	{
		$db = $this->_getDb();
		$sqlConditions = array();

		if (!empty($conditions['user_id']))
		{
			if (is_array($conditions['user_id']))
			{
				$sqlConditions[] = 'resource.user_id IN (' . $db->quote($conditions['user_id']) . ')';
			}
			else
			{
				$sqlConditions[] = 'resource.user_id = ' . $db->quote($conditions['user_id']);
			}
		}

		if (!empty($conditions['resource_category_id']))
		{
			if (is_array($conditions['resource_category_id']))
			{
				$sqlConditions[] = 'resource.resource_category_id IN (' . $db->quote($conditions['resource_category_id']) . ')';
			}
			else
			{
				$sqlConditions[] = 'resource.resource_category_id = ' . $db->quote($conditions['resource_category_id']);
			}
		}

		if (!empty($conditions['prefix_id']))
		{
			if (is_array($conditions['prefix_id']))
			{
				if (in_array(-1, $conditions['prefix_id'])) {
					$conditions['prefix_id'][] = 0;
				}
				$sqlConditions[] = 'resource.prefix_id IN (' . $db->quote($conditions['prefix_id']) . ')';
			}
			else if ($conditions['prefix_id'] == -1)
			{
				$sqlConditions[] = 'resource.prefix_id = 0';
			}
			else
			{
				$sqlConditions[] = 'resource.prefix_id = ' . $db->quote($conditions['prefix_id']);
			}
		}

		if (!empty($conditions['resource_id_not']))
		{
			$sqlConditions[] = 'resource.resource_id <> ' . $db->quote($conditions['resource_id_not']);
		}

		if (!empty($conditions['price']) && is_array($conditions['price']))
		{
			list($operator, $cutOff) = $conditions['price'];
			$cutOff = floatval($cutOff);

			$this->assertValidCutOffOperator($operator);
			$sqlConditions[] = "resource.price $operator " . $db->quote($cutOff);
		}

		if (isset($conditions['deleted']) || isset($conditions['moderated']))
		{
			$sqlConditions[] = $this->prepareStateLimitFromConditions($conditions, 'resource', 'resource_state');
		}
		else
		{
			// sanity check: only get visible resources unless we've explicitly said to get something else
			$sqlConditions[] = "resource.resource_state = 'visible'";
		}

		return $this->getConditionsForClause($sqlConditions);
	}

	/**
	 * Construct 'ORDER BY' clause
	 *
	 * @param array $fetchOptions (uses 'order' key)
	 * @param string $defaultOrderSql Default order SQL
	 *
	 * @return string
	 */
	public function prepareResourceOrderOptions(array &$fetchOptions, $defaultOrderSql = '')
	{
		$choices = array(
			'last_update' => 'resource.last_update',
			'resource_date' => 'resource.resource_date',
			'download_count' => 'resource.download_count %s, resource.last_update DESC',
			'rating_avg' => 'resource.rating_avg %s, resource.last_update DESC',
			'rating_weighted' => 'resource.rating_weighted %s, resource.last_update DESC',
			'username' => 'resource.username %s, resource.last_update DESC',
			'title' => 'resource.title',
		);
		return $this->getOrderByClause($choices, $fetchOptions, $defaultOrderSql);
	}

	/**
	 * Prepares join-related fetch options.
	 *
	 * @param array $fetchOptions
	 *
	 * @return array Containing 'selectFields' and 'joinTables' keys.
	 */
	public function prepareResourceFetchOptions(array $fetchOptions)
	{
		$selectFields = '';
		$joinTables = '';
		$db = $this->_getDb();

		if (!empty($fetchOptions['join']))
		{
			if ($fetchOptions['join'] & self::FETCH_CATEGORY)
			{
				$selectFields .= ',
					category.*, category.last_update AS category_last_update, resource.last_update';
				$joinTables .= '
					LEFT JOIN xf_resource_category AS category ON
						(category.resource_category_id = resource.resource_category_id)';
			}

			if ($fetchOptions['join'] & self::FETCH_DESCRIPTION)
			{
				$selectFields .= ',
					resource_update.message AS description';
				$joinTables .= '
					LEFT JOIN xf_resource_update AS resource_update ON
						(resource_update.resource_update_id = resource.description_update_id)';
			}

			if ($fetchOptions['join'] & self::FETCH_VERSION)
			{
				$selectFields .= ',
					version.version_string,
					version.release_date,
					version.download_url,
					version.rating_count AS version_rating_count,
					version.rating_sum AS version_rating_sum,
					version.download_count AS version_download_count';
				$joinTables .= '
					LEFT JOIN xf_resource_version AS version ON
						(version.resource_version_id = resource.current_version_id)';
			}

			if ($fetchOptions['join'] & self::FETCH_USER)
			{
				$selectFields .= ',
					user.*, user_profile.*, IF(user.username IS NULL, resource.username, user.username) AS username';
				$joinTables .= '
					LEFT JOIN xf_user AS user ON
						(user.user_id = resource.user_id)
					LEFT JOIN xf_user_profile AS user_profile ON
						(user_profile.user_id = resource.user_id)';
			}

			if ($fetchOptions['join'] & self::FETCH_USER_OPTION)
			{
				$selectFields .= ',
					user_option.*';
				$joinTables .= '
					LEFT JOIN xf_user_option AS user_option ON
						(user_option.user_id = resource.user_id)';
			}

			if ($fetchOptions['join'] & self::FETCH_ATTACHMENT)
			{
				$selectFields .= ',
					attachment.attachment_id,
					attachment.view_count AS attachment_view_count,
					attachment_data.filename AS attachment_filename,
					attachment_data.file_size AS attachment_file_size';
				$joinTables .= '
					LEFT JOIN xf_attachment AS attachment ON
						(attachment.content_type = \'resource_version\' AND attachment.content_id = version.resource_version_id)
					LEFT JOIN xf_attachment_data AS attachment_data ON
						(attachment_data.data_id = attachment.data_id)';
			}

			if ($fetchOptions['join'] & self::FETCH_DELETION_LOG)
			{
				$selectFields .= ',
					deletion_log.delete_date, deletion_log.delete_reason,
					deletion_log.delete_user_id, deletion_log.delete_username';
				$joinTables .= '
					LEFT JOIN xf_deletion_log AS deletion_log ON
						(deletion_log.content_type = \'resource\' AND deletion_log.content_id = resource.resource_id)';
			}

			if ($fetchOptions['join'] & self::FETCH_FEATURED)
			{
				$selectFields .= ',
					feature.feature_date';
				$joinTables .= '
					LEFT JOIN xf_resource_feature AS feature ON
						(feature.resource_id = resource.resource_id)';
			}
		}

		if (!empty($fetchOptions['permissionCombinationId']))
		{
			$selectFields .= ',
				permission.cache_value AS category_permission_cache';
			$joinTables .= '
				LEFT JOIN xf_permission_cache_content AS permission
					ON (permission.permission_combination_id = ' . $db->quote($fetchOptions['permissionCombinationId']) . '
						AND permission.content_type = \'resource_category\'
						AND permission.content_id = resource.resource_category_id)';
		}

		if (isset($fetchOptions['watchUserId']))
		{
			if (!empty($fetchOptions['watchUserId']))
			{
				$selectFields .= ',
					IF(resource_watch.user_id IS NULL, 0,
						IF(resource_watch.email_subscribe, \'watch_email\', \'watch_no_email\')) AS is_watched';
				$joinTables .= '
					LEFT JOIN xf_resource_watch AS resource_watch
						ON (resource_watch.resource_id = resource.resource_id
						AND resource_watch.user_id = ' . $this->_getDb()->quote($fetchOptions['watchUserId']) . ')';
			}
			else
			{
				$selectFields .= ',
					0 AS is_watched';
			}
		}

		if (isset($fetchOptions['downloadUserId']))
		{
			if (!empty($fetchOptions['downloadUserId']))
			{
				$selectFields .= ',
					IF(resource_download.last_download_date IS NULL, 0, resource_download.last_download_date) AS last_download_date';
				$joinTables .= '
					LEFT JOIN xf_resource_download AS resource_download
						ON (resource_download.resource_version_id = resource.current_version_id
						AND resource_download.user_id = ' . $this->_getDb()->quote($fetchOptions['downloadUserId']) . ')';
			}
			else
			{
				$selectFields .= ',
					0 AS last_download_date';
			}
		}

		return array(
			'selectFields' => $selectFields,
			'joinTables'   => $joinTables
		);
	}

	public function prepareResource(array $resource, array $category = null, array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);

		if (isset($resource['resource_title']))
		{
			// likely a update with resource info
			$resource['resource_title'] = XenForo_Helper_String::censorString($resource['resource_title']);
		}
		else
		{
			$resource['title'] = XenForo_Helper_String::censorString($resource['title']);
		}
		$resource['tag_line'] = XenForo_Helper_String::censorString($resource['tag_line']);
		if (isset($resource['version_string']))
		{
			$resource['version_string'] = XenForo_Helper_String::censorString($resource['version_string']);
		}
		$resource['isCensored'] = true;

		$resource['isFilelessNoExternal'] = ($resource['is_fileless'] && !$resource['external_purchase_url']);

		if ($resource['external_url'])
		{
			$externalUrl = XenForo_Helper_String::censorString($resource['external_url']);
			if ($externalUrl != $resource['external_url'])
			{
				// URL was censored - wouldn't work anyway
				$resource['external_url'] = '';
			}
			else
			{
				$resource['external_url_components'] = @parse_url($resource['external_url']);
			}
		}

		$resource['price'] = floatval($resource['price']);

		if ($resource['currency'])
		{
			$resource['cost'] = XenForo_Locale::numberFormat($resource['price'], 2) . ' ' . utf8_strtoupper($resource['currency']);
		}
		else
		{
			$resource['cost'] = '';
		}

		$resource['rating'] = $this->getRatingAverage($resource['rating_sum'], $resource['rating_count']);

		if (isset($resource['version_rating_count'], $resource['version_rating_sum']))
		{
			$resource['version_rating'] = $this->getRatingAverage($resource['version_rating_sum'], $resource['version_rating_count']);
		}
		else
		{
			$resource['version_rating'] = 0;
		}

		if ($category)
		{
			$resource['canDownload'] = $this->canDownloadResource($resource, $category, $null, $viewingUser);
			$resource['canEdit'] = $this->canEditResource($resource, $category, $null, $viewingUser);
			$resource['canEditIcon'] = $this->canEditResourceIcon($resource, $category, $null, $viewingUser);
			$resource['canReassign'] = $this->canReassignResource($resource, $category, $null, $viewingUser);
			$resource['canDelete'] = $this->canDeleteResource($resource, $category, 'soft', $null, $viewingUser);
			$resource['canUndelete'] = $this->canUndeleteResource($resource, $category, $null, $viewingUser);
			$resource['canFeatureUnfeature'] = $this->canFeatureUnfeatureResource($resource, $category, $null, $viewingUser);
			$resource['canApprove'] = $this->canApproveResource($resource, $category, $null, $viewingUser);
			$resource['canUnapprove'] = $this->canUnapproveResource($resource, $category, $null, $viewingUser);
			$resource['canRate'] = $this->canRateResource($resource, $category, $null, $viewingUser);
			$resource['canRateIfDownloaded'] = $this->canRateResourceIfDownloaded($resource, $category, $null, $viewingUser);
			$resource['canWatch'] = $this->canWatchResource($resource, $category, $null, $viewingUser);
			$resource['canAddUpdate'] = $this->_getUpdateModel()->canAddUpdate($resource, $category, $null, $viewingUser);
			$resource['canAddVersion'] = $this->_getVersionModel()->canAddVersion($resource, $category, $null, $viewingUser);
			$resource['canViewPreview'] = $this->canViewPreview($resource, $category, $null, $viewingUser);

			if (!isset($resource['canInlineMod']))
			{
				$this->addInlineModOptionToResource($resource, $category, $viewingUser);
			}
		}
		else
		{
			$resource['canDownload'] = false;
			$resource['canEdit'] = false;
			$resource['canEditIcon'] = false;
			$resource['canReassign'] = false;
			$resource['canDelete'] = false;
			$resource['canUndelete'] = false;
			$resource['canFeatureUnfeature'] = false;
			$resource['canApprove'] = false;
			$resource['canUnapprove'] = false;
			$resource['canRate'] = false;
			$resource['canRateIfDownloaded'] = false;
			$resource['canWatch'] = false;
			$resource['canAddUpdate'] = false;
			$resource['canAddVersion'] = false;
			$resource['canViewPreview'] = false;
			$resource['canInlineMod'] = false;
		}

		if (!empty($resource['attachment_id']))
		{
			$resource['attachment'] = $this->_getAttachmentModel()->prepareAttachment(array(
				'attachment_id' => $resource['attachment_id'],
				'filename' => $resource['attachment_filename'],
				'file_size' => $resource['attachment_file_size'],
				'view_count' => $resource['attachment_view_count'],
				'thumbnail_width' => 0,
			));
		}

		if (!empty($resource['user_group_id']))
		{
			$userModel = $this->getModelFromCache('XenForo_Model_User');
			$resource = $userModel->prepareUser($resource);
			$resource['canCleanSpam'] = (
				XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'cleanSpam')
				&& $userModel->couldBeSpammer($resource)
			);
		}

		return $resource;
	}

	public function prepareResourceCustomFields(array $resource, array $category, array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);

		$resource['customFields'] = @unserialize($resource['custom_resource_fields']);
		if (!is_array($resource['customFields']))
		{
			$resource['customFields'] = array();
		}

		$resource['showExtraInfoTab'] = false;

		if (!isset($category['fieldCache']))
		{
			$category['fieldCache'] = @unserialize($category['field_cache']);
			if (!is_array($category['fieldCache']))
			{
				$category['fieldCache'] = array();
			}
		}
		if (!empty($category['fieldCache']['extra_tab']))
		{
			foreach ($category['fieldCache']['extra_tab'] AS $fieldId)
			{
				if (isset($resource['customFields'][$fieldId]) && $resource['customFields'][$fieldId] !== '')
				{
					$resource['showExtraInfoTab'] = true;
					break;
				}
			}
		}

		$resource['customFieldTabs'] = array();
		if (!empty($category['fieldCache']['new_tab']))
		{
			foreach ($category['fieldCache']['new_tab'] AS $fieldId)
			{
				if (isset($resource['customFields'][$fieldId])
					&& (
						(is_string($resource['customFields'][$fieldId]) && $resource['customFields'][$fieldId] !== '')
						|| (is_array($resource['customFields'][$fieldId]) && count($resource['customFields'][$fieldId]))
					)
				) {
					$resource['customFieldTabs'][] = $fieldId;
				}
			}
		}

		return $resource;
	}

	public function addInlineModOptionToResource(array &$resource, array $category, array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

		$modOptions = array();
		$canInlineMod = ($viewingUser['user_id'] && (
			XenForo_Permission::hasContentPermission($categoryPermissions, 'deleteAny')
			|| XenForo_Permission::hasContentPermission($categoryPermissions, 'undelete')
			|| XenForo_Permission::hasContentPermission($categoryPermissions, 'approveUnapprove')
			|| XenForo_Permission::hasContentPermission($categoryPermissions, 'reassign')
			|| XenForo_Permission::hasContentPermission($categoryPermissions, 'editAny')
			|| XenForo_Permission::hasContentPermission($categoryPermissions, 'featureUnfeature')
		));

		if ($canInlineMod)
		{
			$null = null;
			if ($this->canDeleteResource($resource, $category, 'soft', $null, $viewingUser, $categoryPermissions))
			{
				$modOptions['delete'] = true;
			}
			if ($this->canUndeleteResource($resource, $category, $null, $viewingUser, $categoryPermissions))
			{
				$modOptions['undelete'] = true;
			}
			if ($this->canApproveResource($resource, $category, $null, $viewingUser, $categoryPermissions))
			{
				$modOptions['approve'] = true;
			}
			if ($this->canUnapproveResource($resource, $category, $null, $viewingUser, $categoryPermissions))
			{
				$modOptions['unapprove'] = true;
			}
			if ($this->canReassignResource($resource, $category, $null, $viewingUser, $categoryPermissions))
			{
				$modOptions['reassign'] = true;
			}
			if ($this->canFeatureUnfeatureResource($resource, $category, $null, $viewingUser, $categoryPermissions))
			{
				$modOptions['feature'] = true;
				$modOptions['unfeature'] = true;
			}
			if (XenForo_Permission::hasContentPermission($categoryPermissions, 'editAny'))
			{
				$modOptions['move'] = true;
			}
		}

		$resource['canInlineMod'] = (count($modOptions) > 0);

		return $modOptions;
	}

	public function getInlineModOptionsForResources(array $resources, array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);

		$inlineModOptions = array();

		foreach ($resources AS $resource)
		{
			$resourceModOptions = $this->addInlineModOptionToResource($resource, $resource, $viewingUser);
			$inlineModOptions += $resourceModOptions;
		}

		return $inlineModOptions;
	}

	public function prepareResources(array $resources, array $category = null, array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);

		foreach ($resources AS &$resource)
		{
			if ($category === null && isset($resource['category_title']))
			{
				$resource = $this->prepareResource($resource, $resource, $viewingUser);
			}
			else
			{
				$resource = $this->prepareResource($resource, $category, $viewingUser);
			}
		}

		return $resources;
	}

	public function filterUnviewableResources(array $resources, array $category = null, array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);

		foreach ($resources AS $key => $resource)
		{
			$cat = ($category ? $category : $resource);

			if (isset($cat['category_permission_cache']))
			{
				$categoryPermissions = XenForo_Permission::unserializePermissions($cat['category_permission_cache']);
			}
			else
			{
				$categoryPermissions = null;
			}

			if (!$this->canViewResourceAndContainer($resource, $cat, $null, $viewingUser, $categoryPermissions))
			{
				unset($resources[$key]);
			}
		}

		return $resources;
	}

	/**
	 * Gets the average rating based on the sum and count stored.
	 *
	 * @param integer $sum
	 * @param integer $count
	 * @param boolean $round If true, return rating to the nearest 0.5, otherwise full float.
	 *
	 * @return float
	 */
	public function getRatingAverage($sum, $count, $round = false)
	{
		if ($count == 0)
		{
			return 0;
		}

		$average = $sum / $count;

		if ($round)
		{
			$average = round($average / 0.5, 0) * 0.5;
		}

		return $average;
	}

	public function getWeightedRating($count, $sum)
	{
		return (self::$voteThreshold * self::$averageVote + $sum) / (self::$voteThreshold + $count);
	}

	public function canViewResources(&$errorPhraseKey = '', array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);
		return XenForo_Permission::hasPermission($viewingUser['permissions'], 'resource', 'view');
	}

	/**
	 * Determines if a user can view a given resource. Does not check parent (category) permissions.
	 *
	 * @param array $resource
	 * @param array $category
	 * @param string $errorPhraseKey
	 * @param array $viewingUser
	 * @param array|null $categoryPermissions
	 *
	 * @return boolean
	 */
	public function canViewResource(array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

		if (!XenForo_Permission::hasContentPermission($categoryPermissions, 'view'))
		{
			return false;
		}

		if ($resource['resource_state'] == 'moderated')
		{
			if (!XenForo_Permission::hasContentPermission($categoryPermissions, 'viewModerated'))
			{
				if (!$viewingUser['user_id'] || $viewingUser['user_id'] != $resource['user_id'])
				{
					return false;
				}
			}
		}
		else if ($resource['resource_state'] == 'deleted')
		{
			if (!XenForo_Permission::hasContentPermission($categoryPermissions, 'viewDeleted'))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Determines if a user can view a given resource, as well as category perms.
	 *
	 * @param array $resource
	 * @param array $category
	 * @param string $errorPhraseKey
	 * @param array $viewingUser
	 * @param array|null $categoryPermissions
	 *
	 * @return boolean
	 */
	public function canViewResourceAndContainer(array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		if (!$this->_getCategoryModel()->canViewCategory($category, $errorPhraseKey, $viewingUser, $categoryPermissions))
		{
			return false;
		}

		return $this->canViewResource($resource, $category, $errorPhraseKey, $viewingUser, $categoryPermissions);
	}

	/**
	 * Determines if a user can download a given resource. Does not check viewing perms.
	 *
	 * @param array $resource
	 * @param array $category
	 * @param string $errorPhraseKey
	 * @param array $viewingUser
	 * @param array|null $categoryPermissions
	 *
	 * @return boolean
	 */
	public function canDownloadResource(array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

		if ($viewingUser['user_id'] == $resource['user_id'])
		{
			return true;
		}

		return XenForo_Permission::hasContentPermission($categoryPermissions, 'download');
	}

	/**
	 * Determines if a user can edit a given resource. Does not check viewing perms.
	 *
	 * @param array $resource
	 * @param array $category
	 * @param string $errorPhraseKey
	 * @param array $viewingUser
	 * @param array|null $categoryPermissions
	 *
	 * @return boolean
	 */
	public function canEditResource(array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

		if (!$viewingUser['user_id'])
		{
			return false;
		}

		if (XenForo_Permission::hasContentPermission($categoryPermissions, 'editAny'))
		{
			return true;
		}

		return (
			$resource['user_id'] == $viewingUser['user_id']
			&& XenForo_Permission::hasContentPermission($categoryPermissions, 'updateSelf')
		);
	}

	/**
	 * Determines if a user can edit a given resource icon. Does not check viewing perms.
	 *
	 * @param array $resource
	 * @param array $category
	 * @param string $errorPhraseKey
	 * @param array $viewingUser
	 * @param array|null $categoryPermissions
	 *
	 * @return boolean
	 */
	public function canEditResourceIcon(array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

		if (!XenForo_Application::getOptions()->resourceAllowIcons)
		{
			return false;
		}

		if (!$viewingUser['user_id'])
		{
			return false;
		}

		if (XenForo_Permission::hasContentPermission($categoryPermissions, 'editAny'))
		{
			return true;
		}

		return (
			$resource['user_id'] == $viewingUser['user_id']
			&& XenForo_Permission::hasContentPermission($categoryPermissions, 'updateSelf')
		);
	}

	/**
	 * Determines if a user can reassign a given resource. Does not check viewing perms.
	 *
	 * @param array $resource
	 * @param array $category
	 * @param string $errorPhraseKey
	 * @param array $viewingUser
	 * @param array|null $categoryPermissions
	 *
	 * @return boolean
	 */
	public function canReassignResource(array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

		if (!$viewingUser['user_id'])
		{
			return false;
		}

		return XenForo_Permission::hasContentPermission($categoryPermissions, 'reassign');
	}

	/**
	 * Determines if a user can delete a given resource. Does not check viewing perms.
	 *
	 * @param array $resource
	 * @param array $category
	 * @param string $type
	 * @param string $errorPhraseKey
	 * @param array $viewingUser
	 * @param array|null $categoryPermissions
	 *
	 * @return boolean
	 */
	public function canDeleteResource(array $resource, array $category, $type = 'soft', &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

		if (!$viewingUser['user_id'])
		{
			return false;
		}

		if ($type == 'hard')
		{
			return XenForo_Permission::hasContentPermission($categoryPermissions, 'hardDeleteAny');
		}

		if ($resource['user_id'] == $viewingUser['user_id'])
		{
			return XenForo_Permission::hasContentPermission($categoryPermissions, 'deleteSelf');
		}

		return XenForo_Permission::hasContentPermission($categoryPermissions, 'deleteAny');
	}

	/**
	 * Determines if a user can undelete a given resource. Does not check viewing perms.
	 *
	 * @param array $resource
	 * @param array $category
	 * @param string $errorPhraseKey
	 * @param array $viewingUser
	 * @param array|null $categoryPermissions
	 *
	 * @return boolean
	 */
	public function canUndeleteResource(array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

		return ($viewingUser['user_id']
			&& $resource['resource_state'] == 'deleted'
			&& XenForo_Permission::hasContentPermission($categoryPermissions, 'undelete')
		);
	}

	/**
	 * Determines if a user can approve a given resource. Does not check viewing perms.
	 *
	 * @param array $resource
	 * @param array $category
	 * @param string $errorPhraseKey
	 * @param array $viewingUser
	 * @param array|null $categoryPermissions
	 *
	 * @return boolean
	 */
	public function canApproveResource(array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

		return ($viewingUser['user_id']
			&& $resource['resource_state'] == 'moderated'
			&& XenForo_Permission::hasContentPermission($categoryPermissions, 'approveUnapprove')
		);
	}

	/**
	 * Determines if a user can approve a given resource. Does not check viewing perms.
	 *
	 * @param array $resource
	 * @param array $category
	 * @param string $errorPhraseKey
	 * @param array $viewingUser
	 * @param array|null $categoryPermissions
	 *
	 * @return boolean
	 */
	public function canUnapproveResource(array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

		return ($viewingUser['user_id']
			&& $resource['resource_state'] == 'visible'
			&& XenForo_Permission::hasContentPermission($categoryPermissions, 'approveUnapprove')
		);
	}

	/**
	 * Determines if a user can feature/unfeature a given resource. Does not check viewing perms.
	 *
	 * @param array $resource
	 * @param array $category
	 * @param string $errorPhraseKey
	 * @param array $viewingUser
	 * @param array|null $categoryPermissions
	 *
	 * @return boolean
	 */
	public function canFeatureUnfeatureResource(array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

		return ($viewingUser['user_id']
			&& XenForo_Permission::hasContentPermission($categoryPermissions, 'featureUnfeature')
		);
	}

	/**
	 * Determines if a user can rate a resource
	 *
	 * @param array $resource
	 * @param array $category
	 * @param string $errorPhraseKey
	 * @param array $viewingUser
	 * @param array|null $categoryPermissions
	 *
	 * @return boolean
	 */
	public function canRateResource(array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

		if ($resource['resource_state'] != 'visible')
		{
			return false;
		}

		if (!$this->canRateResourceIfDownloaded($resource, $category, $errorPhraseKey, $viewingUser))
		{
			return false;
		}

		if (!$resource['is_fileless'] && XenForo_Application::getOptions()->requireDownloadToRate && empty($resource['last_download_date']))
		{
			$errorPhraseKey = 'you_only_rate_resource_version_downloaded';
			return false;
		}

		return true;
	}

	/**
	* Determines if a user can rate a resource assuming they downloaded it
	*
	* @param array $resource
	* @param array $category
	* @param string $errorPhraseKey
	* @param array $viewingUser
	* @param array|null $categoryPermissions
	*
	* @return boolean
	*/
	public function canRateResourceIfDownloaded(array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

		if ($resource['resource_state'] != 'visible')
		{
			return false;
		}

		if (!$viewingUser['user_id'])
		{
			return false;
		}

		if ($viewingUser['user_id'] == $resource['user_id'])
		{
			return false;
		}

		if (!$this->canDownloadResource($resource, $category, $null, $viewingUser, $categoryPermissions))
		{
			return false;
		}

		return XenForo_Permission::hasContentPermission($categoryPermissions, 'rate');
	}

	/**
	* Determines if the resource can be watched with the given permissions.
	* This does not check viewing permissions.
	*
	* @param array $resource
	* @param array $category
	* @param string $errorPhraseKey
	* @param array|null $viewingUser
	* @param array|null $categoryPermissions
	*
	* @return boolean
	*/
	public function canWatchResource(array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

		if (!$viewingUser['user_id'])
		{
			return false;
		}

		// currently no point letting the creator view the resource as only they can update
		return ($viewingUser['user_id'] != $resource['user_id']);
	}

	/**
	* Determines if the resource preview can be viewed with the given permissions.
	*
	* @param array $resource
	* @param array $category
	* @param string $errorPhraseKey
	* @param array|null $viewingUser
	* @param array|null $categoryPermissions
	*
	* @return boolean
	*/
	public function canViewPreview(array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

		return (XenForo_Application::get('options')->discussionPreviewLength > 0);
	}

	public function getAvailableCurrencies()
	{
		$currencies = preg_split('/\s/', XenForo_Application::getOptions()->resourceCurrencies, -1, PREG_SPLIT_NO_EMPTY);
		$output = array();
		foreach ($currencies AS $currency)
		{
			$output[utf8_substr($currency, 0, 3)] = utf8_strtoupper($currency);
		}

		return $output;
	}

	/**
	 * Get the file path to a resource icon.
	 *
	 * @param integer $resourceId
	 * @param string $externalDataPath External data directory path (optional)
	 *
	 * @return string
	 */
	public function getResourceIconFilePath($resourceId, $externalDataPath = null)
	{
		if ($externalDataPath === null)
		{
			$externalDataPath = XenForo_Helper_File::getExternalDataPath();
		}

		return sprintf('%s/resource_icons/%d/%d.jpg',
			$externalDataPath,
			floor($resourceId / 1000),
			$resourceId
		);
	}

	/**
	 * Sets a resource icon from an uploaded file
	 *
	 * @param XenForo_Upload $upload
	 * @param int $resourceId
	 *
	 * @return bool
	 *
	 * @throws XenForo_Exception
	 */
	public function uploadResourceIcon(XenForo_Upload $upload, $resourceId)
	{
		if (!$resourceId)
		{
			throw new XenForo_Exception('Missing user ID.');
		}

		if (!$upload->isValid())
		{
			throw new XenForo_Exception($upload->getErrors(), true);
		}

		if (!$upload->isImage())
		{
			throw new XenForo_Exception(new XenForo_Phrase('uploaded_file_is_not_valid_image'), true);
		};

		$baseTempFile = $upload->getTempFile();

		$imageType = $upload->getImageInfoField('type');
		$width = $upload->getImageInfoField('width');
		$height = $upload->getImageInfoField('height');

		return $this->applyResourceIcon($resourceId, $baseTempFile, $imageType, $width, $height);
	}

	/**
	 * Sets a resource icon from a known image file
	 *
	 * @param int $resourceId
	 * @param string $fileName
	 * @param int|bool $imageType
	 * @param int|bool $width
	 * @param int|bool $height
	 *
	 * @return bool
	 *
	 * @throws XenForo_Exception
	 */
	public function applyResourceIcon($resourceId, $fileName, $imageType = false, $width = false, $height = false)
	{
		if (!$imageType || !$width || !$height)
		{
			$imageInfo = getimagesize($fileName);
			if (!$imageInfo)
			{
				throw new XenForo_Exception('Non-image passed in to applyAvatar');
			}
			$width = $imageInfo[0];
			$height = $imageInfo[1];
			$imageType = $imageInfo[2];
		}

		if (!in_array($imageType, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)))
		{
			throw new XenForo_Exception(new XenForo_Phrase('uploaded_file_is_not_valid_image'), true);
		}

		if (!XenForo_Image_Abstract::canResize($width, $height))
		{
			throw new XenForo_Exception(new XenForo_Phrase('uploaded_image_is_too_big'), true);
		}

		$maxDimensions = self::$iconSize;
		$imageQuality = self::$iconQuality;
		$outputType = $imageType;

		$image = XenForo_Image_Abstract::createFromFile($fileName, $imageType);
		if (!$image)
		{
			return false;
		}

		$image->thumbnailFixedShorterSide($maxDimensions);

		if ($image->getOrientation() != XenForo_Image_Abstract::ORIENTATION_SQUARE)
		{
			$cropX = floor(($image->getWidth() - $maxDimensions) / 2);
			$cropY = floor(($image->getHeight() - $maxDimensions) / 2);
			$image->crop($cropX, $cropY, $maxDimensions, $maxDimensions);
		}

		$newTempFile = tempnam(XenForo_Helper_File::getTempDir(), 'xf');
		if (!$newTempFile)
		{
			return false;
		}

		$image->output($outputType, $newTempFile, $imageQuality);
		unset($image);

		$filePath = $this->getResourceIconFilePath($resourceId);
		$directory = dirname($filePath);

		if (XenForo_Helper_File::createDirectory($directory, true) && is_writable($directory))
		{
			if (file_exists($filePath))
			{
				@unlink($filePath);
			}

			$writeSuccess = XenForo_Helper_File::safeRename($newTempFile, $filePath);
			if ($writeSuccess && file_exists($newTempFile))
			{
				@unlink($newTempFile);
			}
		}
		else
		{
			$writeSuccess = false;
		}

		if ($writeSuccess)
		{
			$dw = XenForo_DataWriter::create('XenResource_DataWriter_Resource');
			$dw->setExistingData($resourceId);
			$dw->set('icon_date', XenForo_Application::$time);
			$dw->save();
		}

		return $writeSuccess;
	}

	/**
	 * Deletes a resource icon
	 *
	 * @param integer $resourceId
	 */
	public function deleteResourceIcon($resourceId)
	{
		$dw = XenForo_DataWriter::create('XenResource_DataWriter_Resource');
		$dw->setExistingData($resourceId);
		$dw->set('icon_date', 0);
		$dw->save();

		$filePath = $this->getResourceIconFilePath($resourceId);
		@unlink($filePath);
	}

	public function getFeaturedResourcesInCategories(array $categoryIds, array $fetchOptions = array())
	{
		if (!$categoryIds)
		{
			return array();
		}

		if (isset($fetchOptions['join']) && $fetchOptions['join'] & self::FETCH_FEATURED)
		{
			$fetchOptions['join'] &= ~self::FETCH_FEATURED;
		}

		if (!empty($fetchOptions['order']) && $fetchOptions['order'] == 'random')
		{
			$orderClause = 'ORDER BY RAND()';
		}
		else
		{
			$orderClause = $this->prepareResourceOrderOptions($fetchOptions, 'feature.feature_date DESC');
		}
		$joinOptions = $this->prepareResourceFetchOptions($fetchOptions);
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

		return $this->fetchAllKeyed(
			$this->limitQueryResults('
				SELECT resource.*,
					feature.feature_date
					' . $joinOptions['selectFields'] . '
				FROM xf_resource_feature AS feature
				INNER JOIN xf_resource AS resource ON (resource.resource_id = feature.resource_id)
				' . $joinOptions['joinTables'] . '
				WHERE resource.resource_category_id IN (' . $this->_getDb()->quote($categoryIds) . ')
				' . $orderClause
			, $limitOptions['limit'], $limitOptions['offset']
		), 'resource_id');
	}

	public function featureResource(array $resource, $featureDate = null)
	{
		$db = $this->_getDb();

		if ($featureDate === null)
		{
			$featureDate = XenForo_Application::$time;
		}

		XenForo_Db::beginTransaction($db);

		$result = $db->query("
			INSERT INTO xf_resource_feature
				(resource_id, feature_date)
			VALUES
				(?, ?)
			ON DUPLICATE KEY UPDATE
				feature_date = VALUES(feature_date)
		", array($resource['resource_id'], $featureDate));

		if ($result->rowCount() == 1 && $resource['resource_state'] == 'visible')
		{
			// insert with a visible resource
			$dw = XenForo_DataWriter::create('XenResource_DataWriter_Category', XenForo_DataWriter::ERROR_SILENT);
			if (!$dw->setExistingData($resource['resource_category_id']))
			{
				return false;
			}

			$dw->updateFeaturedCount(1);
			$dw->save();

			XenForo_Model_Log::logModeratorAction('resource', $resource, 'feature');
		}

		XenForo_Db::commit($db);

		return true;
	}

	public function unfeatureResource(array $resource)
	{
		$db = $this->_getDb();

		XenForo_Db::beginTransaction($db);

		$affected = $db->delete('xf_resource_feature', 'resource_id = ' . $db->quote($resource['resource_id']));
		if ($affected && $resource['resource_state'] == 'visible')
		{
			// successful delete with a visible resource
			$dw = XenForo_DataWriter::create('XenResource_DataWriter_Category', XenForo_DataWriter::ERROR_SILENT);
			if (!$dw->setExistingData($resource['resource_category_id']))
			{
				return false;
			}

			$dw->updateFeaturedCount(-1);
			$dw->save();

			XenForo_Model_Log::logModeratorAction('resource', $resource, 'unfeature');
		}

		XenForo_Db::commit($db);

		return true;
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
		$this->_getCategoryModel()->standardizeViewingUserReferenceForCategory(
			$categoryId, $viewingUser, $categoryPermissions
		);
	}

	/**
	 * @return XenForo_Model_Attachment
	 */
	protected function _getAttachmentModel()
	{
		return $this->getModelFromCache('XenForo_Model_Attachment');
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
	 * @return XenResource_Model_Update
	 */
	protected function _getUpdateModel()
	{
		return $this->getModelFromCache('XenResource_Model_Update');
	}

	/**
	 * @return XenResource_Model_Version
	 */
	protected function _getVersionModel()
	{
		return $this->getModelFromCache('XenResource_Model_Version');
	}
}