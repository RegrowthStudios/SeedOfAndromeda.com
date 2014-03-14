<?php

class XenResource_WarningHandler_Update extends XenForo_WarningHandler_Abstract
{
	protected function _canView(array $content, array $viewingUser)
	{
		return $this->_getUpdateModel()->canViewUpdateAndContainer(
			$content, $content, $content, $null, $viewingUser, $content['permissions']
		);
	}

	protected function _canWarn($userId, array $content, array $viewingUser)
	{
		return $this->_getUpdateModel()->canWarnUpdate(
			$content, $content, $content, $null, $viewingUser, $content['permissions']
		);
	}

	protected function _canDeleteContent(array $content, array $viewingUser)
	{
		return $this->_getUpdateModel()->canDeleteUpdate(
			$content, $content, $content, 'soft', $null, $viewingUser, $content['permissions']
		);
	}

	protected function _getContent(array $contentIds, array $viewingUser)
	{
		$updateModel = $this->_getUpdateModel();

		$updates = $updateModel->getUpdatesByIds($contentIds, array(
			'join' => XenResource_Model_Update::FETCH_RESOURCE | XenResource_Model_Update::FETCH_CATEGORY,
			'permissionCombinationId' => $viewingUser['permission_combination_id']
		));
		return $updateModel->unserializePermissionsInList($updates, 'category_permission_cache');
	}

	public function getContentTitle(array $content)
	{
		return $content['title'];
	}

	public function getContentUrl(array $content, $canonical = false)
	{
		return XenForo_Link::buildPublicLink(
			($canonical ? 'canonical:' : '') . 'resources/update',
			$content,
			array('resource_update_id' => $content['resource_update_id'])
		);
	}

	public function getContentTitleForDisplay($title)
	{
		// will be escaped in template
		return new XenForo_Phrase('resource_update_in_x', array('title' => $title), false);
	}

	protected function _warn(array $warning, array $content, $publicMessage, array $viewingUser)
	{
		$dw = XenForo_DataWriter::create('XenResource_DataWriter_Update', XenForo_DataWriter::ERROR_SILENT);
		if ($dw->setExistingData($content))
		{
			$dw->set('warning_id', $warning['warning_id']);
			$dw->set('warning_message', $publicMessage);
			$dw->save();
		}
	}

	protected function _reverseWarning(array $warning, array $content)
	{
		if ($content)
		{
			$dw = XenForo_DataWriter::create('XenResource_DataWriter_Update', XenForo_DataWriter::ERROR_SILENT);
			if ($dw->setExistingData($content))
			{
				$dw->set('warning_id', 0);
				$dw->set('warning_message', '');
				$dw->save();
			}
		}
	}

	protected function _deleteContent(array $content, $reason, array $viewingUser)
	{
		$updateDw = XenForo_DataWriter::create('XenResource_DataWriter_Update');
		$updateDw->setExistingData($content);
		$updateDw->setExtraData(XenResource_DataWriter_Update::DATA_DELETE_REASON, $reason);
		$updateDw->set('message_state', 'deleted');
		$updateDw->save();

		XenForo_Model_Log::logModeratorAction('resource_update', $content, 'delete_soft', array('reason' => $reason));
	}

	public function canPubliclyDisplayWarning()
	{
		return true;
	}

	/**
	 * @return XenResource_Model_Update
	 */
	protected function _getUpdateModel()
	{
		return XenForo_Model::create('XenResource_Model_Update');
	}
}