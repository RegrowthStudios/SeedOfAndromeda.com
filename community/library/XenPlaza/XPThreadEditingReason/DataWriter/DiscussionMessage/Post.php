<?php

/**
* Data writer for posts.
*
* @package XenForo_Discussion
*/
class XenPlaza_XPThreadEditingReason_DataWriter_DiscussionMessage_Post extends XFCP_XenPlaza_XPThreadEditingReason_DataWriter_DiscussionMessage_Post
{
	protected function _getFields()
	{	
		$result = parent::_getFields();
		$structure = $this->_messageDefinition->getMessageStructure();
		if($result[$structure['table']]){
			$result[$structure['table']]['XP_edit_reason'] = array('type' => self::TYPE_STRING, 'maxLength' => 120, 'default' => '');	
			$result[$structure['table']]['XP_edit_date'] = array('type' => self::TYPE_UINT,   'required' => true, 'default' => XenForo_Application::$time);	
			$result[$structure['table']]['XP_editor'] = array('type' => self::TYPE_UINT, 'default' => '0' );	
		}
		return $result;
	}
}