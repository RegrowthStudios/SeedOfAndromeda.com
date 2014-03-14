<?php

class XenResource_Model_CategoryWatch extends XenForo_Model
{
	/**
	 * Gets a user's watch record for the specified category ID.
	 *
	 * @param integer $userId
	 * @param integer $categoryId
	 *
	 * @return array|bool
	 */
	public function getUserCategoryWatchByCategoryId($userId, $categoryId)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM xf_resource_category_watch
			WHERE user_id = ?
				AND resource_category_id = ?
		', array($userId, $categoryId));
	}

	/**
	 * Get the watch records for a user, across many category IDs.
	 *
	 * @param integer $userId
	 * @param array $categoryIds
	 *
	 * @return array Format: [resource_category_id] => watch info
	 */
	public function getUserCategoryWatchByCategoryIds($userId, array $categoryIds)
	{
		if (!$categoryIds)
		{
			return array();
		}

		return $this->fetchAllKeyed('
			SELECT *
			FROM xf_resource_category_watch
			WHERE user_id = ?
				AND resource_category_id IN (' . $this->_getDb()->quote($categoryIds) . ')
		', 'resource_category_id', $userId);
	}

	/**
	 * @param integer $userId
	 *
	 * @return array
	 */
	public function getUserCategoryWatchByUser($userId)
	{
		return $this->fetchAllKeyed('
			SELECT *
			FROM xf_resource_category_watch
			WHERE user_id = ?
		', 'resource_category_id', $userId);
	}

	/**
	 * Get a list of all users watching a category. Includes permissions for the category.
	 *
	 * @param integer $categoryId
	 * @param boolean $isUpdate
	 *
	 * @return array Format: [user_id] => info
	 */
	public function getUsersWatchingCategory(array $category, $isUpdate = false)
	{
		if ($isUpdate)
		{
			$notificationLimit = "AND category_watch.notify_on = 'update'";
		}
		else
		{
			$notificationLimit = "AND category_watch.notify_on IN ('resource', 'update')";
		}

		$breadcrumb = unserialize($category['category_breadcrumb']);
		$categoryIds = array_keys($breadcrumb);
		$categoryIds[] = $category['resource_category_id'];

		return $this->fetchAllKeyed('
			SELECT user.*,
				user_option.*,
				user_profile.*,
				category_watch.resource_category_id AS watch_category_id,
				category_watch.notify_on,
				category_watch.send_alert,
				category_watch.send_email,
				permission_combination.cache_value AS global_permission_cache,
				permission.cache_value AS category_permission_cache
			FROM xf_resource_category_watch AS category_watch
			INNER JOIN xf_user AS user ON
				(user.user_id = category_watch.user_id AND user.user_state = \'valid\' AND user.is_banned = 0)
			INNER JOIN xf_user_option AS user_option ON
				(user_option.user_id = user.user_id)
			INNER JOIN xf_user_profile AS user_profile ON
				(user_profile.user_id = user.user_id)
			INNER JOIN xf_permission_combination AS permission_combination ON
				(permission_combination.permission_combination_id = user.permission_combination_id)
			LEFT JOIN xf_permission_cache_content AS permission
				ON (permission.permission_combination_id = user.permission_combination_id
					AND permission.content_type = \'resource_category\'
					AND permission.content_id = ?)
			WHERE category_watch.resource_category_id IN (' . $this->_getDb()->quote($categoryIds) . ')
				' . $notificationLimit . '
				AND (category_watch.include_children <> 0 OR category_watch.resource_category_id = ?)
				AND (category_watch.send_alert <> 0 OR category_watch.send_email <> 0)
		', 'user_id', array($category['resource_category_id'], $category['resource_category_id']));
	}

	protected static $_preventDoubleNotify = array();
	
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
	public function sendNotificationToWatchUsers(array $update, array $resource, array $noAlerts = array(), array $noEmail = array())
	{
		if ($update['message_state'] != 'visible' || $resource['resource_state'] != 'visible')
		{
			return array();
		}

		$resourceModel = $this->_getResourceModel();

		/* @var $userModel XenForo_Model_User */
		$userModel = $this->getModelFromCache('XenForo_Model_User');

		if ($update['resource_update_id'] == $resource['description_update_id'])
		{
			$isUpdate = false;
			$actionType = 'resource';
		}
		else
		{
			$isUpdate = true;
			$actionType = 'update';
		}

		if (XenForo_Application::get('options')->emailWatchedThreadIncludeMessage)
		{
			$parseBbCode = true;
			$emailTemplate = 'watched_resource_category_' . $actionType . '_messagetext';
		}
		else
		{
			$parseBbCode = false;
			$emailTemplate = 'watched_resource_category_' . $actionType;
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

		$users = $this->getUsersWatchingCategory($category, $isUpdate);
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

			if ($user['send_email'] && !in_array($user['user_id'], $noEmail)
				&& $user['email'] && $user['user_state'] == 'valid'
			)
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

			if ($user['send_alert'] && !in_array($user['user_id'], $noAlerts))
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
	 * Sets the category watch state as requested. An empty state will delete any watch record.
	 *
	 * @param integer $userId
	 * @param integer $categoryId
	 * @param string|null $notifyOn If "delete", watch record is removed
	 * @param boolean|null $sendAlert
	 * @param boolean|null $sendEmail
	 * @param boolean|null $includeChildren
	 *
	 * @return boolean
	 */
	public function setCategoryWatchState($userId, $categoryId, $notifyOn = null, $sendAlert = null, $sendEmail = null, $includeChildren = null)
	{
		if (!$userId)
		{
			return false;
		}

		$categoryWatch = $this->getUserCategoryWatchByCategoryId($userId, $categoryId);

		if ($notifyOn === 'delete')
		{
			if ($categoryWatch)
			{
				$dw = XenForo_DataWriter::create('XenResource_DataWriter_CategoryWatch');
				$dw->setExistingData($categoryWatch, true);
				$dw->delete();
			}
			return true;
		}

		$dw = XenForo_DataWriter::create('XenResource_DataWriter_CategoryWatch');
		if ($categoryWatch)
		{
			$dw->setExistingData($categoryWatch, true);
		}
		else
		{
			$dw->set('user_id', $userId);
			$dw->set('resource_category_id', $categoryId);
		}
		if ($notifyOn !== null)
		{
			$dw->set('notify_on', $notifyOn);
		}
		if ($sendAlert !== null)
		{
			$dw->set('send_alert', $sendAlert ? 1 : 0);
		}
		if ($sendEmail !== null)
		{
			$dw->set('send_email', $sendEmail ? 1 : 0);
		}
		if ($includeChildren !== null)
		{
			$dw->set('include_children', $includeChildren ? 1 : 0);
		}
		$dw->save();
		return true;
	}

	public function setCategoryWatchStateForAll($userId, $state)
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
				return $db->update('xf_resource_category_watch',
					array('send_email' => 1),
					"user_id = " . $db->quote($userId)
				);

			case 'watch_no_email':
				return $db->update('xf_resource_category_watch',
					array('send_email' => 0),
					"user_id = " . $db->quote($userId)
				);

			case 'watch_alert':
				return $db->update('xf_resource_category_watch',
					array('send_alert' => 1),
					"user_id = " . $db->quote($userId)
				);

			case 'watch_no_alert':
				return $db->update('xf_resource_category_watch',
					array('send_alert' => 0),
					"user_id = " . $db->quote($userId)
				);

			case 'watch_include_children':
				return $db->update('xf_resource_category_watch',
					array('include_children' => 1),
					"user_id = " . $db->quote($userId)
				);

			case 'watch_no_include_children':
				return $db->update('xf_resource_category_watch',
					array('include_children' => 0),
					"user_id = " . $db->quote($userId)
				);

			case '':
				return $db->delete('xf_resource_category_watch', "user_id = " . $db->quote($userId));

			default:
				return false;
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