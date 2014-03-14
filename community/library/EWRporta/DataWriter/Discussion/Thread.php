<?php

class EWRporta_DataWriter_Discussion_Thread extends XFCP_EWRporta_DataWriter_Discussion_Thread
{
	protected function _discussionPostSave()
	{
		$response = parent::_discussionPostSave();

		if ($this->get('discussion_state') == 'deleted')
		{
			$this->deletePromotion();
		}
		else
		{
			$this->cachePromotions();
		}

		return $response;
	}

	protected function _discussionPostDelete()
	{
		$response = parent::_discussionPostDelete();

		$this->deletePromotion();

		return $response;
	}

	protected function cachePromotions()
	{
		$forumId = $this->get('node_id');
		$promoteForums = $this->getModelFromCache('EWRporta_Model_Promotes')->getPromoteForums();

		if (in_array($forumId, $promoteForums))
		{
			$this->getModelFromCache('EWRporta_Model_Caches')->emptyBlockCache(array('block_id'=>'RecentFeatures'));
			$this->getModelFromCache('EWRporta_Model_Caches')->emptyBlockCache(array('block_id'=>'RecentNews'));
		}

		return true;
	}

	protected function deletePromotion()
	{
		$threadId = $this->get('thread_id');
		$forumId = $this->get('node_id');
		$promoteForums = $this->getModelFromCache('EWRporta_Model_Promotes')->getPromoteForums();

		if ($promote = $this->getModelFromCache('EWRporta_Model_Promotes')->getPromoteByThreadId($threadId))
		{
			$promote['delete'] = true;
			$this->getModelFromCache('EWRporta_Model_Promotes')->updatePromotion($promote);
			$clear = true;
		}
		else if (in_array($forumId, $promoteForums))
		{
			$clear = true;
		}

		if (!empty($clear))
		{
			$this->getModelFromCache('EWRporta_Model_Caches')->emptyBlockCache(array('block_id'=>'RecentFeatures'));
			$this->getModelFromCache('EWRporta_Model_Caches')->emptyBlockCache(array('block_id'=>'RecentNews'));
		}

		return true;
	}
}