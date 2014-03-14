<?php

class EWRcarta_DataWriter_PageWatch extends XenForo_DataWriter
{
	protected function _getFields()
	{
		return array(
			'EWRcarta_watch' => array(
				'user_id'          => array('type' => self::TYPE_UINT,    'required' => true),
				'page_id'          => array('type' => self::TYPE_UINT,    'required' => true),
				'email_subscribe'  => array('type' => self::TYPE_BOOLEAN, 'default' => 0)
			)
		);
	}

	protected function _getExistingData($data)
	{
		if (!is_array($data))
		{
			return false;
		}
		else if (isset($data['user_id'], $data['page_id']))
		{
			$userId = $data['user_id'];
			$pageId = $data['page_id'];
		}
		else if (isset($data[0], $data[1]))
		{
			$userId = $data[0];
			$pageId = $data[1];
		}
		else
		{
			return false;
		}

		return array('EWRcarta_watch' => $this->getModelFromCache('EWRcarta_Model_PageWatch')->getUserPageWatchByPageId($userId, $pageId));
	}

	protected function _getUpdateCondition($tableName)
	{
		return 'user_id = ' . $this->_db->quote($this->getExisting('user_id'))
			. ' AND page_id = ' . $this->_db->quote($this->getExisting('page_id'));
	}
}