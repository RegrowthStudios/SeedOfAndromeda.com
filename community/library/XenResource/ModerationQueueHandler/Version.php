<?php

class XenResource_ModerationQueueHandler_Version extends XenForo_ModerationQueueHandler_Abstract
{
	/**
	 * Gets visible moderation queue entries for specified user.
	 *
	 * @see XenForo_ModerationQueueHandler_Abstract::getVisibleModerationQueueEntriesForUser()
	 */
	public function getVisibleModerationQueueEntriesForUser(array $contentIds, array $viewingUser)
	{
		/* @var $versionModel XenResource_Model_Version */
		$versionModel = XenForo_Model::create('XenResource_Model_Version');
		$versions = $versionModel->getVersionsByIds($contentIds);

		$categories = XenForo_Model::create('XenResource_Model_Category')->getAllCategories(array(
			'permissionCombinationId' => $viewingUser['permission_combination_id']
		));

		/* @var $resourceModel XenResource_Model_Resource */
		$resourceModel = XenForo_Model::create('XenResource_Model_Resource');

		$resourceIds = array();
		foreach ($versions AS $version)
		{
			$resourceIds[] = $version['resource_id'];
		}
		$resources = $resourceModel->getResourcesByIds($resourceIds);

		$output = array();
		foreach ($versions AS $version)
		{
			if (!isset($resources[$version['resource_id']]))
			{
				continue;
			}

			$resource = $resources[$version['resource_id']];

			if (!isset($categories[$resource['resource_category_id']]))
			{
				continue;
			}

			$category = $categories[$resource['resource_category_id']];
			$categoryPermissions = XenForo_Permission::unserializePermissions($category['category_permission_cache']);

			$canManage = true;
			if (!$resourceModel->canViewResourceAndContainer($resource, $category, $null, $viewingUser, $categoryPermissions)
				|| !$versionModel->canDownloadVersion($version, $resource, $category, $null, $viewingUser, $categoryPermissions)
			)
			{
				$canManage = false;
			}
			else if (!XenForo_Permission::hasContentPermission($categoryPermissions, 'deleteAny')
				|| !XenForo_Permission::hasContentPermission($categoryPermissions, 'approveUnapprove')
			)
			{
				$canManage = false;
			}

			if ($canManage)
			{
				$output[$version['resource_version_id']] = array(
					'message' => $version['version_string'],
					'user' => array(
						'user_id' => $resource['user_id'],
						'username' => $resource['username']
					),
					'title' => $resource['title'] . ' ' . $version['version_string'],
					'link' => XenForo_Link::buildPublicLink('resources/history', $resource),
					'contentTypeTitle' => new XenForo_Phrase('resource_version'),
					'titleEdit' => false
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
		$dw = XenForo_DataWriter::create('XenResource_DataWriter_Version', XenForo_DataWriter::ERROR_SILENT);
		$dw->setExistingData($contentId);
		$dw->set('version_state', 'visible');
		$dw->set('version_string', $message);

		if ($dw->save())
		{
			XenForo_Model_Log::logModeratorAction('resource_version', $dw->getMergedData(), 'approve');
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
		$dw = XenForo_DataWriter::create('XenResource_DataWriter_Version', XenForo_DataWriter::ERROR_SILENT);
		$dw->setExistingData($contentId);
		$dw->set('version_state', 'deleted');

		if ($dw->save())
		{
			XenForo_Model_Log::logModeratorAction('resource_version', $dw->getMergedData(), 'delete_soft', array('reason' => ''));
			return true;
		}
		else
		{
			return false;
		}
	}
}