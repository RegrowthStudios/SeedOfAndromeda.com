<?php

class EWRporta_DataWriter_Layouts extends XenForo_DataWriter
{
	protected $_existingDataErrorPhrase = 'requested_page_not_found';

	protected function _getFields()
	{
		return array(
			'EWRporta_layouts' => array(
				'layout_id'		=> array('type' => self::TYPE_STRING,	'required' => true, 'default' => ''),
				'blocks'		=> array('type' => self::TYPE_STRING,	'required' => true, 'default' => ''),
			)
		);
	}

	protected function _getExistingData($data)
	{
		if (!$layoutId = $this->_getExistingPrimaryKey($data, 'layout_id'))
		{
			return false;
		}

		return array('EWRporta_layouts' => $this->getModelFromCache('EWRporta_Model_Layouts')->getLayoutById($layoutId));
	}

	protected function _getUpdateCondition($tableName)
	{
		return 'layout_id = ' . $this->_db->quote($this->getExisting('layout_id'));
	}
}