<?php

class XenResource_ViewPublic_Update_Add extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		$this->_params['editorTemplate'] = XenForo_ViewPublic_Helper_Editor::getEditorTemplate(
			$this, 'message', $this->_params['message'],
			array(
				'extraClass' => 'NoAutoComplete',
				'autoSaveUrl' => XenForo_Link::buildPublicLink('resources/save-draft', $this->_params['resource'])
			)
		);
	}
}