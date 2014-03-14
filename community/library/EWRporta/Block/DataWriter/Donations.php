<?php

class EWRporta_Block_DataWriter_Donations extends XenForo_DataWriter
{
	protected $_existingDataErrorPhrase = 'requested_user_upgrade_not_found';

	protected function _getFields()
	{
		return array(
			'EWRporta_donations' => array(
				'donation_id'			=> array('type' => self::TYPE_UINT,		'autoIncrement' => true),
				'drive_id'				=> array('type' => self::TYPE_STRING,	'required' => true, 'default' => ''),
				'amount'				=> array('type' => self::TYPE_FLOAT,	'required' => true, 'default' => '0'),
				'user_id'				=> array('type' => self::TYPE_UINT,		'required' => true, 'default' => '0'),
				'transaction_id'		=> array('type' => self::TYPE_STRING,	'required' => true, 'default' => ''),
				'transaction_type'		=> array('type' => self::TYPE_STRING,	'required' => true,
						'allowedValues' => array('payment','cancel','info','error')
				),
				'message'				=> array('type' => self::TYPE_STRING,	'required' => true),
				'transaction_details'	=> array('type' => self::TYPE_STRING,	'required' => true),
				'log_date'				=> array('type' => self::TYPE_UINT,		'required' => true),
			)
		);
	}

	protected function _getExistingData($data)
	{
		if (!$id = $this->_getExistingPrimaryKey($data))
		{
			return false;
		}

		return array('EWRporta_donations' => $this->_getUserUpgradeModel()->getUserUpgradeById($id));
	}

	protected function _getUpdateCondition($tableName)
	{
		return 'donation_id = ' . $this->_db->quote($this->getExisting('donation_id'));
	}

	protected function _postSave()
	{
		$this->getModelFromCache('EWRporta_Model_Caches')->emptyBlockCache(array('block_id'=>'Donations'));
	}
}