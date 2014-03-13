<?php

class XenResource_Model_Update extends XenForo_Model
{
	const FETCH_RESOURCE = 0x01;
	const FETCH_CATEGORY = 0x02;
	const FETCH_USER     = 0x04;
	const FETCH_RESOURCE_VERSION = 0x08;

	/**
	 * Get a single resource author post from the DB, specified by its ID
	 *
	 * @param integer $updateId
	 * @param array
	 *
	 * @return array
	 */
	public function getUpdateById($updateId, array $fetchOptions = array())
	{
		if (empty($updateId))
		{
			return array();
		}

		$joinOptions = $this->prepareUpdateFetchOptions($fetchOptions);

		return $this->_getDb()->fetchRow('
			SELECT resource_update.*
				' . $joinOptions['selectFields'] . '
			FROM xf_resource_update AS resource_update
				' . $joinOptions['joinTables'] . '
			WHERE resource_update.resource_update_id = ?
		', $updateId);
	}

	public function getUpdates(array $conditions, array $fetchOptions = array())
	{
		$whereClause = $this->prepareUpdateConditions($conditions, $fetchOptions);

		$orderClause = $this->prepareUpdateOrderOptions($fetchOptions, 'resource_update.post_date');
		$joinOptions = $this->prepareUpdateFetchOptions($fetchOptions);
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

		return $this->fetchAllKeyed($this->limitQueryResults(
			'
				SELECT resource_update.*
					' . $joinOptions['selectFields'] . '
				FROM xf_resource_update AS resource_update
				' . $joinOptions['joinTables'] . '
				WHERE ' . $whereClause . '
				' . $orderClause . '
			', $limitOptions['limit'], $limitOptions['offset']
		), 'resource_update_id');
	}

	public function countUpdates(array $conditions = array())
	{
		$fetchOptions = array();
		$whereClause = $this->prepareUpdateConditions($conditions, $fetchOptions);

		$joinOptions = $this->prepareUpdateFetchOptions($fetchOptions);

		return $this->_getDb()->fetchOne('
			SELECT COUNT(*)
			FROM xf_resource_update AS resource_update
			' . $joinOptions['joinTables'] . '
			WHERE ' . $whereClause
		);
	}

	public function prepareUpdateConditions(array $conditions, array &$fetchOptions = array())
	{
		$db = $this->_getDb();
		$sqlConditions = array();

		if (!empty($conditions['resource_update_id']))
		{
			if (is_array($conditions['resource_update_id']))
			{
				$sqlConditions[] = 'resource_update.resource_update_id IN (' . $db->quote($conditions['resource_update_id']) . ')';
			}
			else
			{
				$sqlConditions[] = 'resource_update.resource_update_id = ' . $db->quote($conditions['resource_update_id']);
			}
		}

		if (!empty($conditions['resource_update_id_not']))
		{
			if (is_array($conditions['resource_update_id_not']))
			{
				$sqlConditions[] = 'resource_update.resource_update_id NOT IN (' . $db->quote($conditions['resource_update_id_not']) . ')';
			}
			else
			{
				$sqlConditions[] = 'resource_update.resource_update_id <> ' . $db->quote($conditions['resource_update_id_not']);
			}
		}

		if (isset($conditions['deleted']) || isset($conditions['moderated']))
		{
			$condition = $this->prepareStateLimitFromConditions($conditions, 'resource_update', 'message_state');
			$condition = str_replace('resource_update.user_id', 'resource.user_id', $condition);
			$sqlConditions[] = $condition;

			if (!isset($fetchOptions['join']))
			{
				$fetchOptions['join'] = 0;
			}

			$fetchOptions['join'] |= self::FETCH_RESOURCE;
		}
		else
		{
			// sanity check: only get visible updates unless we've explicitly said to get something else
			$sqlConditions[] = "resource_update.message_state = 'visible'";
		}

		if (!empty($conditions['resource_id']))
		{
			$sqlConditions[] = 'resource_update.resource_id = ' . $db->quote($conditions['resource_id']);
		}

		return $this->getConditionsForClause($sqlConditions);
	}

	public function prepareUpdateOrderOptions(array &$fetchOptions, $defaultOrderSql = '')
	{
		$choices = array(
			'post_date' => 'resource_update.post_date'
		);
		return $this->getOrderByClause($choices, $fetchOptions, $defaultOrderSql);
	}

	public function prepareUpdateFetchOptions(array $fetchOptions)
	{
		$selectFields = '';
		$joinTables = '';

		$db = $this->_getDb();

		if (!empty($fetchOptions['join']))
		{
			if ($fetchOptions['join'] & self::FETCH_RESOURCE)
			{
				$selectFields .= ',
					resource.*, resource.title AS resource_title, resource_update.title';

				$joinTables .= '
					INNER JOIN xf_resource AS resource ON
						(resource.resource_id = resource_update.resource_id)';
			}

			if ($fetchOptions['join'] & self::FETCH_CATEGORY && $fetchOptions['join'] & self::FETCH_RESOURCE)
			{
				$selectFields .= ',
					category.*, category.last_update AS category_last_update, resource.last_update';
				$joinTables .= '
					INNER JOIN xf_resource_category AS category ON
						(category.resource_category_id = resource.resource_category_id)';
			}

			if ($fetchOptions['join'] & self::FETCH_USER && $fetchOptions['join'] & self::FETCH_RESOURCE)
			{
				$selectFields .= ',
					user.*';
				$joinTables .= '
					INNER JOIN xf_user AS user ON
						(user.user_id = resource.user_id)';
			}

			if ($fetchOptions['join'] & self::FETCH_RESOURCE_VERSION && $fetchOptions['join'] & self::FETCH_RESOURCE)
			{
				$selectFields .= ',
					version.version_string,
					version.release_date,
					version.download_url,
					version.rating_count AS version_rating_count,
					version.rating_sum AS version_rating_sum,
					version.download_count AS version_download_count';
				$joinTables .= '
					INNER JOIN xf_resource_version AS version ON
						(version.resource_version_id = resource.current_version_id)';
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

		if (isset($fetchOptions['likeUserId']))
		{
			if (empty($fetchOptions['likeUserId']))
			{
				$selectFields .= ',
					0 AS like_date';
			}
			else
			{
				$selectFields .= ',
					liked_content.like_date';
				$joinTables .= '
					LEFT JOIN xf_liked_content AS liked_content
						ON (liked_content.content_type = \'resource_update\'
							AND liked_content.content_id = resource_update.resource_update_id
							AND liked_content.like_user_id = ' . $db->quote($fetchOptions['likeUserId']) . ')';
			}
		}

		if (isset($fetchOptions['setUserId']))
		{
			$selectFields .= ',
				' . intval($fetchOptions['setUserId']) . ' AS user_id';
		}

		return array(
			'selectFields' => $selectFields,
			'joinTables'   => $joinTables
		);
	}

	public function getUpdatesByIds(array $updateIds, array $fetchOptions = array())
	{
		if (empty($updateIds))
		{
			return array();
		}

		return $this->getUpdates(array(
			'resource_update_id' => $updateIds,
			'moderated' => true,
			'deleted' => true
		), $fetchOptions);
	}

	/**
	 * Gets update IDs in the specified range. The IDs returned will be those immediately
	 * after the "start" value (not including the start), up to the specified limit.
	 *
	 * @param integer $start IDs greater than this will be returned
	 * @param integer $limit Number of posts to return
	 *
	 * @return array List of IDs
	 */
	public function getUpdateIdsInRange($start, $limit)
	{
		$db = $this->_getDb();

		return $db->fetchCol($db->limit('
			SELECT resource_update_id
			FROM xf_resource_update
			WHERE resource_update_id > ?
			ORDER BY resource_update_id
		', $limit), $start);
	}

	public function prepareUpdates(array $updates, array $resource, array $category, array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);

		foreach ($updates AS &$update)
		{
			$update = $this->prepareUpdate($update, $resource, $category, $viewingUser);
		}

		return $updates;
	}

	public function prepareUpdate(array $update, array $resource, array $category, array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);

		$update['title'] = XenForo_Helper_String::censorString($update['title']);
		$update['isCensored'] = true;

		$update['user_id'] = $resource['user_id'];

		$update['canLike'] = $this->canLikeUpdate($update, $resource, $category, $errorPhraseKey, $viewingUser);
		$update['canReport'] = $this->canReportUpdate($update, $resource, $category, $errorPhraseKey, $viewingUser);
		$update['canEdit'] = $this->canEditUpdate($update, $resource, $category, $errorPhraseKey, $viewingUser);
		$update['canDelete'] = $this->canDeleteUpdate($update, $resource, $category, $errorPhraseKey, $viewingUser);
		$update['canWarn'] = $this->canWarnUpdate($update, $resource, $category, $errorPhraseKey, $viewingUser);

		$update['isDescriptionUpdate'] = ($update['resource_update_id'] == $resource['description_update_id']);

		if ($update['likes'])
		{
			$update['likeUsers'] = unserialize($update['like_users']);
		}

		if (isset($update['resource_title']))
		{
			$update = $this->_getResourceModel()->prepareResource($update, $category, $viewingUser);
		}

		if (!isset($update['isTrusted']) && isset($resource['isTrusted']))
		{
			$update['isTrusted'] = $resource['isTrusted'];
		}

		return $update;
	}

	public function snippetUpdate(array $update, $length = 500)
	{
		$update['message'] = $this->snippetUpdateText($update['message'], $length, $isSnippet);
		$update['isMessageTrimmed'] = $isSnippet;

		return $update;
	}

	public function snippetUpdateText($text, $length, &$isSnippet)
	{
		$parser = XenForo_BbCode_Parser::create(XenForo_BbCode_Formatter_Base::create('XenForo_BbCode_Formatter_BbCode_AutoLink', false));
		$newText = $parser->render(XenForo_Helper_String::wholeWordTrim($text, $length));

		$isSnippet = (strlen($newText) < strlen($text));
		return $newText;
	}

	/**
	 * Gets the attachments that belong to the given posts, and merges them in with
	 * their parent post (in the attachments key). The attachments key will not be
	 * set if no attachments are found for the post.
	 *
	 * @param array $posts
	 *
	 * @return array Posts, with attachments added where necessary
	 */
	public function getAndMergeAttachmentsIntoUpdates(array $updates)
	{
		$updateIds = array();

		foreach ($updates AS $updateId => $update)
		{
			if ($update['attach_count'])
			{
				$updateIds[] = $updateId;
			}
		}

		if ($updateIds)
		{
			$attachmentModel = $this->_getAttachmentModel();

			foreach ($attachmentModel->getAttachmentsByContentIds('resource_update', $updateIds) AS $attachment)
			{
				$preparedAttachment = $attachmentModel->prepareAttachment($attachment);
				$updates[$attachment['content_id']]['attachments'][$attachment['attachment_id']] = $preparedAttachment;
			}
		}

		return $updates;
	}

	/**
	 * Gets the set of attachment params required to allow uploading.
	 *
	 * @param array $contentData Information about the content, for URL building
	 * @param array|null $viewingUser
	 * @param string|null $tempHash
	 *
	 * @return array|bool
	 */
	public function getUpdateAttachmentParams(array $contentData = array(), array $viewingUser = null, $tempHash = null)
	{
		if ($this->canUploadAndManageUpdateAttachment($null, $viewingUser))
		{
			return array(
				'hash' => $tempHash ? $tempHash : md5(uniqid('', true)),
				'content_type' => 'resource_update',
				'content_data' => $contentData
			);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Gets the normal attachment constraints, then alters them for resource screen shots.
	 *
	 * @return array
	 */
	public function getUpdateAttachmentConstraints()
	{
		$attachmentConstraints = $this->getModelFromCache('XenForo_Model_Attachment')->getAttachmentConstraints();

		$attachmentConstraints['extensions'] = array('jpg', 'jpeg', 'jpe', 'png', 'gif');

		return $attachmentConstraints;
	}

	/**
	* Determines if a user can view updates for the given resource.
	* Does not check parent viewing permissions.
	*
	* @param array $update
	* @param array $resource
	* @param array $category
	* @param string $errorPhraseKey
	* @param array $viewingUser
	* @param array|null $categoryPermissions Permissions for this category; if null, use visitor's
	*
	* @return boolean
	*/
	public function canViewUpdate(array $update, array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

		if ($update['message_state'] == 'moderated')
		{
			if (!XenForo_Permission::hasContentPermission($categoryPermissions, 'viewModerated'))
			{
				if (!$viewingUser['user_id'] || $viewingUser['user_id'] != $resource['user_id'])
				{
					return false;
				}
			}
		}
		else if ($update['message_state'] == 'deleted')
		{
			if (!XenForo_Permission::hasContentPermission($categoryPermissions, 'viewDeleted'))
			{
				return false;
			}
		}

		return true;
	}

	/**
	* Determines if a user can view updates for the given resource, ensuring
	* they can view all parent elements as well.
	*
	* @param array $update
	* @param array $resource
	* @param array $category
	* @param string $errorPhraseKey
	* @param array $viewingUser
	* @param array|null $categoryPermissions Permissions for this category; if null, use visitor's
	*
	* @return boolean
	*/
	public function canViewUpdateAndContainer(array $update, array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		if (!$this->canViewUpdate($update, $resource, $category, $errorPhraseKey, $viewingUser, $categoryPermissions))
		{
			return false;
		}

		return $this->_getResourceModel()->canViewResourceAndContainer($resource, $category, $errorPhraseKey, $viewingUser, $categoryPermissions);
	}

	/**
	* Determines if a user can add a update for the given resource.
	* Does not check resource viewing permissions.
	*
	* @param array $resource
	* @param array $category
	* @param string $errorPhraseKey
	* @param array $viewingUser
	* @param array|null $categoryPermissions Permissions for this category; if null, use visitor's
	*
	* @return boolean
	*/
	public function canAddUpdate(array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

		if (!$viewingUser['user_id'])
		{
			return false;
		}

		if ($resource['user_id'] == $viewingUser['user_id'])
		{
			return XenForo_Permission::hasContentPermission($categoryPermissions, 'updateSelf');
		}

		return false;
	}

	/**
	 * Determines if a user can edit this update for the given resource.
	 * Does not check resource viewing permissions.
	 *
	 * @param array $update
	 * @param array $resource
	 * @param array $category
	 * @param string $errorPhraseKey
	 * @param array $viewingUser
	 * @param array|null $categoryPermissions Permissions for this category; if null, use visitor's
	 *
	 * @return boolean
	 */
	public function canEditUpdate(array $update, array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		return $this->_getResourceModel()->canEditResource($resource, $category, $errorPhraseKey, $viewingUser);
	}

	/**
	 * Determines if a user can delete this update for the given resource.
	 * Does not check resource viewing permissions.
	 *
	 * @param array $update
	 * @param array $resource
	 * @param array $category
	 * @param string $type
	 * @param string $errorPhraseKey
	 * @param array $viewingUser
	 * @param array|null $categoryPermissions Permissions for this category; if null, use visitor's
	 *
	 * @return boolean
	 */
	public function canDeleteUpdate(array $update, array $resource, array $category, $type = 'soft', &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		if ($resource['description_update_id'] == $update['resource_update_id'])
		{
			return false;
		}

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
			return XenForo_Permission::hasContentPermission($categoryPermissions, 'updateSelf');
		}

		return XenForo_Permission::hasContentPermission($categoryPermissions, 'deleteAny');
	}

	/**
	 * Determines if a user can like this update for the given resource.
	 * Does not check resource viewing permissions.
	 *
	 * @param array $update
	 * @param array $resource
	 * @param array $category
	 * @param string $errorPhraseKey
	 * @param array $viewingUser
	 * @param array|null $categoryPermissions Permissions for this category; if null, use visitor's
	 *
	 * @return boolean
	 */
	public function canLikeUpdate(array $update, array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

		if (!$viewingUser['user_id'])
		{
			return false;
		}

		if ($resource['user_id'] == $viewingUser['user_id']) // resource user_id == update user_id
		{
			$errorPhraseKey = 'liking_own_content_cheating';
			return false;
		}

		return XenForo_Permission::hasContentPermission($categoryPermissions, 'like');
	}

	/**
	 * Determines if a user can warn this update for the given resource.
	 * Does not check resource viewing permissions.
	 *
	 * @param array $update
	 * @param array $resource
	 * @param array $category
	 * @param string $errorPhraseKey
	 * @param array $viewingUser
	 * @param array|null $categoryPermissions Permissions for this category; if null, use visitor's
	 *
	 * @return boolean
	 */
	public function canWarnUpdate(array $update, array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

		if ($update['warning_id'] || empty($resource['user_id']))
		{
			return false;
		}

		if (!empty($resource['is_admin']) || !empty($resource['is_moderator']))
		{
			return false;
		}

		if ($resource['user_id'] == $viewingUser['user_id'])
		{
			return false;
		}

		if (!$viewingUser['user_id'])
		{
			return false;
		}

		return XenForo_Permission::hasContentPermission($categoryPermissions, 'warn');
	}

	/**
	 * Checks that the viewing user may report the specified update
	 *
	 * @param array $update
	 * @param array $resource
	 * @param array $category
	 * @param string $errorPhraseKey
	 * @param array $viewingUser
	 * @param array|null $categoryPermissions Permissions for this category; if null, use visitor's
	 *
	 * @return boolean
	 */
	public function canReportUpdate(array $update, array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		if ($update['message_state'] != 'visible')
		{
			return false;
		}

		return $this->getModelFromCache('XenForo_Model_User')->canReportContent($errorPhraseKey, $viewingUser);
	}

	/**
	* Checks that the viewing user may managed a reported update
	*
	* @param array $update
	* @param array $resource
	* @param array $category
	* @param string $errorPhraseKey
	* @param array $viewingUser
	* @param array|null $categoryPermissions Permissions for this category; if null, use visitor's
	*
	* @return boolean
	*/
	public function canManageReportedUpdate(array $update, array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

		if (!$viewingUser['user_id'])
		{
			return false;
		}

		return (
			XenForo_Permission::hasContentPermission($categoryPermissions, 'deleteAny')
			|| XenForo_Permission::hasContentPermission($categoryPermissions, 'editAny')
		);
	}

	/**
	 * Determines if a user can view update images for the given resource.
	 * Does not check resource viewing permissions.
	 *
	 * @param array $resource
	 * @param array $category
	 * @param string $errorPhraseKey
	 * @param array $viewingUser
	 * @param array|null $categoryPermissions Permissions for this category; if null, use visitor's
	 *
	 * @return boolean
	 */
	public function canViewUpdateImages(array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

		return XenForo_Permission::hasContentPermission($categoryPermissions, 'viewUpdateAttach');
	}

	/**
	 * Determines if a new attachment can be posted in the specified resource,
	 * with the given permissions. If no permissions are specified, permissions
	 * are retrieved from the currently visiting user. This does not check viewing permissions.
	 *
	 * @param string $errorPhraseKey Returned phrase key for a specific error
	 * @param array|null $viewingUser
	 *
	 * @return boolean
	 */
	public function canUploadAndManageUpdateAttachment(&$errorPhraseKey = '', array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);

		return ($viewingUser['user_id']
			&& XenForo_Permission::hasPermission($viewingUser['permissions'], 'resource', 'uploadUpdateAttach')
		);
	}

	/**
	 * Attempts to update any instances of an old username in like_users with a new username
	 *
	 * @param integer $oldUserId
	 * @param integer $newUserId
	 * @param string $oldUsername
	 * @param string $newUsername
	 */
	public function batchUpdateLikeUser($oldUserId, $newUserId, $oldUsername, $newUsername)
	{
		$db = $this->_getDb();

		$oldUserId = $db->quote($oldUserId);
		$newUserId = $db->quote($newUserId);

		// note that xf_liked_content should have already been updated with $newUserId

		$db->query('
			UPDATE xf_resource_update
			SET like_users = REPLACE(like_users, ' .
				$db->quote('i:' . $oldUserId . ';s:8:"username";s:' . strlen($oldUsername) . ':"' . $oldUsername . '";') . ', ' .
				$db->quote('i:' . $newUserId . ';s:8:"username";s:' . strlen($newUsername) . ':"' . $newUsername . '";') . ')
			WHERE resource_update_id IN (
				SELECT content_id FROM xf_liked_content
				WHERE content_type = \'resource_update\'
				AND like_user_id = ' . $newUserId . '
			)
		');
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