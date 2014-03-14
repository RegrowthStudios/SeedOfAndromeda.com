<?php

class XenResource_DataWriter_CategoryWatch extends XenForo_DataWriter
{
	/**
	* Gets the fields that are defined for the table. See parent for explanation.
	*
	* @return array
	*/
	protected function _getFields()
	{
		return array(
			'xf_resource_category_watch' => array(
				'user_id'    => array('type' => self::TYPE_UINT,    'required' => true),
				'resource_category_id'    => array('type' => self::TYPE_UINT,    'required' => true),
				'notify_on'  => array('type' => self::TYPE_STRING, 'default' => '',
					'allowedValues' => array('', 'resource', 'update')
				),
				'send_alert' => array('type' => self::TYPE_BOOLEAN, 'default' => 0),
				'send_email' => array('type' => self::TYPE_BOOLEAN, 'default' => 0),
				'include_children' => array('type' => self::TYPE_BOOLEAN, 'default' => 1)
			)
		);
	}

	/**
	* Gets the actual existing data out of data that was passed in. See parent for explanation.
	*
	* @param mixed
	*
	* @return array|false
	*/
	protected function _getExistingData($data)
	{
		if (!is_array($data))
		{
			return false;
		}
		else if (isset($data['user_id'], $data['resource_category_id']))
		{
			$userId = $data['user_id'];
			$nodeId = $data['resource_category_id'];
		}
		else if (isset($data[0], $data[1]))
		{
			$userId = $data[0];
			$nodeId = $data[1];
		}
		else
		{
			return false;
		}

		return array('xf_resource_category_watch' => $this->_getCategoryWatchModel()->getUserCategoryWatchByCategoryId($userId, $nodeId));
	}

	/**
	* Gets SQL condition to update the existing record.
	*
	* @return string
	*/
	protected function _getUpdateCondition($tableName)
	{
		return 'user_id = ' . $this->_db->quote($this->getExisting('user_id'))
			. ' AND resource_category_id = ' . $this->_db->quote($this->getExisting('resource_category_id'));
	}

	/**
	 * @return XenResource_Model_CategoryWatch
	 */
	protected function _getCategoryWatchModel()
	{
		return $this->getModelFromCache('XenForo_Model_CategoryWatch');
	}
}