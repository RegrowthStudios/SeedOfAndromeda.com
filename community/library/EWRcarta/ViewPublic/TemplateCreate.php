<?php

class EWRcarta_ViewPublic_TemplateCreate extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		$this->_params['editorTemplate'] = XenForo_ViewPublic_Helper_Editor::getEditorTemplate(
			$this, 'template_content', '', array('disable' => true)
		);
	}
}