<?php

class XenResource_Model_ResourceWatch extends XenForo_Model
{
	/**
	 * Gets a user's resource watch record for the specified resource ID.
	 *
	 * @param integer $userId
	 * @param integer $resourceId
	 *
	 * @return array|bool
	 */
	public function getUserResourceWatchByResourceId($userId, $resourceId)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM xf_resource_watch
			WHERE user_id = ?
				AND resource_id = ?
		', array($userId, $resourceId));
	}

	/**
	 * Get the resource watch records for a user, across many resource IDs.
	 *
	 * @param integer $userId
	 * @param array $resourceIds
	 *
	 * @return array Format: [resource_id] => resource watch info
	 */
	public function getUserResourceWatchByResourceIds($userId, array $resourceIds)
	{
		if (!$resourceIds)
		{
			return array();
		}

		return $this->fetchAllKeyed('
			SELECT *
			FROM xf_resource_watch
			WHERE user_id = ?
				AND resource_id IN (' . $this->_getDb()->quote($resourceIds) . ')
		', 'resource_id', $userId);
	}

	/**
	 * Get a list of all users watching a resource.
	 *
	 * @param integer $resourceId
	 * @param integer $categoryId
	 *
	 * @return array Format: [user_id] => info
	 */
	public function getUsersWatchingResource($resourceId, $categoryId)
	{
		return $this->fetchAllKeyed('
			SELECT user.*,
				user_option.*,
				user_profile.*,
				permission_combination.cache_value AS global_permission_cache,
				permission_cache_content.cache_value AS category_permission_cache,
				resource_watch.email_subscribe
			FROM xf_resource_watch AS resource_watch
			INNER JOIN xf_user AS user ON
				(user.user_id = resource_watch.user_id AND user.user_state = \'valid\' AND user.is_banned = 0)
			INNER JOIN xf_user_option AS user_option ON
				(user_option.user_id = user.user_id)
			INNER JOIN xf_user_profile AS user_profile ON
				(user_profile.user_id = user.user_id)
			INNER JOIN xf_permission_combination AS permission_combination ON
				(permission_combination.permission_combination_id = user.permission_combination_id)
			INNER JOIN xf_permission_cache_content AS permission_cache_content ON
				(permission_cache_content.permission_combination_id = user.permission_combination_id
					AND permission_cache_content.content_type = \'resource_category\'
					AND permission_cache_content.content_id = ?)
			WHERE resource_watch.resource_id = ?
		', 'user_id', array($categoryId, $resourceId));
	}

	/**
	 * Send a notification to the users watching the resource.
	 *
	 * @param array $update The reply that has been added
	 * @param array $resource Info about the resource the update is in
	 * @param array $noAlerts List of user ids to NOT alert (but still send email)
	 * @param array $noEmail List of user ids to not send an email
	 *
	 * @return array Empty or keys: alerted: user ids alerted, emailed: user ids emailed
	 */
	public function sendNotificationToWatchUsersOnUpdate(array $update, array $resource, array $noAlerts = array(), array $noEmail = array())
	{
		if ($update['message_state'] != 'visible' || $resource['resource_state'] != 'visible')
		{
			return array();
		}

		$resourceModel = $this->_getResourceModel();

		/* @var $userModel XenForo_Model_User */
		$userModel = $this->getModelFromCache('XenForo_Model_User');

		if (XenForo_Application::get('options')->emailWatchedThreadIncludeMessage)
		{
			$parseBbCode = true;
			$emailTemplate = 'watched_resource_update_messagetext';
		}
		else
		{
			$parseBbCode = false;
			$emailTemplate = 'watched_resource_update';
		}

		$resourceUser = $userModel->getUserById($resource['user_id']);
		if (!$resourceUser)
		{
			$resourceUser = $userModel->getVisitingGuestUser();
		}

		if (!empty($resource['category_breadcrumb']))
		{
			$category = $resource;
		}
		else
		{
			$category = $this->_getCategoryModel()->getCategoryById($resource['resource_category_id']);
			if (!$category)
			{
				return array();
			}
		}

		$alerted = array();
		$emailed = array();

		$users = $this->getUsersWatchingResource($resource['resource_id'], $resource['resource_category_id']);
		foreach ($users AS $user)
		{
			if ($user['user_id'] == $resource['user_id'])
			{
				continue;
			}

			$user['permissions'] = XenForo_Permission::unserializePermissions($user['global_permission_cache']);
			$categoryPermissions = XenForo_Permission::unserializePermissions($user['category_permission_cache']);

			if (!$resourceModel->canViewResourceAndContainer($resource, $category, $null, $user, $categoryPermissions))
			{
				continue;
			}

			if ($user['email_subscribe'] && $user['email'] && $user['user_state'] == 'valid')
			{
				if (!isset($update['messageText']) && $parseBbCode)
				{
					$bbCodeParserText = XenForo_BbCode_Parser::create(XenForo_BbCode_Formatter_Base::create('Text'));
					$update['messageText'] = new XenForo_BbCode_TextWrapper($update['message'], $bbCodeParserText);

					$bbCodeParserHtml = XenForo_BbCode_Parser::create(XenForo_BbCode_Formatter_Base::create('HtmlEmail'));
					$update['messageHtml'] = new XenForo_BbCode_TextWrapper($update['message'], $bbCodeParserHtml);
				}

				if (!isset($resource['titleCensored']))
				{
					$resource['titleCensored'] = XenForo_Helper_String::censorString($resource['title']);
					$update['titleCensored'] = XenForo_Helper_String::censorString($update['title']);
				}

				$user['email_confirm_key'] = $userModel->getUserEmailConfirmKey($user);

				$mail = XenForo_Mail::create($emailTemplate, array(
					'update' => $update,
					'resource' => $resource,
					'category' => $category,
					'resourceUser' => $resourceUser,
					'receiver' => $user
				), $user['language_id']);
				$mail->enableAllLanguagePreCache();
				$mail->queue($user['email'], $user['username']);

				$emailed[] = $user['user_id'];
				$noEmail[] = $user['user_id'];
			}

			if (XenForo_Model_Alert::userReceivesAlert($user, 'resource_update', 'insert'))
			{
				XenForo_Model_Alert::alert(
					$user['user_id'],
					$resource['user_id'],
					$resource['username'],
					'resource_update',
					$update['resource_update_id'],
					'insert'
				);

				$alerted[] = $user['user_id'];
				$noAlerts[] = $user['user_id'];
			}
		}

		return array(
			'emailed' => $emailed,
			'alerted' => $alerted
		);
	}

	/**
	 * Get the resources watched by a specific user.
	 *
	 * @param integer $userId
	 * @param array $fetchOptions Resource fetch options (uses all valid for XenForo_Model_Resource).
	 *
	 * @return array Format: [resource_id] => info
	 */
	public function getResourcesWatchedByUser($userId, array $fetchOptions = array())
	{
		$joinOptions = $this->_getResourceModel()->prepareResourceFetchOptions($fetchOptions);
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

		return $this->fetchAllKeyed($this->limitQueryResults(
			'
				SELECT resource.*,
					resource_watch.email_subscribe
					' . $joinOptions['selectFields'] . '
				FROM xf_resource_watch AS resource_watch
				INNER JOIN xf_resource AS resource ON
					(resource.resource_id = resource_watch.resource_id)
				' . $joinOptions['joinTables'] . '
				WHERE resource_watch.user_id = ?
					AND resource.resource_state = \'visible\'
				ORDER BY resource.last_update DESC
			', $limitOptions['limit'], $limitOptions['offset']
		), 'resource_id', $userId);
	}

	/**
	 * Gets the total number of resources a user is watching.
	 *
	 * @param integer $userId
	 *
	 * @return integer
	 */
	public function countResourcesWatchedByUser($userId)
	{
		return $this->_getDb()->fetchOne('
			SELECT COUNT(*)
			FROM xf_resource_watch AS resource_watch
			INNER JOIN xf_resource AS resource ON
					(resource.resource_id = resource_watch.resource_id)
			WHERE resource_watch.user_id = ?
					AND resource.resource_state = \'visible\'
		', $userId);
	}

	/**
	 * Sets the resource watch state as requested. An empty state will delete any watch record.
	 *
	 * @param integer $userId
	 * @param integer $resourceId
	 * @param string $state Values: watch_email, watch_no_email, (empty string)
	 *
	 * @return boolean
	 */
	public function setResourceWatchState($userId, $resourceId, $state)
	{
		if (!$userId)
		{
			return false;
		}

		$resourceWatch = $this->getUserResourceWatchByResourceId($userId, $resourceId);

		switch ($state)
		{
			case 'watch_email':
			case 'watch_no_email':
				$dw = XenForo_DataWriter::create('XenResource_DataWriter_ResourceWatch');
				if ($resourceWatch)
				{
					$dw->setExistingData($resourceWatch, true);
				}
				else
				{
					$dw->set('user_id', $userId);
					$dw->set('resource_id', $resourceId);
				}
				$dw->set('email_subscribe', ($state == 'watch_email' ? 1 : 0));
				$dw->save();
				return true;

			case '':
				if ($resourceWatch)
				{
					$dw = XenForo_DataWriter::create('XenResource_DataWriter_ResourceWatch');
					$dw->setExistingData($resourceWatch, true);
					$dw->delete();
				}
				return true;

			default:
				return false;
		}
	}

	public function setResourceWatchStateForAll($userId, $state)
	{
		$userId = intval($userId);
		if (!$userId)
		{
			return false;
		}

		$db = $this->_getDb();

		switch ($state)
		{
			case 'watch_email':
				return $db->update('xf_resource_watch',
					array('email_subscribe' => 1),
					"user_id = " . $db->quote($userId)
				);

			case 'watch_no_email':
				return $db->update('xf_resource_watch',
					array('email_subscribe' => 0),
					"user_id = " . $db->quote($userId)
				);

			case '':
				return $db->delete('xf_resource_watch', "user_id = " . $db->quote($userId));

			default:
				return false;
		}
	}

	/**
	 * Sets the resource watch state based on the user's default. This will never unwatch a resource.
	 *
	 * @param integer $userId
	 * @param integer $resourceId
	 * @param string $state Values: watch_email, watch_no_email, (empty string)
	 *
	 * @return boolean
	 */
	public function setResourceWatchStateWithUserDefault($userId, $resourceId, $state)
	{
		if (!$userId)
		{
			return false;
		}

		$resourceWatch = $this->getUserResourceWatchByResourceId($userId, $resourceId);
		if ($resourceWatch)
		{
			return true;
		}

		switch ($state)
		{
			case 'watch_email':
			case 'watch_no_email':
				$dw = XenForo_DataWriter::create('XenResource_DataWriter_ResourceWatch');
				$dw->set('user_id', $userId);
				$dw->set('resource_id', $resourceId);
				$dw->set('email_subscribe', ($state == 'watch_email' ? 1 : 0));
				$dw->save();
				return true;

			default:
				return false;
		}
	}

	/**
	 * Sets the resource watch state for the visitor from an array of input. Keys in input:
	 * 	* watch_resource_state: if true, uses watch_resource and watch_resource_email to set state as requested
	 *  * watch_resource: if true, watches resource
	 *  * watch_resource_email: if true (and watch_resource is true), watches resource with email; otherwise, watches resource without email
	 *
	 * @param integer $resourceId
	 * @param array $input
	 *
	 * @return boolean
	 */
	public function setVisitorResourceWatchStateFromInput($resourceId, array $input)
	{
		$visitor = XenForo_Visitor::getInstance();

		if (!$visitor['user_id'])
		{
			return false;
		}

		if ($input['watch_resource_state'])
		{
			if ($input['watch_resource'])
			{
				$watchState = ($input['watch_resource_email'] ? 'watch_email' : 'watch_no_email');
			}
			else
			{
				$watchState = '';
			}

			return $this->setResourceWatchState($visitor['user_id'], $resourceId, $watchState);
		}
		else
		{
			return $this->setResourceWatchStateWithUserDefault($visitor['user_id'], $resourceId, $visitor['default_watch_state']);
		}
	}

	/**
	 * Gets the resource watch state for the specified resource for the visiting user.
	 *
	 * @param integer|bool $resourceId Resource ID, or false if unknown
	 * @param boolean $useDefaultIfNotWatching If true, uses visitor default if resource isn't watched
	 *
	 * @return string Values: watch_email, watch_no_email, (empty string)
	 */
	public function getResourceWatchStateForVisitor($resourceId = false, $useDefaultIfNotWatching = true)
	{
		$visitor = XenForo_Visitor::getInstance();
		if (!$visitor['user_id'])
		{
			return '';
		}

		if ($resourceId)
		{
			$resourceWatch = $this->getUserResourceWatchByResourceId($visitor['user_id'], $resourceId);
		}
		else
		{
			$resourceWatch = false;
		}

		if ($resourceWatch)
		{
			return ($resourceWatch['email_subscribe'] ? 'watch_email' : 'watch_no_email');
		}
		else if ($useDefaultIfNotWatching)
		{
			return $visitor['default_watch_state'];
		}
		else
		{
			return '';
		}
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
	 * @return XenForo_Model_Alert
	 */
	protected function _getAlertModel()
	{
		return $this->getModelFromCache('XenForo_Model_Alert');
	}
}