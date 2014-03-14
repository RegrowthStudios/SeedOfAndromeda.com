<?php

class EWRporta_DataWriter_Catlinks extends XenForo_DataWriter
{
	protected $_existingDataErrorPhrase = 'requested_catlink_not_found';

	protected function _getFields()
	{
		return array(
			'EWRporta_catlinks' => array(
				'category_id'	=> array('type' => self::TYPE_UINT, 'required' => true),
				'thread_id'		=> array('type' => self::TYPE_UINT, 'required' => true),
				'catlink_id'	=> array('type' => self::TYPE_UINT, 'autoIncrement' => true),
			)
		);
	}

	protected function _getExistingData($data)
	{
		if (!$linkID = $this->_getExistingPrimaryKey($data, 'catlink_id'))
		{
			return false;
		}

		return array('EWRporta_catlinks' => $this->getModelFromCache('EWRporta_Model_Categories')->getCategoryLinkByID($linkID));
	}

	protected function _getUpdateCondition($tableName)
	{
		return 'catlink_id = ' . $this->_db->quote($this->getExisting('catlink_id'));
	}
}