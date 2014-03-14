<?php

class EWRcarta_NewsFeedHandler_Wiki extends XenForo_NewsFeedHandler_Abstract
{
	public function getContentByIds(array $contentIds, $model, array $viewingUser)
	{
		return $model->getModelFromCache('EWRcarta_Model_Pages')->getPagesByIDs($contentIds);
	}
}