<?php

/**
 * @extends XenForo_ControllerPublic_Watched
 */
class XenResource_Listener_Proxy_ControllerWatched extends XFCP_XenResource_Listener_Proxy_ControllerWatched
{
	protected function _takeEmailAction(array $user, $action, $type, $id)
	{
		if ($type == '' || $type == 'resource')
		{
			if ($id)
			{
				$this->_getResourceWatchModel()->setResourceWatchState($user['user_id'], $id, $action);
			}
			else
			{
				$this->_getResourceWatchModel()->setResourceWatchStateForAll($user['user_id'], $action);
			}
		}

		if ($type == '' || $type == 'resource_category')
		{
			if ($id)
			{
				$this->_getCategoryWatchModel()->setCategoryWatchState(
					$user['user_id'], $id,
					$action == '' ? 'delete' : null,
					null,
					$action == 'watch_email' ? true : false
				);
			}
			else
			{
				$this->_getCategoryWatchModel()->setCategoryWatchStateForAll($user['user_id'], $action);
			}
		}

		parent::_takeEmailAction($user, $action, $type, $id);
	}

	protected function _getEmailActionConfirmPhrase(array $user, $action, $type, $id)
	{
		if ($type == 'resource')
		{
			if ($id)
			{
				return new XenForo_Phrase('you_sure_you_want_to_update_notification_settings_for_one_resource');
			}
			else
			{
				return new XenForo_Phrase('you_sure_you_want_to_update_notification_settings_for_all_resources');
			}
		}

		if ($type == 'resource_category')
		{
			if ($id)
			{
				return new XenForo_Phrase('you_sure_you_want_to_update_notification_settings_for_one_resource_category');
			}
			else
			{
				return new XenForo_Phrase('you_sure_you_want_to_update_notification_settings_for_all_resource_cats');
			}
		}

		return parent::_getEmailActionConfirmPhrase($user, $action, $type, $id);
	}

	/**
	 * @return XenResource_Model_ResourceWatch
	 */
	protected function _getResourceWatchModel()
	{
		return $this->getModelFromCache('XenResource_Model_ResourceWatch');
	}

	/**
	 * @return XenResource_Model_CategoryWatch
	 */
	protected function _getCategoryWatchModel()
	{
		return $this->getModelFromCache('XenResource_Model_CategoryWatch');
	}
}