<?php

class EWRcarta_DataWriter_Templates extends XenForo_DataWriter
{
	protected $_existingDataErrorPhrase = 'requested_page_not_found';

	protected function _getFields()
	{
		return array(
			'EWRcarta_templates' => array(
				'template_name'		=> array('type' => self::TYPE_STRING, 'required' => true),
				'template_content'	=> array('type' => self::TYPE_STRING, 'required' => true),
			)
		);
	}

	protected function _getExistingData($data)
	{
		if (!$templateName = $this->_getExistingPrimaryKey($data, 'template_name'))
		{
			return false;
		}

		return array('EWRcarta_templates' => $this->getModelFromCache('EWRcarta_Model_Templates')->getTemplateBySlug($templateName));
	}

	protected function _getUpdateCondition($tableName)
	{
		return 'template_name = ' . $this->_db->quote($this->getExisting('template_name'));
	}

	protected function _preSave()
	{
		$template = $this->get('template_name');
		$template = strtolower(trim($template));
		$template = preg_replace('#[^-a-z0-9\s]#', '-', $template);
		$template = preg_replace('#^[-\s]+|[-\s]+$#', '', $template);
		$template = preg_replace('#[-\s]+#', '-', $template);

		$this->set('template_name', $template);
	}
}