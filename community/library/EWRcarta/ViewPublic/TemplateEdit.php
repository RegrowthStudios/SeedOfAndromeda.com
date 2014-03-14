<?php

class EWRcarta_ViewPublic_TemplateEdit extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		$this->_params['editorTemplate'] = XenForo_ViewPublic_Helper_Editor::getEditorTemplate(
			$this, 'template_content', $this->_params['template']['template_content'], array('disable' => true)
		);
	}
}