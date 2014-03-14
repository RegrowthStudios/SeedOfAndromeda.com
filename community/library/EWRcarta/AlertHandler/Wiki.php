<?php

class EWRcarta_AlertHandler_Wiki extends XenForo_AlertHandler_Abstract
{
	public function getContentByIds(array $contentIds, $model, $userId, array $viewingUser)
	{
		return $model->getModelFromCache('EWRcarta_Model_Pages')->getPagesByIDs($contentIds);
	}
}