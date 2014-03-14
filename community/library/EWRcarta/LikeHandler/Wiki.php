<?php

class EWRcarta_LikeHandler_Wiki extends XenForo_LikeHandler_Abstract
{
	public function incrementLikeCounter($contentId, array $latestLikes, $adjustAmount = 1)
	{
		$dw = XenForo_DataWriter::create('EWRcarta_DataWriter_Pages');
		$dw->setExistingData($contentId);
		$dw->set('page_likes', $dw->get('page_likes') + $adjustAmount);
		$dw->set('page_like_users', $latestLikes);
		$dw->save();
	}

	public function getContentData(array $contentIds, array $viewingUser)
	{
		$pageModel = XenForo_Model::create('EWRcarta_Model_Pages');
		$pages = $pageModel->getPagesByIDs($contentIds);

		return $pages;
	}

	public function getListTemplateName()
	{
		return 'news_feed_item_wiki_like';
	}
}