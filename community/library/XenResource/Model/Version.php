<?php

class XenResource_Model_Version extends XenForo_Model
{
	const FETCH_BLOG     = 0x01;
	const FETCH_FILE     = 0x02;
	const FETCH_RESOURCE = 0x04;

	public function getVersionById($versionId, array $fetchOptions = array())
	{
		if (empty($versionId))
		{
			return array();
		}

		$joinOptions = $this->prepareVersionFetchOptions($fetchOptions);

		return $this->_getDb()->fetchRow('
			SELECT version.*
				' . $joinOptions['selectFields'] . '
			FROM xf_resource_version AS version
			' . $joinOptions['joinTables'] . '
			WHERE version.resource_version_id = ?
		', $versionId);
	}

	public function getVersionsByIds(array $versionIds, array $fetchOptions = array())
	{
		if (empty($versionIds))
		{
			return array();
		}

		return $this->getVersions(array(
			'resource_version_id' => $versionIds,
			'moderated' => true,
			'deleted' => true
		), $fetchOptions);
	}

	public function getVersions(array $conditions, array $fetchOptions = array())
	{
		$whereClause = $this->prepareVersionConditions($conditions, $fetchOptions);

		$orderClause = $this->prepareVersionOrderOptions($fetchOptions, 'version.release_date DESC');
		$joinOptions = $this->prepareVersionFetchOptions($fetchOptions);
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

		return $this->fetchAllKeyed($this->limitQueryResults(
			'
				SELECT version.*
					' . $joinOptions['selectFields'] . '
				FROM xf_resource_version AS version
				' . $joinOptions['joinTables'] . '
				WHERE ' . $whereClause . '
				' . $orderClause . '
			', $limitOptions['limit'], $limitOptions['offset']
		), 'resource_version_id');
	}

	public function prepareVersionConditions(array $conditions, array &$fetchOptions = array())
	{
		$db = $this->_getDb();
		$sqlConditions = array();

		if (!empty($conditions['resource_id']))
		{
			$sqlConditions[] = 'version.resource_id = ' . $db->quote($conditions['resource_id']);
		}

		if (!empty($conditions['version_id_not']))
		{
			$sqlConditions[] = 'version.resource_version_id <> ' . $db->quote($conditions['version_id_not']);
		}

		if (!empty($conditions['resource_version_id']))
		{
			if (is_array($conditions['resource_version_id']))
			{
				$sqlConditions[] = 'version.resource_version_id IN (' . $db->quote($conditions['resource_version_id']) . ')';
			}
			else
			{
				$sqlConditions[] = 'version.resource_version_id = ' . $db->quote($conditions['resource_version_id']);
			}
		}

		if (isset($conditions['deleted']) || isset($conditions['moderated']))
		{
			$condition = $this->prepareStateLimitFromConditions($conditions, 'version', 'version_state');
			$condition = str_replace('version.user_id', 'resource.user_id', $condition);
			$sqlConditions[] = $condition;

			if (!isset($fetchOptions['join']))
			{
				$fetchOptions['join'] = 0;
			}

			$fetchOptions['join'] |= self::FETCH_RESOURCE;
		}
		else
		{
			$sqlConditions[] = "version.version_state = 'visible'";
		}

		return $this->getConditionsForClause($sqlConditions);
	}

	public function prepareVersionOrderOptions(array &$fetchOptions, $defaultOrderSql = '')
	{
		$choices = array(
			'download_count' => 'version.download_count',
		);
		return $this->getOrderByClause($choices, $fetchOptions, $defaultOrderSql);
	}

	public function prepareVersionFetchOptions(array $fetchOptions)
	{
		$selectFields = '';
		$joinTables = '';
		$db = $this->_getDb();

		if (!empty($fetchOptions['join']))
		{
			if ($fetchOptions['join'] & self::FETCH_BLOG)
			{
				$selectFields .= ',
					resource_update.*';
				$joinTables .= '
					LEFT JOIN xf_resource_update AS resource_update ON
						(resource_update.resource_update_id = version.resource_update_id)';
			}

			if ($fetchOptions['join'] & self::FETCH_FILE)
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

			if ($fetchOptions['join'] & self::FETCH_RESOURCE)
			{
				$selectFields .= ',
					resource.*,
					resource.download_count AS resource_download_count, version.download_count,
					resource.rating_count AS resource_rating_count, version.rating_count,
					resource.rating_sum AS resource_rating_sum, version.rating_sum';
				$joinTables .= '
					INNER JOIN xf_resource AS resource ON
						(resource.resource_id = version.resource_id)';
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

		return array(
			'selectFields' => $selectFields,
			'joinTables'   => $joinTables
		);
	}

	/**
	 * Prepare a number of versions FROM ONE RESOURCE for output
	 *
	 * @param array $versions
	 * @param array $resource
	 * @param array $category
	 *
	 * @return array
	 */
	public function prepareVersions(array $versions, array $resource, array $category)
	{
		foreach ($versions AS &$version)
		{
			$version = $this->prepareVersion($version, $resource, $category);
		}

		return $versions;
	}

	public function prepareVersion(array $version, array $resource, array $category)
	{
		$version['version_string'] = XenForo_Helper_String::censorString($version['version_string']);
		$version['isCensored'] = true;

		$version['canEdit'] = $this->canEditVersion($version, $resource, $category);
		$version['canDelete'] = $this->canDeleteVersion($version, $resource, $category, 'soft');

		return $version;
	}

	public function getVersionFileParams(array $version, array $contentData, array $viewingUser = null)
	{
		return array(
			'hash' => md5(uniqid('', true)),
			'content_type' => 'resource_version',
			'content_data' => $contentData
		);
	}

	public function getVersionFileConstraints()
	{
		$options = XenForo_Application::get('options');

		return array(
			'extensions' => preg_split('/\s+/', trim($options->resourceExtensions)),
			'size' => $options->resourceMaxFileSize * 1024,
			'width' => $options->attachmentMaxDimensions['width'],
			'height' => $options->attachmentMaxDimensions['height'],
			'count' => 1
		);
	}

	public function canDownloadVersion(array $version, array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

		if (!$this->_getResourceModel()->canDownloadResource($resource, $category, $errorPhraseKey, $viewingUser))
		{
			return false;
		}

		if ($version['version_state'] == 'moderated')
		{
			if (!XenForo_Permission::hasContentPermission($categoryPermissions, 'viewModerated'))
			{
				if (!$viewingUser['user_id'] || $viewingUser['user_id'] != $resource['user_id'])
				{
					return false;
				}
			}
		}
		else if ($version['version_state'] == 'deleted')
		{
			if (!XenForo_Permission::hasContentPermission($categoryPermissions, 'viewDeleted'))
			{
				return false;
			}
		}

		return true;
	}

	public function canAddVersion(array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		$this->standardizeViewingUserReferenceForCategory($category, $viewingUser, $categoryPermissions);

		if (!$viewingUser['user_id'])
		{
			return false;
		}

		if ($resource['resource_state'] != 'visible') {
			return false;
		}

		if ($resource['user_id'] == $viewingUser['user_id'])
		{
			return XenForo_Permission::hasContentPermission($categoryPermissions, 'updateSelf');
		}

		return false;
	}

	public function canEditVersion(array $version, array $resource, array $category, &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		return false; // TODO: versions are not editable
	}

	public function canDeleteVersion(array $version, array $resource, array $category, $type = 'soft', &$errorPhraseKey = '', array $viewingUser = null, array $categoryPermissions = null)
	{
		if ($resource['current_version_id'] == $version['resource_version_id'])
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

	public function logVersionDownload(array $version, $userId)
	{
		$db = $this->_getDb();

		XenForo_Db::beginTransaction($db);

		if (!$userId || !$db->fetchOne('
			SELECT 1
			FROM xf_resource_download
			WHERE user_id = ?
				AND resource_id = ?
			LIMIT 1
		', array($userId, $version['resource_id'])))
		{
			$db->query('
				UPDATE xf_resource
				SET download_count = download_count + 1
				WHERE resource_id = ?
			', $version['resource_id']);
		}

		if ($userId)
		{
			$statement = $db->query('
				INSERT INTO xf_resource_download
					(resource_version_id, user_id, resource_id, last_download_date)
				VALUES
					(?, ?, ?, ?)
				ON DUPLICATE KEY UPDATE
					last_download_date = VALUES(last_download_date)
			', array($version['resource_version_id'], $userId, $version['resource_id'], XenForo_Application::$time));

			$updateVersionCount = ($statement->rowCount() == 1); // 1 = insert, 2 = update, don't count
		}
		else
		{
			$updateVersionCount = true;
		}

		if ($updateVersionCount)
		{
			$db->query('
				UPDATE xf_resource_version
				SET download_count = download_count + 1
				WHERE resource_version_id = ?
			', $version['resource_version_id']);
		}

		XenForo_Db::commit($db);
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