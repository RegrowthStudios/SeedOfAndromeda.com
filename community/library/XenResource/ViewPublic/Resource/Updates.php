<?php

class XenResource_ViewPublic_Resource_Updates extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		$bbCodeParser = XenForo_BbCode_Parser::create(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));
		$bbCodeOptions = array(
			'states' => array(
				'viewAttachments' => $this->_params['canViewImages']
			)
		);

		XenForo_ViewPublic_Helper_Message::bbCodeWrapMessages($this->_params['updates'], $bbCodeParser, $bbCodeOptions);
	}
}