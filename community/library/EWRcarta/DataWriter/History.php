<?php

class EWRcarta_DataWriter_History extends XenForo_DataWriter
{
	protected $_existingDataErrorPhrase = 'requested_page_not_found';

	protected function _getFields()
	{
		return array(
			'EWRcarta_history' => array(
				'page_id'			=> array('type' => self::TYPE_UINT, 'required' => true),
				'user_id'			=> array('type' => self::TYPE_UINT, 'required' => true),
				'username'			=> array('type' => self::TYPE_STRING, 'required' => true),
				'history_id'		=> array('type' => self::TYPE_UINT, 'autoIncrement' => true),
				'history_date'		=> array('type' => self::TYPE_UINT, 'required' => true),
				'history_type'		=> array('type' => self::TYPE_STRING, 'required' => true,
					'allowedValues' => array('bbcode', 'html', 'phpfile')
				),
				'history_content'	=> array('type' => self::TYPE_STRING, 'required' => true),
				'history_current'	=> array('type' => self::TYPE_UINT, 'required' => true),
				'history_revert'	=> array('type' => self::TYPE_UINT, 'required' => true),
				'history_ip'		=> array('type' => self::TYPE_UINT, 'required' => false),
			)
		);
	}

	protected function _getExistingData($data)
	{
		if (!$historyID = $this->_getExistingPrimaryKey($data, 'history_id'))
		{
			return false;
		}

		return array('EWRcarta_history' => $this->getModelFromCache('EWRcarta_Model_History')->getHistoryByID($historyID));
	}

	protected function _getUpdateCondition($tableName)
	{
		return 'history_id = ' . $this->_db->quote($this->getExisting('history_id'));
	}

	protected function _preSave()
	{
		$visitor = XenForo_Visitor::getInstance();
		$this->set('user_id', $visitor['user_id']);
		$this->set('username', ($visitor['user_id'] ? $visitor['username'] : $_SERVER['REMOTE_ADDR']));
	}
}