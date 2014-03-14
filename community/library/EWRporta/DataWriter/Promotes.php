<?php

class EWRporta_DataWriter_Promotes extends XenForo_DataWriter
{
	protected $_existingDataErrorPhrase = 'requested_page_not_found';

	protected function _getFields()
	{
		return array(
			'EWRporta_promotes' => array(
				'thread_id'		=> array('type' => self::TYPE_UINT, 'required' => true),
				'promote_date'	=> array('type' => self::TYPE_UINT, 'required' => true),
				'promote_icon'	=> array('type' => self::TYPE_STRING, 'required' => true, 'default' => 'default',
					'allowedValues' => array('default', 'avatar', 'attach', 'image', 'medio', 'disabled'),
				),
				'promote_data'	=> array('type' => self::TYPE_STRING, 'required' => true, 'default' => '0'),
			)
		);
	}

	protected function _getExistingData($data)
	{
		if (!$threadID = $this->_getExistingPrimaryKey($data, 'thread_id'))
		{
			return false;
		}

		return array('EWRporta_promotes' => $this->getModelFromCache('EWRporta_Model_Promotes')->getPromoteByThreadId($threadID));
	}

	protected function _getUpdateCondition($tableName)
	{
		return 'thread_id = ' . $this->_db->quote($this->getExisting('thread_id'));
	}
}