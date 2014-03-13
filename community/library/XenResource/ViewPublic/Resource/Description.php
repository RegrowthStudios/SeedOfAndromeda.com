<?php

class XenResource_ViewPublic_Resource_Description extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		XenForo_Application::set('view', $this);

		$bbCodeParser = XenForo_BbCode_Parser::create(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));
		$bbCodeOptions = array(
			'states' => array(
				'viewAttachments' => $this->_params['canViewImages']
			),
			'showSignature' => false
		);

		$this->_params['update']['messageHtml'] = XenForo_ViewPublic_Helper_Message::getBbCodeWrapper(
			$this->_params['update'], $bbCodeParser, $bbCodeOptions
		);

	}
}