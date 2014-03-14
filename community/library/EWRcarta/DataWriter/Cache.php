<?php

class EWRcarta_DataWriter_Cache extends XenForo_DataWriter
{
	protected $_existingDataErrorPhrase = 'requested_page_not_found';

	protected function _getFields()
	{
		return array(
			'EWRcarta_cache' => array(
				'page_id'		=> array('type' => self::TYPE_UINT, 'required' => true),
				'cache_date'	=> array('type' => self::TYPE_UINT, 'required' => true),
				'cache_content'	=> array('type' => self::TYPE_STRING, 'required' => false),
			)
		);
	}

	protected function _getExistingData($data)
	{
		if (!$pageID = $this->_getExistingPrimaryKey($data, 'page_id'))
		{
			return false;
		}

		return array('EWRcarta_cache' => $this->getModelFromCache('EWRcarta_Model_Cache')->getCacheByID($pageID));
	}

	protected function _getUpdateCondition($tableName)
	{
		return 'page_id = ' . $this->_db->quote($this->getExisting('page_id'));
	}

	protected function _preSave()
	{
		$this->set('cache_date', XenForo_Application::$time);
	}
}