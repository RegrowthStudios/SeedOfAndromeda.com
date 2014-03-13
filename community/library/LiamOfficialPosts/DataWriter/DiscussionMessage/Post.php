<?php

class LiamOfficialPosts_DataWriter_DiscussionMessage_Post extends XFCP_LiamOfficialPosts_DataWriter_DiscussionMessage_Post
{

	protected function _getFields()
	{
		$fields = parent::_getFields();
		
		$fields['xf_post']['official_post'] = array(
			'type' => XenForo_DataWriter::TYPE_BOOLEAN
		);
		
		return $fields;
	}

	protected function _messagePreSave()
	{
		$parent = parent::_messagePreSave();
		
		if (XenForo_Application::isRegistered('liam_officialpost'))
		{
			$officialPost = XenForo_Application::get('liam_officialpost');
			
			$this->set('official_post', $officialPost);
		}
		
		return $parent;
	}

}