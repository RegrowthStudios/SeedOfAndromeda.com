<?php

class XenResource_ViewPublic_Resource_Add extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		$message = (isset($this->_params['resource']['description']) ? $this->_params['resource']['description'] : '');

		$this->_params['editorTemplate'] = XenForo_ViewPublic_Helper_Editor::getEditorTemplate(
			$this, 'message', $message,
			array(
				'extraClass' => 'NoAutoComplete',
				'autoSaveUrl' =>
					empty($this->_params['resource']['resource_id'])
					? XenForo_Link::buildPublicLink('resources/categories/save-draft', $this->_params['category'])
					: ''
			)
		);

		foreach ($this->_params['customFields'] AS &$fields)
		{
			foreach ($fields AS &$field)
			{
				if ($field['field_type'] == 'bbcode')
				{
					$field['editorTemplateHtml'] = XenForo_ViewPublic_Helper_Editor::getEditorTemplate(
						$this, 'custom_fields[' . $field['field_id'] . ']',
						isset($field['field_value']) ? $field['field_value'] : '',
						array(
							'height' => '100px',
							'extraClass' => 'NoAttachment NoAutoComplete'
						)
					);
				}
			}
		}
	}
}