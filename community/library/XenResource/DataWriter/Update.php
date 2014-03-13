<?php

class XenResource_DataWriter_Update extends XenForo_DataWriter
{
	/**
	 * Holds the temporary hash used to pull attachments and associate them with this message.
	 *
	 * @var string
	 */
	const DATA_ATTACHMENT_HASH = 'attachmentHash';

	const DATA_DELETE_REASON = 'deleteReason';

	const OPTION_UPDATE_LAST_UPDATE = 'updateLastUpdate';

	protected $_resource = null;

	protected $_isFirstVisible = false;

	/**
	* Gets the fields that are defined for the table. See parent for explanation.
	*
	* @return array
	*/
	protected function _getFields()
	{
		return array(
			'xf_resource_update' => array(
				'resource_update_id' => array('type' => self::TYPE_UINT, 'autoIncrement' => true),
				'resource_id'        => array('type' => self::TYPE_UINT, 'required' => true),
				'title'              => array('type' => self::TYPE_STRING, 'required' => true,
					'maxLength' => 100, 'requiredError' => 'please_enter_valid_title'
				),
				'message'            => array('type' => self::TYPE_STRING, 'required' => true,
					'requiredError' => 'please_enter_valid_message'
				),
				'message_state'      => array('type' => self::TYPE_STRING, 'default' => 'visible',
					'allowedValues' => array('visible', 'moderated', 'deleted')
				),
				'post_date'          => array('type' => self::TYPE_UINT, 'required' => true, 'default' => XenForo_Application::$time),
				'attach_count'       => array('type' => self::TYPE_UINT_FORCED, 'default' => 0),
				'likes'              => array('type' => self::TYPE_UINT_FORCED, 'default' => 0),
				'like_users'         => array('type' => self::TYPE_SERIALIZED, 'default' => 'a:0:{}'),
				'ip_id'              => array('type' => self::TYPE_UINT,   'default' => 0),
				'had_first_visible'  => array('type' => self::TYPE_BOOLEAN, 'default' => 0),
				'warning_id'         => array('type' => self::TYPE_UINT,   'default' => 0),
				'warning_message'    => array('type' => self::TYPE_STRING, 'default' => ''),
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
		if (!$id = $this->_getExistingPrimaryKey($data, 'resource_update_id'))
		{
			return false;
		}

		return array('xf_resource_update' => $this->_getUpdateModel()->getUpdateById($id));
	}

	/**
	* Gets SQL condition to update the existing record.
	*
	* @return string
	*/
	protected function _getUpdateCondition($tableName)
	{
		return 'resource_update_id = ' . $this->_db->quote($this->getExisting('resource_update_id'));
	}

	protected function _getDefaultOptions()
	{
		return array(
			self::OPTION_UPDATE_LAST_UPDATE => true
		);
	}

	protected function _preSave()
	{
		if ($this->get('message_state') === null)
		{
			$this->set('message_state', 'visible');
		}

		if (!$this->get('had_first_visible') && $this->get('message_state') == 'visible')
		{
			$this->set('had_first_visible', 1);
			$this->_isFirstVisible = true;
			if (!$this->isChanged('post_date'))
			{
				$this->set('post_date', XenForo_Application::$time);
			}
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

		$resourceDw = XenForo_DataWriter::create('XenResource_DataWriter_Resource', XenForo_DataWriter::ERROR_SILENT);
		if ($resourceDw->setExistingData($this->get('resource_id')))
		{
			if ($this->getOption(self::OPTION_UPDATE_LAST_UPDATE))
			{
				$usernameUpdated = $resourceDw->checkUserName();
				if ($this->isInsert() && $this->get('message_state') == 'visible')
				{
					$resourceDw->updateUpdateCount(1);
					$resourceDw->updateLastUpdate($this->get('post_date'));
					$resourceDw->save();
				}
				else if ($this->isUpdate() && $this->isChanged('message_state'))
				{
					$resourceDw->updateUpdateCount();
					$resourceDw->updateLastUpdate();
					$resourceDw->save();
				}
				else if ($usernameUpdated)
				{
					$resourceDw->save();
				}
			}

			$this->_resource = $resourceDw->getMergedData();
			$this->_updateThread($this->_resource);
		}

		if ($this->isChanged('title') || $this->isChanged('message'))
		{
			$indexer = new XenForo_Search_Indexer();
			$dataHandler = XenForo_Search_DataHandler_Abstract::create('XenResource_Search_DataHandler_Update');

			$dataHandler->insertIntoIndex($indexer, $this->getMergedData(), $this->_resource);
		}

		if ($this->isChanged('message_state'))
		{
			$this->_updateDeletionLog();
			$this->_updateModerationQueue();
		}

		if ($this->_isFirstVisible && $this->_resource)
		{
			if (!$this->get('ip_id'))
			{
				$ipId = XenForo_Model_Ip::log(
					$this->_resource['user_id'], 'resource_update', $this->get('resource_update_id'), 'insert'
				);
				$this->set('ip_id', $ipId, '', array('setAfterPreSave' => true));

				$this->_db->update('xf_resource_update', array(
					'ip_id' => $ipId
				), 'resource_update_id = ' . $this->_db->quote($this->getExisting('resource_update_id')));
			}

			if ($this->get('message_state') == 'visible')
			{
				$this->_getNewsFeedModel()->publish(
					$this->_resource['user_id'],
					$this->_resource['username'],
					'resource_update',
					$this->get('resource_update_id'),
					'insert'
				);
			}
		}
	}

	/**
	* Post-save handling, after the transaction is committed.
	*/
	protected function _postSaveAfterTransaction()
	{
		if ($this->_isFirstVisible)
		{
			if (!$this->_resource && $this->get('resource_id'))
			{
				$this->_resource = $this->_getResourceModel()->getResourceById($this->get('resource_id'));
			}

			if ($this->_resource
				&& $this->_resource['resource_state'] == 'visible'
				&& $this->get('message_state') == 'visible'
				&& !empty($this->_resource['description_update_id'])
				&& $this->_resource['description_update_id'] != $this->get('resource_update_id')
			)
			{
				$update = $this->getMergedData();

				$notified = $this->getModelFromCache('XenResource_Model_ResourceWatch')->sendNotificationToWatchUsersOnUpdate(
					$update, $this->_resource
				);

				$this->getModelFromCache('XenResource_Model_CategoryWatch')->sendNotificationToWatchUsers(
					$update, $this->_resource,
					isset($notified['alerted']) ? $notified['alerted'] : array(),
					isset($notified['emailed']) ? $notified['emailed'] : array()
				);
			}
		}
	}

	/**
	 * Associates attachments with this message.
	 *
	 * @param string $attachmentHash
	 */
	protected function _associateAttachments($attachmentHash)
	{
		$rows = $this->_db->update('xf_attachment', array(
			'content_type' => 'resource_update',
			'content_id' => $this->get('resource_update_id'),
			'temp_hash' => '',
			'unassociated' => 0
		), 'temp_hash = ' . $this->_db->quote($attachmentHash));

		if ($rows)
		{
			$this->set('attach_count', $this->get('attach_count') + $rows, '', array('setAfterPreSave' => true));

			$this->_db->update('xf_resource_update', array(
				'attach_count' => $this->get('attach_count')
			), 'resource_update_id = ' .  $this->_db->quote($this->get('resource_update_id')));
		}
	}

	protected function _updateThread(array $resource)
	{
		if (!$this->_isFirstVisible || !$resource || !$resource['discussion_thread_id'])
		{
			return false;
		}

		$thread = $this->getModelFromCache('XenForo_Model_Thread')->getThreadById($resource['discussion_thread_id']);
		if (!$thread || $thread['discussion_type'] != 'resource')
		{
			return false;
		}

		$forum = $this->getModelFromCache('XenForo_Model_Forum')->getForumById($thread['node_id']);
		if (!$forum)
		{
			return false;
		}

		$messageText = $this->get('message');

		// note: this doesn't actually strip the BB code - it will fix the BB code in the snippet though
		$parser = XenForo_BbCode_Parser::create(XenForo_BbCode_Formatter_Base::create('XenForo_BbCode_Formatter_BbCode_AutoLink', false));
		$snippet = $parser->render(XenForo_Helper_String::wholeWordTrim($messageText, 500));

		$message = new XenForo_Phrase('resource_message_create_update', array(
			'title' => $this->get('title'),
			'username' => $resource['username'],
			'userId' => $resource['user_id'],
			'snippet' => $snippet,
			'updateLink' => XenForo_Link::buildPublicLink('canonical:resources/update', $resource, array('update' => $this->get('resource_update_id'))),
			'resourceTitle' => $resource['title'],
			'resourceLink' => XenForo_Link::buildPublicLink('canonical:resources', $resource)
		), false);

		$writer = XenForo_DataWriter::create('XenForo_DataWriter_DiscussionMessage_Post', XenForo_DataWriter::ERROR_SILENT);
		$writer->bulkSet(array(
			'thread_id' => $thread['thread_id'],
			'user_id' => $resource['user_id'],
			'username' => $resource['username']
		));
		$writer->set('message', $message->render());
		$writer->set('message_state', $this->getModelFromCache('XenForo_Model_Post')->getPostInsertMessageState($thread, $forum));
		$writer->setOption(XenForo_DataWriter_DiscussionMessage::OPTION_PUBLISH_FEED, false);
		$writer->save();

		$threadReadDate = $this->getModelFromCache('XenForo_Model_Thread')->getUserThreadReadDate(
			$resource['user_id'], $thread['thread_id']
		);
		$forumReadDate = $this->getModelFromCache('XenForo_Model_Forum')->getUserForumReadDate(
			$resource['user_id'], $forum['node_id']
		);

		if (max($threadReadDate, $forumReadDate) >= $thread['last_post_date']) {
			$this->getModelFromCache('XenForo_Model_Thread')->markThreadRead(
				$thread, $forum, XenForo_Application::$time
			);
		}

		return $writer->get('post_id');
	}

	/**
	 * Post-delete handling.
	 */
	protected function _postDelete()
	{
		$this->getModelFromCache('XenForo_Model_Alert')->deleteAlerts('resource_update', $this->get('resource_update_id'));

		$this->_deleteAttachments();

		if ($this->get('message_state') == 'visible')
		{
			$resourceDw = XenForo_DataWriter::create('XenResource_DataWriter_Resource', XenForo_DataWriter::ERROR_SILENT);
			if ($resourceDw->setExistingData($this->get('resource_id')))
			{
				$resourceDw->updateLastUpdate();
				$resourceDw->updateUpdateCount(-1);
				$resourceDw->save();
			}
		}

		$indexer = new XenForo_Search_Indexer();
		$indexer->deleteFromIndex('resource_update', $this->get('resource_update_id'));

		$this->_updateDeletionLog(true);
		$this->getModelFromCache('XenForo_Model_ModerationQueue')->deleteFromModerationQueue(
			'resource_update', $this->get('resource_update_id')
		);
	}

	/**
	 * Deletes the attachments associated with this update.
	 */
	protected function _deleteAttachments()
	{
		$this->getModelFromCache('XenForo_Model_Attachment')->deleteAttachmentsFromContentIds(
			'resource_update',
			array($this->get('resource_update_id'))
		);
	}

	protected function _updateDeletionLog($hardDelete = false)
	{
		if ($hardDelete
			|| ($this->isChanged('message_state') && $this->getExisting('message_state') == 'deleted')
		)
		{
			$this->getModelFromCache('XenForo_Model_DeletionLog')->removeDeletionLog(
				'resource_update', $this->get('resource_update_id')
			);
		}

		if ($this->isChanged('message_state') && $this->get('message_state') == 'deleted')
		{
			$reason = $this->getExtraData(self::DATA_DELETE_REASON);
			$this->getModelFromCache('XenForo_Model_DeletionLog')->logDeletion(
				'resource_update', $this->get('resource_update_id'), $reason
			);
		}
	}

	protected function _updateModerationQueue()
	{
		if (!$this->isChanged('message_state'))
		{
			return;
		}

		if ($this->get('message_state') == 'moderated')
		{
			$this->getModelFromCache('XenForo_Model_ModerationQueue')->insertIntoModerationQueue(
				'resource_update', $this->get('resource_update_id'), $this->get('post_date')
			);
		}
		else if ($this->getExisting('message_state') == 'moderated')
		{
			$this->getModelFromCache('XenForo_Model_ModerationQueue')->deleteFromModerationQueue(
				'resource_update', $this->get('resource_update_id')
			);
		}
	}

	public function setResource(array $resource)
	{
		$this->_resource = $resource;
	}

	/**
	 * @return XenResource_Model_Update
	 */
	protected function _getUpdateModel()
	{
		return $this->getModelFromCache('XenResource_Model_Update');
	}

	/**
	* @return XenResource_Model_Resource
	*/
	protected function _getResourceModel()
	{
		return $this->getModelFromCache('XenResource_Model_Resource');
	}
}