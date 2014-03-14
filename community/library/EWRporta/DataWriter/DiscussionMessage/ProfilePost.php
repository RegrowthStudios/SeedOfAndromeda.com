<?php

class EWRporta_DataWriter_DiscussionMessage_ProfilePost extends XFCP_EWRporta_DataWriter_DiscussionMessage_ProfilePost
{
	protected function _messagePostSave()
	{
		$response = parent::_messagePostSave();

		$this->cacheStatusUpdates();

		return $response;
	}

	protected function _messagePostDelete()
	{
		$response = parent::_messagePostDelete();

		$this->cacheStatusUpdates();

		return $response;
	}

	protected function cacheStatusUpdates()
	{
		$this->getModelFromCache('EWRporta_Model_Caches')->emptyBlockCache(array('block_id'=>'StatusUpdates'));

		return true;
	}
}