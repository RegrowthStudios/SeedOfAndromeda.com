<?php

/**
 * Handler for the specific resource-update-related like aspects.
 *
 * @package XenForo_Like
 */
class XenResource_LikeHandler_Update extends XenForo_LikeHandler_Abstract
{
	/**
	 * Increments the like counter.
	 * @see XenForo_LikeHandler_Abstract::incrementLikeCounter()
	 */
	public function incrementLikeCounter($contentId, array $latestLikes, $adjustAmount = 1)
	{
		$dw = XenForo_DataWriter::create('XenResource_DataWriter_Update');
		$dw->setExistingData($contentId);
		$dw->set('likes', $dw->get('likes') + $adjustAmount);
		$dw->set('like_users', $latestLikes);
		$dw->save();
	}

	/**
	 * Gets content data (if viewable).
	 * @see XenForo_LikeHandler_Abstract::getContentData()
	 */
	public function getContentData(array $contentIds, array $viewingUser)
	{
		/* @var $updateModel XenResource_Model_Update */
		$updateModel = XenForo_Model::create('XenResource_Model_Update');

		$updates = $updateModel->getUpdatesByIds($contentIds, array(
			'join' => XenResource_Model_Update::FETCH_RESOURCE | XenResource_Model_Update::FETCH_CATEGORY,
			'permissionCombinationId' => $viewingUser['permission_combination_id']
		));
		foreach ($updates AS $updateId => &$update)
		{
			$categoryPermissions = XenForo_Permission::unserializePermissions($update['category_permission_cache']);

			if (!$updateModel->canViewUpdate($update, $update, $update, $null, $viewingUser, $categoryPermissions))
			{
				unset($updates[$updateId]);
			}
			else
			{
				$update = $updateModel->prepareUpdate($update, $update, $update, $viewingUser);
			}
		}

		return $updates;
	}

	/**
	 * @see XenForo_LikeHandler_Abstract::batchUpdateContentUser()
	 */
	public function batchUpdateContentUser($oldUserId, $newUserId, $oldUsername, $newUsername)
	{
		$updateModel = XenForo_Model::create('XenResource_Model_Update');
		$updateModel->batchUpdateLikeUser($oldUserId, $newUserId, $oldUsername, $newUsername);
	}

	/**
	 * Gets the name of the template that will be used when listing likes of this type.
	 *
	 * @return string news_feed_item_post_like
	 */
	public function getListTemplateName()
	{
		return 'news_feed_item_resource_update_like';
	}
}