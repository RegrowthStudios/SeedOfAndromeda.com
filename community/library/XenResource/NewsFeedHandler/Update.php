<?php

/**
 * News feed handler for user profile changes
 *
 * @author kier
 *
 */
class XenResource_NewsFeedHandler_Update extends XenForo_NewsFeedHandler_Abstract
{
	protected $_updateModel;

	/**
	 * Just returns a value for each requested ID
	 * but does no actual DB work
	 *
	 * @param array $contentIds
	 * @param XenForo_Model_NewsFeed $model
	 * @param array $viewingUser Information about the viewing user (keys: user_id, permission_combination_id, permissions)
	 *
	 * @return array
	 */
	public function getContentByIds(array $contentIds, $model, array $viewingUser)
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
	 * Determines if the given news feed item is viewable.
	 *
	 * @param array $item
	 * @param mixed $content
	 * @param array $viewingUser
	 *
	 * @return boolean
	 */
	public function canViewNewsFeedItem(array $item, $content, array $viewingUser)
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