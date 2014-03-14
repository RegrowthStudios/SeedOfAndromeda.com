<?php

class XenResource_ModerationQueueHandler_Update extends XenForo_ModerationQueueHandler_Abstract
{
	/**
	 * Gets visible moderation queue entries for specified user.
	 *
	 * @see XenForo_ModerationQueueHandler_Abstract::getVisibleModerationQueueEntriesForUser()
	 */
	public function getVisibleModerationQueueEntriesForUser(array $contentIds, array $viewingUser)
	{
		/* @var $updateModel XenResource_Model_Update */
		$updateModel = XenForo_Model::create('XenResource_Model_Update');
		$updates = $updateModel->getUpdatesByIds($contentIds);

		$categories = XenForo_Model::create('XenResource_Model_Category')->getAllCategories(array(
			'permissionCombinationId' => $viewingUser['permission_combination_id']
		));

		$resourceIds = array();
		foreach ($updates AS $update)
		{
			$resourceIds[] = $update['resource_id'];
		}
		$resources = XenForo_Model::create('XenResource_Model_Resource')->getResourcesByIds($resourceIds);

		$output = array();
		foreach ($updates AS $update)
		{
			if (!isset($resources[$update['resource_id']]))
			{
				continue;
			}

			$resource = $resources[$update['resource_id']];

			if (!isset($categories[$resource['resource_category_id']]))
			{
				continue;
			}

			$category = $categories[$resource['resource_category_id']];
			$categoryPermissions = XenForo_Permission::unserializePermissions($category['category_permission_cache']);

			$canManage = true;
			if (!$updateModel->canViewUpdateAndContainer($update, $resource, $category, $null, $viewingUser, $categoryPermissions))
			{
				$canManage = false;
			}
			else if (!XenForo_Permission::hasContentPermission($categoryPermissions, 'editAny')
				|| !XenForo_Permission::hasContentPermission($categoryPermissions, 'deleteAny')
				|| !XenForo_Permission::hasContentPermission($categoryPermissions, 'approveUnapprove')
			)
			{
				$canManage = false;
			}

			if ($canManage)
			{
				$output[$update['resource_update_id']] = array(
					'message' => $update['message'],
					'user' => array(
						'user_id' => $resource['user_id'],
						'username' => $resource['username']
					),
					'title' => $update['title'],
					'link' => XenForo_Link::buildPublicLink('resources', $resource, array('update' => $update['resource_update_id'])),
					'contentTypeTitle' => new XenForo_Phrase('resource_update'),
					'titleEdit' => true
				);
			}
		}

		return $output;
	}

	/**
	 * Approves the specified moderation queue entry.
	 *
	 * @see XenForo_ModerationQueueHandler_Abstract::approveModerationQueueEntry()
	 */
	public function approveModerationQueueEntry($contentId, $message, $title)
	{
		$dw = XenForo_DataWriter::create('XenResource_DataWriter_Update', XenForo_DataWriter::ERROR_SILENT);
		$dw->setExistingData($contentId);
		$dw->set('message_state', 'visible');
		$dw->set('title', $title);
		$dw->set('message', $message);

		if ($dw->save())
		{
			XenForo_Model_Log::logModeratorAction('resource_update', $dw->getMergedData(), 'approve');
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Deletes the specified moderation queue entry.
	 *
	 * @see XenForo_ModerationQueueHandler_Abstract::deleteModerationQueueEntry()
	 */
	public function deleteModerationQueueEntry($contentId)
	{
		$dw = XenForo_DataWriter::create('XenResource_DataWriter_Update', XenForo_DataWriter::ERROR_SILENT);
		$dw->setExistingData($contentId);
		$dw->set('message_state', 'deleted');

		if ($dw->save())
		{
			XenForo_Model_Log::logModeratorAction('resource_update', $dw->getMergedData(), 'delete_soft', array('reason' => ''));
			return true;
		}
		else
		{
			return false;
		}
	}
}