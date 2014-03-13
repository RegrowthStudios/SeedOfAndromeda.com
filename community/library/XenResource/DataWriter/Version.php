<?php

class XenResource_DataWriter_Version extends XenForo_DataWriter
{
	/**
	 * Holds the temporary hash used to pull attachments and associate them with this message.
	 *
	 * @var string
	 */
	const DATA_ATTACHMENT_HASH = 'attachmentHash';

	const DATA_DELETE_REASON = 'deleteReason';

	/**
	 * If the resource is fileless, then ensure that the version is fileless. Defaults to false;
	 *
	 * @var string
	 */
	const OPTION_IS_FILELESS = 'isFileless';

	protected $_updateDw = null;

	protected $_isFirstVisible = false;

	/**
	* Gets the fields that are defined for the table. See parent for explanation.
	*
	* @return array
	*/
	protected function _getFields()
	{
		return array(
			'xf_resource_version' => array(
				'resource_version_id' => array('type' => self::TYPE_UINT, 'autoIncrement' => true),
				'resource_id'         => array('type' => self::TYPE_UINT, 'required' => true),
				'resource_update_id'  => array('type' => self::TYPE_UINT, 'default' => 0),
				'version_string'      => array('type' => self::TYPE_STRING, 'required' => true,
					'maxLength' => 25, 'requiredError' => 'please_enter_valid_version'
				),
				'version_state'       => array('type' => self::TYPE_STRING, 'default' => 'visible',
					'allowedValues' => array('visible', 'moderated', 'deleted')
				),
				'download_url'        => array('type' => self::TYPE_STRING, 'default' => '',
					'verification' => array('XenForo_DataWriter_Helper_Uri', 'verifyUriOrEmpty')
				),
				'release_date'        => array('type' => self::TYPE_UINT, 'required' => true, 'default' => XenForo_Application::$time),
				'download_count'      => array('type' => self::TYPE_UINT_FORCED, 'default' => 0),
				'rating_count'        => array('type' => self::TYPE_UINT_FORCED, 'default' => 0),
				'rating_sum'          => array('type' => self::TYPE_UINT_FORCED, 'default' => 0),
				'had_first_visible'   => array('type' => self::TYPE_BOOLEAN, 'default' => 0)
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
		if (!$id = $this->_getExistingPrimaryKey($data, 'resource_version_id'))
		{
			return false;
		}

		return array('xf_resource_version' => $this->_getVersionModel()->getVersionById($id));
	}

	/**
	* Gets SQL condition to update the existing record.
	*
	* @return string
	*/
	protected function _getUpdateCondition($tableName)
	{
		return 'resource_version_id = ' . $this->_db->quote($this->getExisting('resource_version_id'));
	}

	protected function _getDefaultOptions()
	{
		return array(
			self::OPTION_IS_FILELESS => false
		);
	}

	protected function _preSave()
	{
		if ($this->get('version_state') === null)
		{
			$this->set('version_state', 'visible');
		}

		if (!$this->get('had_first_visible') && $this->get('version_state') == 'visible')
		{
			$this->set('had_first_visible', 1);
			$this->_isFirstVisible = true;
			if (!$this->isChanged('release_date'))
			{
				$this->set('release_date', XenForo_Application::$time);
			}
		}

		if ($this->isInsert())
		{
			$attachmentHash = $this->getExtraData(self::DATA_ATTACHMENT_HASH);
			if (!$attachmentHash)
			{
				$attachCount = 0;
			}
			else
			{
				$attachCount = $this->_db->fetchOne('
					SELECT COUNT(*)
					FROM xf_attachment
					WHERE temp_hash = ?
				', $attachmentHash);
			}

			if ($this->getOption(self::OPTION_IS_FILELESS))
			{
				if ($this->get('download_url') || $attachCount)
				{
					$this->error(new XenForo_Phrase('fileless_resource_may_not_have_download_url_or_attached_file'));
				}
			}
			else if ($this->get('download_url') && $attachCount)
			{
				$this->error(new XenForo_Phrase('you_may_only_enter_external_url_or_upload_file'));
			}
			else if (!$this->get('download_url') && $attachCount != 1)
			{
				$this->error(new XenForo_Phrase('you_must_upload_file_to_create_version_or_resource'));
			}

			if (!$this->get('release_date'))
			{
				$this->set('release_date', XenForo_Application::$time);
			}
		}

		if ($this->isUpdate() && $this->isChanged('version_state') && $this->get('version_state') != 'visible')
		{
			$resource = $this->_getResourceModel()->getResourceById($this->get('resource_id'));
			if ($resource && $resource['current_version_id'] == $this->get('resource_version_id'))
			{
				$this->error(new XenForo_Phrase('current_version_of_resource_may_not_be_deleted'));
			}
		}

		if ($this->_updateDw)
		{
			if ($this->_updateDw->isInsert())
			{
				$this->_updateDw->set('resource_id', $this->get('resource_id'));
				$this->_updateDw->set('post_date', $this->get('release_date'));
			}

			$this->_updateDw->preSave();
			$this->_errors += $this->_updateDw->getErrors();
		}
	}

	/**
	 * Post-save handling.
	 */
	protected function _postSave()
	{
		$attachmentHash = $this->getExtraData(self::DATA_ATTACHMENT_HASH);

		if ($attachmentHash)
		{
			$this->_associateAttachments($attachmentHash);
		}

		$postSaveChanges = array();

		if ($this->_isFirstVisible)
		{
			if ($this->get('resource_update_id'))
			{
				$this->getUpdateDw();
			}
			if ($this->_updateDw && !$this->_updateDw->isChanged('post_date'))
			{
				$this->_updateDw->set('post_date',
					$this->get('release_date'), '', array('setAfterPreSave' => true)
				);
			}
		}

		if ($this->_updateDw)
		{
			if ($this->_updateDw->isInsert())
			{
				$this->_updateDw->save();

				$this->set('resource_update_id',
					$this->_updateDw->get('resource_update_id'), '', array('setAfterPreSave' => true)
				);
				$postSaveChanges['resource_update_id'] = $this->get('resource_update_id');
			}
			else
			{
				$this->_updateDw->save();
			}
		}

		if ($postSaveChanges)
		{
			$this->_db->update('xf_resource_version', $postSaveChanges,
				'resource_version_id = ' .  $this->_db->quote($this->get('resource_version_id'))
			);
		}

		if ($this->isChanged('version_state'))
		{
			$resourceDw = XenForo_DataWriter::create('XenResource_DataWriter_Resource', XenForo_DataWriter::ERROR_SILENT);
			if ($resourceDw->setExistingData($this->get('resource_id')))
			{
				$resourceDw->updateCurrentVersion();
				$resourceDw->save();
			}

			$this->_updateDeletionLog();
			$this->_updateModerationQueue();
		}
	}

	/**
	 * Associates attachments with this message.
	 *
	 * @param string $attachmentHash
	 */
	protected function _associateAttachments($attachmentHash)
	{
		$this->_db->update('xf_attachment', array(
			'content_type' => 'resource_version',
			'content_id' => $this->get('resource_version_id'),
			'temp_hash' => '',
			'unassociated' => 0
		), 'temp_hash = ' . $this->_db->quote($attachmentHash));
	}

	protected function _preDelete()
	{
		$resource = $this->_getResourceModel()->getResourceById($this->get('resource_id'));
		if ($resource && $resource['current_version_id'] == $this->get('resource_version_id'))
		{
			$this->error(new XenForo_Phrase('current_version_of_resource_may_not_be_deleted'));
		}
	}

	/**
	 * Post-delete handling.
	 */
	protected function _postDelete()
	{
		$this->_deleteAttachments();

		$resourceDw = XenForo_DataWriter::create('XenResource_DataWriter_Resource', XenForo_DataWriter::ERROR_SILENT);
		if ($resourceDw->setExistingData($this->get('resource_id')))
		{
			$resourceDw->updateLastUpdate();
			$resourceDw->save();
		}

		$this->_updateDeletionLog(true);
		$this->getModelFromCache('XenForo_Model_ModerationQueue')->deleteFromModerationQueue(
			'resource_version', $this->get('resource_version_id')
		);
	}

	/**
	 * Deletes the attachments associated with this version.
	 */
	protected function _deleteAttachments()
	{
		$this->getModelFromCache('XenForo_Model_Attachment')->deleteAttachmentsFromContentIds(
			'resource_version',
			array($this->get('resource_version_id'))
		);
	}

	protected function _updateDeletionLog($hardDelete = false)
	{
		if ($hardDelete
			|| ($this->isChanged('version_state') && $this->getExisting('version_state') == 'deleted')
		)
		{
			$this->getModelFromCache('XenForo_Model_DeletionLog')->removeDeletionLog(
				'resource_version', $this->get('resource_version_id')
			);
		}

		if ($this->isChanged('version_state') && $this->get('version_state') == 'deleted')
		{
			$reason = $this->getExtraData(self::DATA_DELETE_REASON);
			$this->getModelFromCache('XenForo_Model_DeletionLog')->logDeletion(
				'resource_version', $this->get('resource_version_id'), $reason
			);
		}
	}

	protected function _updateModerationQueue()
	{
		if (!$this->isChanged('version_state'))
		{
			return;
		}

		if ($this->get('version_state') == 'moderated')
		{
			$this->getModelFromCache('XenForo_Model_ModerationQueue')->insertIntoModerationQueue(
				'resource_version', $this->get('resource_version_id'), $this->get('release_date')
			);
		}
		else if ($this->getExisting('version_state') == 'moderated')
		{
			$this->getModelFromCache('XenForo_Model_ModerationQueue')->deleteFromModerationQueue(
				'resource_version', $this->get('resource_version_id')
			);
		}
	}

	public function getUpdateDw()
	{
		if ($this->_updateDw === null)
		{
			$this->_updateDw = XenForo_DataWriter::create('XenResource_DataWriter_Update', $this->_errorHandler);
			if ($updateId = $this->get('resource_update_id'))
			{
				if (!$this->_updateDw->setExistingData($updateId))
				{
					$this->_updateDw = false;
				}
			}
		}

		return $this->_updateDw;
	}

	/**
	 * @return XenResource_Model_Version
	 */
	protected function _getVersionModel()
	{
		return $this->getModelFromCache('XenResource_Model_Version');
	}

	/**
	 * @return XenResource_Model_Resource
	 */
	protected function _getResourceModel()
	{
		return $this->getModelFromCache('XenResource_Model_Resource');
	}
}