<?php

class EWRporta_Model_Attachment extends XFCP_EWRporta_Model_Attachment
{
	public function canViewAttachment(array $attachment, $tempHash = '', array $viewingUser = null)
	{
		if ($attachment['filename'] == 'slide.jpg') { return true; }

		return parent::canViewAttachment($attachment, $tempHash, $viewingUser);
	}
}