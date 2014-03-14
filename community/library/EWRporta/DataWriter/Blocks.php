<?php

class EWRporta_DataWriter_Blocks extends XenForo_DataWriter
{
	protected $_existingDataErrorPhrase = 'requested_page_not_found';

	protected function _getFields()
	{
		return array(
			'EWRporta_blocks' => array(
				'block_id'					=> array('type' => self::TYPE_STRING,	'required' => true, 'verification' => array('$this', '_verifyBlockId')),
				'title'						=> array('type' => self::TYPE_STRING,	'required' => true, 'default' => ''),
				'version_string'			=> array('type' => self::TYPE_STRING,	'required' => false, 'default' => ''),
				'version_id'				=> array('type' => self::TYPE_UINT,		'required' => false, 'default' => 0),
				'install_callback_class'	=> array('type' => self::TYPE_STRING,	'required' => false, 'default' => ''),
				'install_callback_method'	=> array('type' => self::TYPE_STRING,	'required' => false, 'default' => ''),
				'uninstall_callback_class'	=> array('type' => self::TYPE_STRING,	'required' => false, 'default' => ''),
				'uninstall_callback_method'	=> array('type' => self::TYPE_STRING,	'required' => false, 'default' => ''),
				'cache'						=> array('type' => self::TYPE_STRING,	'required' => true, 'default' => '+10 minutes'),
				'display'					=> array('type' => self::TYPE_STRING,	'required' => true, 'default' => 'show',
					'allowedValues' => array('show', 'hide'),
				),
				'groups'					=> array('type' => self::TYPE_STRING,	'required' => false, 'maxLength' => 255, 'default' => ''),
				'url'						=> array('type' => self::TYPE_STRING,	'required' => false, 'maxLength' => 100, 'default' => ''),
				'locked'					=> array('type' => self::TYPE_BOOLEAN,	'required' => true, 'default' => 0),
				'active'					=> array('type' => self::TYPE_BOOLEAN,	'required' => true, 'default' => 1)
			)
		);
	}

	protected function _getExistingData($data)
	{
		if (!$blockId = $this->_getExistingPrimaryKey($data, 'block_id'))
		{
			return false;
		}

		return array('EWRporta_blocks' => $this->getModelFromCache('EWRporta_Model_Blocks')->getBlockById($blockId));
	}

	protected function _getUpdateCondition($tableName)
	{
		return 'block_id = ' . $this->_db->quote($this->getExisting('block_id'));
	}

	protected function _verifyBlockId($blockId)
	{
		if (preg_match('/[^a-zA-Z0-9_]/', $blockId))
		{
			$this->error(new XenForo_Phrase('please_enter_an_id_using_only_alphanumeric'), 'block_id');
			return false;
		}

		if ($this->isInsert() || $blockId != $this->getExisting('block_id'))
		{
			$existing = $this->getModelFromCache('EWRporta_Model_Blocks')->getBlockById($blockId);
			if ($existing)
			{
				$this->error(new XenForo_Phrase('add_on_ids_must_be_unique'), 'block_id');
				return false;
			}
		}

		return true;
	}

	protected function _preSave()
	{
		if ($this->get('install_callback_class') || $this->get('install_callback_method'))
		{
			$class = $this->get('install_callback_class');
			$method = $this->get('install_callback_method');

			if (!XenForo_Application::autoload($class) || !method_exists($class, $method))
			{
				$this->error(new XenForo_Phrase('please_enter_valid_callback_method'), 'install_callback_method');
			}
		}

		if ($this->get('uninstall_callback_class') || $this->get('uninstall_callback_method'))
		{
			$class = $this->get('uninstall_callback_class');
			$method = $this->get('uninstall_callback_method');

			if (!XenForo_Application::autoload($class) || !method_exists($class, $method))
			{
				$this->error(new XenForo_Phrase('please_enter_valid_callback_method'), 'uninstall_callback_method');
			}
		}
	}

	protected function _postSave()
	{
		if ($this->isUpdate() && $this->isChanged('block_id'))
		{
			$db = $this->_db;
			$updateClause = 'block_id = ' . $db->quote($this->getExisting('block_id'));
			$updateValue = array('block_id' => $this->get('block_id'));

			$db->update('EWRporta_options', $updateValue, $updateClause);
		}
	}

	protected function _preDelete()
	{
		if ($this->get('uninstall_callback_class') && $this->get('uninstall_callback_method'))
		{
			$class = $this->get('uninstall_callback_class');
			$method = $this->get('uninstall_callback_method');

			if (!XenForo_Application::autoload($class) || !method_exists($class, $method))
			{
				$this->error(new XenForo_Phrase('files_necessary_uninstallation_addon_not_found'));
			}
		}
	}

	protected function _postDelete()
	{
		if ($this->get('uninstall_callback_class') && $this->get('uninstall_callback_method'))
		{
			call_user_func(
				array($this->get('uninstall_callback_class'), $this->get('uninstall_callback_method')),
				$this->getMergedData()
			);
		}
	}
}