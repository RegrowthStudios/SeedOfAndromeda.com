<?php

class EWRporta_DataWriter_Caches extends XenForo_DataWriter
{
	protected $_existingDataErrorPhrase = 'requested_page_not_found';

	protected function _getFields()
	{
		return array(
			'EWRporta_caches' => array(
				'block_id'		=> array('type' => self::TYPE_STRING,	'required' => true),
				'date'			=> array('type' => self::TYPE_UINT,		'required' => true),
				'results'		=> array('type' => self::TYPE_STRING,	'required' => true),
			)
		);
	}

	protected function _getExistingData($data)
	{
		if (!$blockId = $this->_getExistingPrimaryKey($data, 'block_id'))
		{
			return false;
		}

		return array('EWRporta_caches' => $this->getModelFromCache('EWRporta_Model_Caches')->getCacheByBlockId($blockId));
	}

	protected function _getUpdateCondition($tableName)
	{
		return 'block_id = ' . $this->_db->quote($this->getExisting('block_id'));
	}

	protected function _preSave()
	{
		$this->set('date', XenForo_Application::$time);
	}
}