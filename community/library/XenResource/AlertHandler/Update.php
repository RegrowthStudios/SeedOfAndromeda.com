<?php

class XenResource_AlertHandler_Update extends XenForo_AlertHandler_Abstract
{
	protected $_updateModel;

	/**
	 * Fetches the content required by alerts.
	 *
	 * @param array $contentIds
	 * @param XenForo_Model_Alert $model Alert model invoking this
	 * @param integer $userId User ID the alerts are for
	 * @param array $viewingUser Information about the viewing user (keys: user_id, permission_combination_id, permissions)
	 *
	 * @return array
	 */
	public function getContentByIds(array $contentIds, $model, $userId, array $viewingUser)
	{
		$updateModel = $this->_getUpdateModel();

		$updates = $updateModel->getUpdatesByIds($contentIds, array(
			'join' => XenResource_Model_Update::FETCH_RESOURCE | XenResource_Model_Update::FETCH_CATEGORY,
			'permissionCombinationId' => $viewingUser['permission_combination_id']
		));
		foreach ($updates AS &$update)
		{
			$update['resource_title'] = XenForo_Helper_String::censorString($update['resource_title']);
			$update['title'] = XenForo_Helper_String::censorString($update['title']);
		}

		return $updates;
	}

	/**
	* Determines if the update is viewable.
	* @see XenForo_AlertHandler_Abstract::canViewAlert()
	*/
	public function canViewAlert(array $alert, $content, array $viewingUser)
	{
		$updateModel = $this->_getUpdateModel();

		$categoryPermissions = XenForo_Permission::unserializePermissions($content['category_permission_cache']);

		return $updateModel->canViewUpdateAndContainer(
			$content, $content, $content, $null, $viewingUser, $categoryPermissions
		);
	}

	/**
	 * @return XenResource_Model_Update
	 */
	protected function _getUpdateModel()
	{
		if (!$this->_updateModel)
		{
			$this->_updateModel = XenForo_Model::create('XenResource_Model_Update');
		}

		return $this->_updateModel;
	}
}