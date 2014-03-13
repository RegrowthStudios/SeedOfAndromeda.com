<?php

class XenResource_DataWriter_Rating extends XenForo_DataWriter
{
	/**
	 * Constants to store the extra data fields for version and resource DWs in _postSave
	 *
	 * @var string
	 * @var string
	 */
	const DATA_RESOURCE_DW = 'XenResource_DataWriter_Resource';
	const DATA_VERSION_DW = 'XenResource_DataWriter_Version';

	const DATA_DELETE_REASON = 'deleteReason';

	/**
	* Gets the fields that are defined for the table. See parent for explanation.
	*
	* @return array
	*/
	protected function _getFields()
	{
		return array(
			'xf_resource_rating' => array(
				'resource_rating_id'  => array('type' => self::TYPE_UINT, 'autoIncrement' => true),
				'resource_version_id' => array('type' => self::TYPE_UINT, 'required' => true),
				'version_string'      => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 50),
				'resource_id'         => array('type' => self::TYPE_UINT, 'required' => true),
				'user_id'             => array('type' => self::TYPE_UINT, 'required' => true),
				'rating'              => array('type' => self::TYPE_UINT, 'required' => true, 'min' => 1, 'max' => 5),
				'rating_date'         => array('type' => self::TYPE_UINT, 'required' => true, 'default' => XenForo_Application::$time),
				'rating_state'        => array('type' => self::TYPE_STRING, 'default' => 'visible',
					'allowedValues' => array('visible', 'deleted')
				),
				'message'             => array('type' => self::TYPE_STRING, 'default' => ''),
				'author_response'     => array('type' => self::TYPE_STRING, 'default' => ''),
				'is_review'           => array('type' => self::TYPE_BOOLEAN, 'default' => 0),
				'count_rating'        => array('type' => self::TYPE_BOOLEAN, 'default' => 1),
				'warning_id'          => array('type' => self::TYPE_UINT,   'default' => 0),
				'is_anonymous'        => array('type' => self::TYPE_BOOLEAN, 'default' => 0),
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
		$versionId = false;
		$userId = false;
		$ratingId = false;

		if (!is_array($data))
		{
			$ratingId = $data;
		}
		else if (isset($data['resource_version_id'], $data['user_id']))
		{
			$versionId = $data['resource_version_id'];
			$userId = $data['user_id'];
		}
		else if (isset($data[0], $data[1]))
		{
			$versionId = $data[0];
			$userId = $data[1];
		}
		else
		{
			return false;
		}

		if ($ratingId)
		{
			$rating = $this->_getRatingModel()->getRatingById($ratingId);
		}
		else
		{
			$rating = $this->_getRatingModel()->getRatingByVersionAndUserId($versionId, $userId);
		}
		return array('xf_resource_rating' => $rating);
	}

	/**
	* Gets SQL condition to update the existing record.
	*
	* @return string
	*/
	protected function _getUpdateCondition($tableName)
	{
		return 'resource_rating_id = ' . $this->_db->quote($this->getExisting('resource_rating_id'));
	}

	protected function _preSave()
	{
		if ($this->get('message'))
		{
			$this->set('is_review', 1);
		}

		if (!$this->get('user_id') || !$this->get('resource_version_id'))
		{
			throw new XenForo_Exception('Must provide user and version ID');
		}

		if ($this->isChanged('user_id') || $this->isChanged('resource_version_id'))
		{
			$existing = $this->_getRatingModel()->getRatingByVersionAndUserId(
				$this->get('resource_version_id'), $this->get('user_id')
			);
			if ($existing)
			{
				throw new XenForo_Exception('Duplicate record insert attempted');
			}
		}
	}

	/**
	 * Post-save handling.
	 */
	protected function _postSave()
	{
		$this->_updateResource($this->get('rating'), null);

		if ($this->isChanged('rating_state'))
		{
			$this->_updateDeletionLog();
		}

		if ($this->isInsert() && $this->get('is_review'))
		{
			$resource = $this->_getResourceModel()->getResourceById($this->get('resource_id'), array(
				'join' => XenResource_Model_Resource::FETCH_USER | XenResource_Model_Resource::FETCH_USER_OPTION
			));

			if ($resource && XenForo_Model_Alert::userReceivesAlert($resource, 'resource_rating', 'review'))
			{
				$user = $this->getModelFromCache('XenForo_Model_User')->getUserById($this->get('user_id'));
				if ($user)
				{
					XenForo_Model_Alert::alert(
						$resource['user_id'],
						$user['user_id'],
						$user['username'],
						'resource_rating',
						$this->get('resource_rating_id'),
						'review'
					);
				}
			}
		}

		if ($this->isUpdate() && $this->isChanged('author_response') && $this->get('author_response'))
		{
			$resource = $this->_getResourceModel()->getResourceById($this->get('resource_id'), array(
				'join' => XenResource_Model_Resource::FETCH_USER | XenResource_Model_Resource::FETCH_USER_OPTION
			));

			$user = $this->getModelFromCache('XenForo_Model_User')->getUserById($this->get('user_id'));

			if ($resource && $user && XenForo_Model_Alert::userReceivesAlert($user, 'resource_rating', 'reply'))
			{
				XenForo_Model_Alert::alert(
					$user['user_id'],
					$resource['user_id'],
					$resource['username'],
					'resource_rating',
					$this->get('resource_rating_id'),
					'reply'
				);
			}
		}
	}

	/**
	 * Post-delete handling.
	 */
	protected function _postDelete()
	{
		$this->_updateResource($this->getExisting('rating'), true);
		$this->_updateDeletionLog(true);

		/* @var $alertModel XenForo_Model_Alert */
		$alertModel = $this->getModelFromCache('XenForo_Model_Alert');
		$alertModel->deleteAlerts('resource_rating', $this->get('resource_rating_id'));
	}

	protected function _recalculateCountable()
	{
		if (!$this->get('user_id'))
		{
			return;
		}

		$this->_db->query('
			UPDATE xf_resource_rating
			SET count_rating = 0
			WHERE resource_id = ?
				AND user_id = ?
		', array($this->get('resource_id'), $this->get('user_id')));
		$this->_db->query('
			UPDATE xf_resource_rating
			SET count_rating = 1
			WHERE resource_id = ?
				AND user_id = ?
				AND rating_state = \'visible\'
			ORDER BY rating_date DESC
			LIMIT 1
		', array($this->get('resource_id'), $this->get('user_id')));
	}

	/**
	 * Update the resource and version tables to reflect the new rating
	 *
	 * @param integer $rating
	 */
	protected function _updateResource($rating, $isDelete = null)
	{
		if ($isDelete === null)
		{
			$isDelete = ($this->isUpdate() && $this->isChanged('rating_state') && $this->get('rating_state') == 'deleted');
		}
		$isVisible = ($this->get('rating_state') == 'visible');
		$wasVisible = ($this->getExisting('rating_state') == 'visible');

		$versionDw = XenForo_DataWriter::create('XenResource_DataWriter_Version', XenForo_DataWriter::ERROR_SILENT);
		$versionDw->setExistingData($this->get('resource_version_id'));

		$resourceDw = XenForo_DataWriter::create('XenResource_DataWriter_Resource', XenForo_DataWriter::ERROR_SILENT);
		$resourceDw->setExistingData($versionDw->get('resource_id'));

		if ($this->isChanged('rating_state') && $isVisible)
		{
			// deleted -> visible
			$versionDw->set('rating_count', $versionDw->get('rating_count') + 1);
			$versionDw->set('rating_sum', $versionDw->get('rating_sum') + $rating);
		}
		else if ($isDelete && $wasVisible)
		{
			// visible -> delete
			$versionDw->set('rating_count', $versionDw->get('rating_count') - 1);
			$versionDw->set('rating_sum', $versionDw->get('rating_sum') - $rating);
		}
		else if ($isVisible)
		{
			// state hasn't changed, but check if the total has
			$versionDw->set('rating_sum', $versionDw->get('rating_sum') + $rating - $this->getExisting('rating'));
		}

		if ($wasVisible && $isDelete && $this->get('is_review'))
		{
			$resourceDw->updateReviewCount(-1);
		}
		else if ($isVisible && $this->get('is_review') && (!$wasVisible || !$this->getExisting('is_review')))
		{
			$resourceDw->updateReviewCount(1);
		}
		else if ($wasVisible && $this->getExisting('is_review') && (!$this->get('is_review') || !$isVisible))
		{
			$resourceDw->updateReviewCount(-1);
		}

		$this->_recalculateCountable();
		$resourceDw->updateRating();

		$versionDw->save();
		$resourceDw->save();

		$this->setExtraData(self::DATA_VERSION_DW, $versionDw);
		$this->setExtraData(self::DATA_RESOURCE_DW, $resourceDw);
	}

	protected function _updateDeletionLog($hardDelete = false)
	{
		if ($hardDelete
			|| ($this->isChanged('rating_state') && $this->getExisting('rating_state') == 'deleted')
		)
		{
			$this->getModelFromCache('XenForo_Model_DeletionLog')->removeDeletionLog(
				'resource_rating', $this->get('resource_rating_id')
			);
		}

		if ($this->isChanged('rating_state') && $this->get('rating_state') == 'deleted')
		{
			$reason = $this->getExtraData(self::DATA_DELETE_REASON);
			$this->getModelFromCache('XenForo_Model_DeletionLog')->logDeletion(
				'resource_rating', $this->get('resource_rating_id'), $reason
			);
		}
	}

	/**
	* @return XenResource_Model_Rating
	*/
	protected function _getRatingModel()
	{
		return $this->getModelFromCache('XenResource_Model_Rating');
	}

	/**
	 * @return XenResource_Model_Resource
	 */
	protected function _getResourceModel()
	{
		return $this->getModelFromCache('XenResource_Model_Resource');
	}
}