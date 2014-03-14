<?php

class EWRcarta_AttachmentHandler_Wiki extends XenForo_AttachmentHandler_Abstract
{
	protected $_contentRoute = 'wiki';
	protected $_contentTypePhraseKey = 'wiki';
	protected $_contentIdKey = 'page_id';

	protected function _canUploadAndManageAttachments(array $contentData, array $viewingUser)
	{
		return true;
	}

	public function _canViewAttachment(array $attachment, array $viewingUser)
	{
		return true;
	}

	public function attachmentPostDelete(array $attachment, Zend_Db_Adapter_Abstract $db)
	{
		return true;
	}

	public function getAttachmentCountLimit()
	{
		return true;
	}
}