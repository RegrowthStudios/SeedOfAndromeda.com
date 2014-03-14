<?php

class XenResource_WarningHandler_Rating extends XenForo_WarningHandler_Abstract
{
	protected function _canView(array $content, array $viewingUser)
	{
		return $this->_getRatingModel()->canViewRatingAndContainer(
			$content, $content, $content, $null, $viewingUser, $content['permissions']
		);
	}

	protected function _canWarn($userId, array $content, array $viewingUser)
	{
		return $this->_getRatingModel()->canWarnRating(
			$content, $content, $content, $null, $viewingUser, $content['permissions']
		);
	}

	protected function _canDeleteContent(array $content, array $viewingUser)
	{
		return $this->_getRatingModel()->canDeleteRating(
			$content, $content, $content, 'soft', $null, $viewingUser, $content['permissions']
		);
	}

	protected function _getContent(array $contentIds, array $viewingUser)
	{
		$ratingModel = $this->_getRatingModel();

		$ratings = $ratingModel->getRatingsByIds($contentIds, array(
			'join' => XenResource_Model_Rating::FETCH_RESOURCE | XenResource_Model_Rating::FETCH_CATEGORY,
			'permissionCombinationId' => $viewingUser['permission_combination_id']
		));
		return $ratingModel->unserializePermissionsInList($ratings, 'category_permission_cache');
	}

	public function getContentTitle(array $content)
	{
		return $content['title'];
	}

	public function getContentUrl(array $content, $canonical = false)
	{
		return XenForo_Link::buildPublicLink(
			($canonical ? 'canonical:' : '') . 'resources/reviews',
			$content,
			array('resource_update_id' => $content['resource_rating_id'])
		);
	}

	public function getContentTitleForDisplay($title)
	{
		// will be escaped in template
		return new XenForo_Phrase('resource_review_in_x', array('title' => $title), false);
	}

	protected function _warn(array $warning, array $content, $publicMessage, array $viewingUser)
	{
		$dw = XenForo_DataWriter::create('XenResource_DataWriter_Rating', XenForo_DataWriter::ERROR_SILENT);
		if ($dw->setExistingData($content))
		{
			$dw->set('warning_id', $warning['warning_id']);
			$dw->save();
		}
	}

	protected function _reverseWarning(array $warning, array $content)
	{
		if ($content)
		{
			$dw = XenForo_DataWriter::create('XenResource_DataWriter_Rating', XenForo_DataWriter::ERROR_SILENT);
			if ($dw->setExistingData($content))
			{
				$dw->set('warning_id', 0);
				$dw->save();
			}
		}
	}

	protected function _deleteContent(array $content, $reason, array $viewingUser)
	{
		$updateDw = XenForo_DataWriter::create('XenResource_DataWriter_Rating');
		$updateDw->setExistingData($content);
		$updateDw->set('rating_state', 'deleted');
		$updateDw->save();

		XenForo_Model_Log::logModeratorAction('resource_rating', $content, 'delete_soft');
	}

	public function canPubliclyDisplayWarning()
	{
		return false;
	}

	/**
	 * @return XenResource_Model_Rating
	 */
	protected function _getRatingModel()
	{
		return XenForo_Model::create('XenResource_Model_Rating');
	}
}