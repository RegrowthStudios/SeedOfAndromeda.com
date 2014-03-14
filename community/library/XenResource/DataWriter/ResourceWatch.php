<?php

class XenResource_DataWriter_ResourceWatch extends XenForo_DataWriter
{
	/**
	* Gets the fields that are defined for the table. See parent for explanation.
	*
	* @return array
	*/
	protected function _getFields()
	{
		return array(
			'xf_resource_watch' => array(
				'user_id'          => array('type' => self::TYPE_UINT,    'required' => true),
				'resource_id'      => array('type' => self::TYPE_UINT,    'required' => true),
				'email_subscribe'  => array('type' => self::TYPE_BOOLEAN, 'default' => 0),
				'watch_key'        => array('type' => self::TYPE_STRING,  'default' => '', 'maxLength' => 16)
			)
		);
	}

	/**
	* Gets the actual existing data out of data that was passed in. See parent for explanation.
	*
	* @param mixed
	*
	* @return array|bool
	*/
	protected function _getExistingData($data)
	{
		if (!is_array($data))
		{
			return false;
		}
		else if (isset($data['user_id'], $data['resource_id']))
		{
			$userId = $data['user_id'];
			$threadId = $data['resource_id'];
		}
		else if (isset($data[0], $data[1]))
		{
			$userId = $data[0];
			$threadId = $data[1];
		}
		else
		{
			return false;
		}

		return array('xf_resource_watch' => $this->_getResourceWatchModel()->getUserResourceWatchByThreadId($userId, $threadId));
	}

	/**
	* Gets SQL condition to update the existing record.
	*
	* @return string
	*/
	protected function _getUpdateCondition($tableName)
	{
		return 'user_id = ' . $this->_db->quote($this->getExisting('user_id'))
			. ' AND resource_id = ' . $this->_db->quote($this->getExisting('resource_id'));
	}

	protected function _preSave()
	{
		if (!$this->get('watch_key'))
		{
			$this->set('watch_key', substr(md5(uniqid()), 0, 16));
		}
	}

	/**
	 * @return XenResource_Model_ResourceWatch
	 */
	protected function _getResourceWatchModel()
	{
		return $this->getModelFromCache('XenResource_Model_ResourceWatch');
	}
}