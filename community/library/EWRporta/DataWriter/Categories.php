<?php

class EWRporta_DataWriter_Categories extends XenForo_DataWriter
{
	protected $_existingDataErrorPhrase = 'requested_category_not_found';

	protected function _getFields()
	{
		return array(
			'EWRporta_categories' => array(
				'style_id'			=> array('type' => self::TYPE_UINT, 'required' => true),
				'category_id'		=> array('type' => self::TYPE_UINT, 'required' => true),
				'category_slug'		=> array('type' => self::TYPE_STRING, 'required' => true),
				'category_name'		=> array('type' => self::TYPE_STRING, 'required' => true),
				'category_type'		=> array('type' => self::TYPE_STRING, 'required' => true, 'default' => 'major',
					'allowedValues'		=> array('major', 'minor')
				),
			)
		);
	}

	protected function _getExistingData($data)
	{
		if (!$catID = $this->_getExistingPrimaryKey($data, 'category_id'))
		{
			return false;
		}

		return array('EWRporta_categories' => $this->getModelFromCache('EWRporta_Model_Categories')->getCategoryById($catID));
	}

	protected function _getUpdateCondition($tableName)
	{
		return 'category_id = ' . $this->_db->quote($this->getExisting('category_id'));
	}
}