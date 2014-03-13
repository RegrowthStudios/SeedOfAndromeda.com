<?php

class XenResource_ViewPublic_Update_Edit extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		$this->_params['editorTemplate'] = XenForo_ViewPublic_Helper_Editor::getEditorTemplate(
			$this, 'message',
			$this->_params['update']['message'],
			array(
				'extraClass' => 'NoAutoComplete',
				'editorId' => 'message' . $this->_params['update']['resource_update_id']
			)
		);
	}
}