<?php

class XenResource_ModerationQueueHandler_Resource extends XenForo_ModerationQueueHandler_Abstract
{
	/**
	 * Gets visible moderation queue entries for specified user.
	 *
	 * @see XenForo_ModerationQueueHandler_Abstract::getVisibleModerationQueueEntriesForUser()
	 */
	public function getVisibleModerationQueueEntriesForUser(array $contentIds, array $viewingUser)
	{
		/* @var $resourceModel XenResource_Model_Resource */
		$resourceModel = XenForo_Model::create('XenResource_Model_Resource');
		$resources = $resourceModel->getResourcesByIds($contentIds, array(
			'join' => XenResource_Model_Resource::FETCH_DESCRIPTION
		));

		$categories = XenForo_Model::create('XenResource_Model_Category')->getAllCategories(array(
			'permissionCombinationId' => $viewingUser['permission_combination_id']
		));

		$output = array();
		foreach ($resources AS $resource)
		{
			if (!isset($categories[$resource['resource_category_id']]))
			{
				continue;
			}

			$category = $categories[$resource['resource_category_id']];
			$categoryPermissions = XenForo_Permission::unserializePermissions($category['category_permission_cache']);

			$canManage = true;
			if (!$resourceModel->canViewResourceAndContainer($resource, $category, $null, $viewingUser, $categoryPermissions))
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
				$output[$resource['resource_id']] = array(
					'message' => $resource['description'],
					'user' => array(
						'user_id' => $resource['user_id'],
						'username' => $resource['username']
					),
					'title' => $resource['title'],
					'link' => XenForo_Link::buildPublicLink('resources', $resource),
					'contentTypeTitle' => new XenForo_Phrase('resource'),
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
		$dw = XenForo_DataWriter::create('XenResource_DataWriter_Resource', XenForo_DataWriter::ERROR_SILENT);
		$dw->setExistingData($contentId);
		$dw->set('resource_state', 'visible');
		$dw->set('title', $title);

		if ($dw->save())
		{
			$updateDw = XenForo_DataWriter::create('XenResource_DataWriter_Update', XenForo_DataWriter::ERROR_SILENT);
			if ($updateDw->setExistingData($dw->get('description_update_id')))
			{
				$updateDw->set('message', $message);
				$updateDw->save();
			}

			XenForo_Model_Log::logModeratorAction('resource', $dw->getMergedData(), 'approve');

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
		$dw = XenForo_DataWriter::create('XenResource_DataWriter_Resource', XenForo_DataWriter::ERROR_SILENT);
		$dw->setExistingData($contentId);
		$dw->set('resource_state', 'deleted');

		if ($dw->save())
		{
			XenForo_Model_Log::logModeratorAction('resource', $dw->getMergedData(), 'delete_soft', array('reason' => ''));
			return true;
		}
		else
		{
			return false;
		}
	}
}