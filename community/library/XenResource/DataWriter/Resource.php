<?php

class XenResource_DataWriter_Resource extends XenForo_DataWriter
{
	const DATA_DESCRIPTION_MESSAGE = 'message';
	const DATA_DESCRIPTION_ATTACHMENT_HASH = 'descriptionAttachmentHash';

	const DATA_VERSION_STRING = 'versionString';
	const DATA_VERSION_ATTACHMENT_HASH = 'versionAttachmentHash';

	const DATA_THREAD_WATCH_DEFAULT = 'watchDefault';

	/**
	 * Holds the reason for soft deletion.
	 *
	 * @var string
	 */
	const DATA_DELETE_REASON = 'deleteReason';

	const DATA_FIELD_DEFINITIONS = 'fieldDefinitions';

	const OPTION_CREATE_THREAD_NODE_ID = 'createThreadNodeId';
	const OPTION_CREATE_THREAD_PREFIX_ID = 'createThreadNodeId';
	const OPTION_PAID_THREAD_TITLE_TEMPLATE = 'paidThreadTitleTemplate';
	const OPTION_DELETE_THREAD_TITLE_TEMPLATE = 'deleteThreadTitleTemplate';
	const OPTION_DELETE_THREAD_ACTION = 'deleteThreadAction';
	const OPTION_DELETE_ADD_POST = 'deleteAddPost';

	/**
	 * @var XenResource_DataWriter_Update
	 */
	protected $_descriptionDw = null;

	/**
	 * @var XenResource_DataWriter_Version
	 */
	protected $_versionDw = null;

	protected $_isFirstVisible = false;

	/**
	 * The custom fields to be updated. Use setCustomFields to manage this.
	 *
	 * @var array|null
	 */
	protected $_updateCustomFields = null;

	/**
	* Gets the fields that are defined for the table. See parent for explanation.
	*
	* @return array
	*/
	protected function _getFields()
	{
		return array(
			'xf_resource' => array(
				'resource_id'          => array('type' => self::TYPE_UINT, 'autoIncrement' => true),
				'title'                => array('type' => self::TYPE_STRING, 'required' => true,
					'maxLength' => 100, 'requiredError' => 'please_enter_valid_title'
				),
				'tag_line'             => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 100,
					'requiredError' => 'please_enter_valid_tag_line'
				),
				'user_id'              => array('type' => self::TYPE_UINT, 'required' => true),
				'username'             => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 50,
					'requiredError' => 'please_enter_valid_name'
				),
				'resource_state'       => array('type' => self::TYPE_STRING, 'default' => 'visible',
					'allowedValues' => array('visible', 'moderated', 'deleted')
				),
				'resource_date'        => array('type' => self::TYPE_UINT, 'default' => XenForo_Application::$time),
				'resource_category_id' => array('type' => self::TYPE_UINT, 'required' => true,
					'verification' => array('$this', '_validateCategoryId')
				),
				'current_version_id'   => array('type' => self::TYPE_UINT, 'default' => 0),
				'description_update_id'=> array('type' => self::TYPE_UINT, 'default' => 0),
				'discussion_thread_id' => array('type' => self::TYPE_UINT, 'default' => 0),
				'external_url'         => array('type' => self::TYPE_STRING, 'default' => '',
					'verification' => array('XenForo_DataWriter_Helper_Uri', 'verifyUriOrEmpty')
				),
				'is_fileless'          => array('type' => self::TYPE_BOOLEAN, 'default' => 0),
				'external_purchase_url'=> array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 500,
					'verification' => array('XenForo_DataWriter_Helper_Uri', 'verifyUriOrEmpty')
				),
				'price'                => array('type' => self::TYPE_FLOAT, 'default' => 0),
				'currency'             => array('type' => self::TYPE_STRING,  'default' => ''),
				'download_count'       => array('type' => self::TYPE_UINT_FORCED, 'default' => 0),
				'rating_count'         => array('type' => self::TYPE_UINT_FORCED, 'default' => 0),
				'rating_sum'           => array('type' => self::TYPE_UINT_FORCED, 'default' => 0),
				'rating_avg'           => array('type' => self::TYPE_FLOAT, 'default' => 0),
				'rating_weighted'      => array('type' => self::TYPE_FLOAT, 'default' => 0),
				'update_count'         => array('type' => self::TYPE_UINT_FORCED, 'default' => 0),
				'review_count'         => array('type' => self::TYPE_UINT_FORCED, 'default' => 0),
				'last_update'          => array('type' => self::TYPE_UINT, 'default' => XenForo_Application::$time),
				'alt_support_url'      => array('type' => self::TYPE_STRING, 'default' => '',
					'verification' => array('XenForo_DataWriter_Helper_Uri', 'verifyUriOrEmpty')
				),
				'had_first_visible'    => array('type' => self::TYPE_BOOLEAN, 'default' => 0),
				'custom_resource_fields' => array('type' => self::TYPE_SERIALIZED, 'default' => ''),
				'prefix_id'            => array('type' => self::TYPE_UINT, 'default' => 0),
				'icon_date'            => array('type' => self::TYPE_UINT, 'default' => 0)
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
		if (!$id = $this->_getExistingPrimaryKey($data, 'resource_id'))
		{
			return false;
		}

		return array('xf_resource' => $this->_getResourceModel()->getResourceById($id));
	}

	/**
	* Gets SQL condition to update the existing record.
	*
	* @return string
	*/
	protected function _getUpdateCondition($tableName)
	{
		return 'resource_id = ' . $this->_db->quote($this->getExisting('resource_id'));
	}

	protected function _getDefaultOptions()
	{
		$options = XenForo_Application::getOptions();

		return array(
			self::OPTION_CREATE_THREAD_NODE_ID => null,
			self::OPTION_CREATE_THREAD_PREFIX_ID => null,
			self::OPTION_PAID_THREAD_TITLE_TEMPLATE => $options->paidResourceThreadTemplate,
			self::OPTION_DELETE_THREAD_ACTION => $options->get('resourceDeleteThreadAction', 'action'),
			self::OPTION_DELETE_THREAD_TITLE_TEMPLATE => $options->get('resourceDeleteThreadAction', 'update_title') ? $options->get('resourceDeleteThreadAction', 'title_template') : '',
			self::OPTION_DELETE_ADD_POST => $options->get('resourceDeleteThreadAction', 'add_post')
		);
	}

	protected function _validateCategoryId(&$id)
	{
		$category = $this->_getCategoryModel()->getCategoryById($id);
		if (!$category)
		{
			$this->error(new XenForo_Phrase('requested_category_not_found'), 'resource_category_id');
			return false;
		}

		return true;
	}

	public function checkUserName()
	{
		if ($this->get('user_id'))
		{
			$user = $this->_getUserModel()->getUserById($this->get('user_id'));
			if ($user)
			{
				$changed = $this->get('username') != $user['username'];
				$this->set('username', $user['username']);

				return $changed;
			}
		}

		return false;
	}

	public function setCustomFields(array $fieldValues, array $fieldsShown = null)
	{
		$fieldModel = $this->_getFieldModel();
		$fields = $fieldModel->getResourceFieldsForEdit($this->get('resource_category_id'));

		if (!is_array($fieldsShown))
		{
			$fieldsShown = array_keys($fields);
		}

		if ($this->get('resource_id') && !$this->_importMode)
		{
			$existingValues = $fieldModel->getResourceFieldValues($this->get('resource_id'));
		}
		else
		{
			$existingValues = array();
		}

		$finalValues = array();

		foreach ($fieldsShown AS $fieldId)
		{
			if (!isset($fields[$fieldId]))
			{
				continue;
			}

			$field = $fields[$fieldId];
			$multiChoice = ($field['field_type'] == 'checkbox' || $field['field_type'] == 'multiselect');

			if ($multiChoice)
			{
				// multi selection - array
				$value = array();
				if (isset($fieldValues[$fieldId]))
				{
					if (is_string($fieldValues[$fieldId]))
					{
						$value = array($fieldValues[$fieldId]);
					}
					else if (is_array($fieldValues[$fieldId]))
					{
						$value = $fieldValues[$fieldId];
					}
				}
			}
			else
			{
				// single selection - string
				if (isset($fieldValues[$fieldId]))
				{
					if (is_array($fieldValues[$fieldId]))
					{
						$value = count($fieldValues[$fieldId]) ? strval(reset($fieldValues[$fieldId])) : '';
					}
					else
					{
						$value = strval($fieldValues[$fieldId]);
					}
				}
				else
				{
					$value = '';
				}
			}

			$existingValue = (isset($existingValues[$fieldId]) ? $existingValues[$fieldId] : null);

			if (!$this->_importMode)
			{
				$valid = $fieldModel->verifyResourceFieldValue($field, $value, $error);
				if (!$valid)
				{
					$this->error($error, "custom_field_$fieldId");
					continue;
				}

				if ($field['required'] && ($value === '' || $value === array()))
				{
					$this->error(new XenForo_Phrase('please_enter_value_for_all_required_fields'), "required");
					continue;
				}
			}

			if ($value !== $existingValue)
			{
				$finalValues[$fieldId] = $value;
			}
		}

		$this->_updateCustomFields = $this->_filterValidFields($finalValues + $existingValues, $fields);
		$this->set('custom_resource_fields', $this->_updateCustomFields);
	}

	protected function _filterValidFields(array $values, array $fields)
	{
		$newValues = array();
		foreach ($fields AS $field)
		{
			if (isset($values[$field['field_id']]))
			{
				$newValues[$field['field_id']] = $values[$field['field_id']];
			}
		}

		return $newValues;
	}

	protected function _preSave()
	{
		if ($this->get('resource_state') === null)
		{
			$this->set('resource_state', 'visible');
		}

		if (!$this->get('had_first_visible') && $this->get('resource_state') == 'visible')
		{
			$this->set('had_first_visible', 1);
			$this->_isFirstVisible = true;
			if (!$this->isChanged('resource_date'))
			{
				$this->set('resource_date', XenForo_Application::$time);
				$this->set('last_update', XenForo_Application::$time);
			}
		}

		$commercialParts = (floatval($this->get('price')) ? 1 : 0)
			+ ($this->get('currency') ? 1 : 0)
			+ ($this->get('external_purchase_url') ? 1 : 0);
		if ($commercialParts > 0 && $commercialParts < 3)
		{
			$this->error(new XenForo_Phrase('please_complete_all_commercial_resource_related_fields'), 'currency');
		}

		if ($this->isChanged('resource_category_id'))
		{
			$category = $this->_getCategoryModel()->getCategoryById($this->get('resource_category_id'));
			if ($this->get('external_purchase_url') && !$category['allow_commercial_external'])
			{
				$this->error(new XenForo_Phrase('this_category_does_not_allow_external_commercial_resources'), 'resource_category_id');
			}
			else if ($this->get('is_fileless') && !$this->get('external_purchase_url') && !$category['allow_fileless'])
			{
				$this->error(new XenForo_Phrase('this_category_does_not_allow_fileless_resources'), 'resource_category_id');
			}
			else if (!$this->get('is_fileless') && !$category['allow_local'] && !$category['allow_external'])
			{
				$this->error(new XenForo_Phrase('category_not_allow_new_resources'), 'resource_category_id');
			}

			if ($this->isUpdate() && !is_array($this->_updateCustomFields))
			{
				// need to filter the custom fields to only the fields that apply to the new category
				$fieldModel = $this->_getFieldModel();

				$this->_updateCustomFields = $this->_filterValidFields(
					$fieldModel->getResourceFieldValues($this->get('resource_id')),
					$fieldModel->getResourceFieldsForEdit($this->get('resource_category_id'))
				);
				$this->set('custom_resource_fields', $this->_updateCustomFields);
			}
		}

		if ($this->get('prefix_id') && $this->isChanged('resource_category_id'))
		{
			if (!$this->_getPrefixModel()->getPrefixIfInCategory($this->get('prefix_id'), $this->get('resource_category_id')))
			{
				$this->set('prefix_id', 0); // invalid prefix
			}
		}

		if ($this->_descriptionDw)
		{
			if ($this->_descriptionDw->isInsert())
			{
				$this->_descriptionDw->set('resource_id', 0); // temporary, set later
				$this->_descriptionDw->setOption(XenResource_DataWriter_Update::OPTION_UPDATE_LAST_UPDATE, false);
			}
			$this->_descriptionDw->set('title', $this->get('title'));

			$this->_descriptionDw->preSave();
			$this->_errors += $this->_descriptionDw->getErrors();
		}

		if ($this->_versionDw)
		{
			if ($this->_versionDw->isInsert())
			{
				$this->_versionDw->set('resource_id', 0); // temporary, set later
				$this->_versionDw->set('resource_update_id', 0); // temporary, set later
			}

			$this->_versionDw->preSave();
			$this->_errors += $this->_versionDw->getErrors();
		}

		if ($this->isInsert())
		{
			$this->updateRating(
				intval($this->get('rating_sum')), intval($this->get('rating_count'))
			);
		}
	}

	protected function _postSave()
	{
		$postSaveChanges = array();

		$this->updateCustomFields();

		if ($this->isUpdate() && $this->isChanged('title'))
		{
			$dw = $this->getDescriptionDw();
			$dw->set('title', $this->get('title'), '', array(
				'runVerificationCallback' => false,
				'setAfterPreSave' => true
			));

			$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread', XenForo_DataWriter::ERROR_SILENT);
			if ($threadDw->setExistingData($this->get('discussion_thread_id')) && $threadDw->get('discussion_type') == 'resource')
			{
				$threadTitle = $this->_stripTemplateComponents($threadDw->get('title'), $this->getOption(self::OPTION_DELETE_THREAD_TITLE_TEMPLATE));
				$threadTitle = $this->_stripTemplateComponents($threadTitle, $this->getOption(self::OPTION_PAID_THREAD_TITLE_TEMPLATE));

				if ($threadTitle == $this->getExisting('title'))
				{
					$threadDw->set('title', $this->_getThreadTitle());
					$threadDw->save();
				}
			}
		}

		if ($this->isUpdate() && $this->isChanged('resource_category_id') && $this->get('discussion_thread_id'))
		{
			$catDw = $this->_getCategoryDwForUpdate();

			$nodeId = $this->getOption(self::OPTION_CREATE_THREAD_NODE_ID);
			if ($nodeId === null)
			{
				$nodeId = $catDw->get('thread_node_id');
			}
			$prefixId = $this->getOption(self::OPTION_CREATE_THREAD_PREFIX_ID);
			if ($prefixId === null)
			{
				$prefixId = $catDw->get('thread_prefix_id');
			}

			$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread', XenForo_DataWriter::ERROR_SILENT);
			if ($threadDw->setExistingData($this->get('discussion_thread_id')) && $threadDw->get('discussion_type') == 'resource')
			{
				$threadDw->set('node_id', $nodeId);
				$threadDw->set('prefix_id', $prefixId);
				$threadDw->save();
			}
		}

		if ($this->_isFirstVisible)
		{
			$this->getDescriptionDw();
			if ($this->_descriptionDw && !$this->_descriptionDw->isChanged('post_date'))
			{
				$this->_descriptionDw->set('post_date',
					$this->get('resource_date'), '', array('setAfterPreSave' => true)
				);
			}

			$this->getVersionDw();
			if ($this->_versionDw && !$this->_versionDw->isChanged('release_date'))
			{
				$this->_versionDw->set('release_date',
					$this->get('resource_date'), '', array('setAfterPreSave' => true)
				);
			}
		}

		if ($this->_descriptionDw)
		{
			if ($this->_descriptionDw->isInsert())
			{
				$this->_descriptionDw->set('resource_id', $this->get('resource_id'), '', array('setAfterPreSave' => true));
				$this->_descriptionDw->setResource($this->getMergedData());
				$this->_descriptionDw->save();

				$this->set('description_update_id',
					$this->_descriptionDw->get('resource_update_id'), '', array('setAfterPreSave' => true)
				);
				$postSaveChanges['description_update_id'] = $this->get('description_update_id');
			}
			else
			{
				$this->_descriptionDw->save();
			}
		}

		if ($this->_versionDw)
		{
			if ($this->_versionDw->isInsert())
			{
				$this->_versionDw->set('resource_id', $this->get('resource_id'), '', array('setAfterPreSave' => true));
				$this->_versionDw->set('resource_update_id', $this->get('description_update_id'), '', array('setAfterPreSave' => true));
				$this->_versionDw->save();

				$this->set('current_version_id',
					$this->_versionDw->get('resource_version_id'), '', array('setAfterPreSave' => true)
				);
				$postSaveChanges['current_version_id'] = $this->get('current_version_id');
			}
			else
			{
				$this->_versionDw->save();
			}
		}

		$removed = false;
		if ($this->isChanged('resource_state'))
		{
			if ($this->get('resource_state') == 'visible')
			{
				$this->_resourceMadeVisible($postSaveChanges);
			}
			else if ($this->isUpdate() && $this->getExisting('resource_state') == 'visible')
			{
				$this->_resourceRemoved();
				$removed = true;
			}

			$this->_updateDeletionLog();
			$this->_updateModerationQueue();
		}

		if ($postSaveChanges)
		{
			$this->_db->update('xf_resource', $postSaveChanges,
				'resource_id = ' .  $this->_db->quote($this->get('resource_id'))
			);
		}

		$catDw = $this->_getCategoryDwForUpdate();
		if ($catDw && !$removed)
		{
			// will already be called for removal
			$catDw->resourceUpdate($this);
			$catDw->save();
		}

		if ($this->isUpdate()
			&& ($this->isChanged('resource_category_id')
				|| $this->isChanged('title')
				|| $this->isChanged('tag_line')
				|| $this->isChanged('prefix_id')
			))
		{
			$updateIds = $this->_db->fetchCol('
				SELECT resource_update_id
				FROM xf_resource_update
				WHERE resource_id = ?
			', $this->get('resource_id'));

			$indexer = new XenForo_Search_Indexer();
			$dataHandler = XenForo_Search_DataHandler_Abstract::create('XenResource_Search_DataHandler_Update');
			$dataHandler->quickIndex($indexer, $updateIds);
		}

		if ($this->isUpdate() && $this->isChanged('user_id'))
		{
			if ($this->get('user_id') && $this->get('resource_state') == 'visible' && !$this->isChanged('resource_state'))
			{
				$this->_db->query('
					UPDATE xf_user
					SET resource_count = resource_count + 1
					WHERE user_id = ?
				', $this->get('user_id'));
			}

			if ($this->getExisting('user_id') && $this->getExisting('resource_state') == 'visible')
			{
				$this->_db->query('
					UPDATE xf_user
					SET resource_count = resource_count - 1
					WHERE user_id = ?
				', $this->getExisting('user_id'));
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
			$description = $this->getDescriptionDw();
			if ($description)
			{
				$this->getModelFromCache('XenResource_Model_CategoryWatch')->sendNotificationToWatchUsers(
					$description->getMergedData(), $this->getMergedData()
				);
			}
		}
	}

	public function updateCustomFields()
	{
		if (is_array($this->_updateCustomFields))
		{
			$resourceId = $this->get('resource_id');

			$this->_db->query('DELETE FROM xf_resource_field_value WHERE resource_id = ?', $resourceId);

			foreach ($this->_updateCustomFields AS $fieldId => $value)
			{
				if (is_array($value))
				{
					$value = serialize($value);
				}
				$this->_db->query('
					INSERT INTO xf_resource_field_value
						(resource_id, field_id, field_value)
					VALUES
						(?, ?, ?)
					ON DUPLICATE KEY UPDATE
						field_value = VALUES(field_value)
				', array($resourceId, $fieldId, $value));
			}
		}
	}

	protected function _stripTemplateComponents($string, $template)
	{
		if (!$template) {
			return $string;
		}

		$template = str_replace('\{title\}', '(.*)', preg_quote($template, '/'));

		if (preg_match('/^' . $template . '$/', $string, $match)) {
			return $match[1];
		}

		return $string;
	}

	/**
	 * Post-delete handling.
	 */
	protected function _postDelete()
	{
		$versionIds = $this->_db->fetchCol('
			SELECT resource_version_id
			FROM xf_resource_version
			WHERE resource_id = ?
		', $this->get('resource_id'));
		$this->getModelFromCache('XenForo_Model_Attachment')->deleteAttachmentsFromContentIds(
			'resource_version', $versionIds
		);

		$updateIds = $this->_db->fetchCol('
			SELECT resource_update_id
			FROM xf_resource_update
			WHERE resource_id = ?
		', $this->get('resource_id'));
		$this->getModelFromCache('XenForo_Model_Attachment')->deleteAttachmentsFromContentIds(
			'resource_update', $updateIds
		);
		$this->getModelFromCache('XenForo_Model_Alert')->deleteAlerts(
			'resource_update', $updateIds
		);

		$ratingIds = $this->_db->fetchCol('
			SELECT resource_rating_id
			FROM xf_resource_rating
			WHERE resource_id = ?
		', $this->get('resource_id'));
		$this->getModelFromCache('XenForo_Model_Alert')->deleteAlerts(
			'resource_rating', $ratingIds
		);

		$idQuoted = $this->_db->quote($this->get('resource_id'));
		$this->_db->delete('xf_resource_update', 'resource_id = ' . $idQuoted);
		$this->_db->delete('xf_resource_version', 'resource_id = ' . $idQuoted);
		$this->_db->delete('xf_resource_watch', 'resource_id = ' . $idQuoted);
		$this->_db->delete('xf_resource_feature', 'resource_id = ' . $idQuoted);
		$this->_db->delete('xf_resource_rating', 'resource_id = ' . $idQuoted);

		$indexer = new XenForo_Search_Indexer();
		$indexer->deleteFromIndex('resource_update', $updateIds);

		if ($this->getExisting('resource_state') == 'visible')
		{
			$this->_resourceRemoved();
		}
		$this->_updateDeletionLog(true);
		$this->getModelFromCache('XenForo_Model_ModerationQueue')->deleteFromModerationQueue(
			'resource', $this->get('resource_id')
		);

		$filePath = $this->_getResourceModel()->getResourceIconFilePath($this->get('resource_id'));
		@unlink($filePath);
	}

	protected function _getThreadTitle()
	{
		if (floatval($this->get('price')) > 0 && $this->getOption(self::OPTION_PAID_THREAD_TITLE_TEMPLATE))
		{
			$title = str_replace(
				'{title}', $this->get('title'),
				$this->getOption(self::OPTION_PAID_THREAD_TITLE_TEMPLATE)
			);
		}
		else
		{
			$title = $this->get('title');
		}

		if ($this->get('resource_state') != 'visible' && $this->getOption(self::OPTION_DELETE_THREAD_TITLE_TEMPLATE))
		{
			$title = str_replace(
				'{title}', $this->get('title'),
				$this->getOption(self::OPTION_DELETE_THREAD_TITLE_TEMPLATE)
			);
		}

		return $title;
	}

	protected function _insertDiscussionThread($nodeId, $prefixId = 0)
	{
		if (!$nodeId)
		{
			return false;
		}

		$forum = $this->getModelFromCache('XenForo_Model_Forum')->getForumById($nodeId);
		if (!$forum)
		{
			return false;
		}

		$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread', XenForo_DataWriter::ERROR_SILENT);
		$threadDw->setExtraData(XenForo_DataWriter_Discussion_Thread::DATA_FORUM, $forum);
		$threadDw->bulkSet(array(
			'node_id' => $nodeId,
			'title' => $this->_getThreadTitle(),
			'user_id' => $this->get('user_id'),
			'username' => $this->get('username'),
			'discussion_type' => 'resource',
			'prefix_id' => $prefixId
		));
		$threadDw->set('discussion_state', $this->getModelFromCache('XenForo_Model_Post')->getPostInsertMessageState(array(), $forum));
		$threadDw->setOption(XenForo_DataWriter_Discussion::OPTION_PUBLISH_FEED, false);

		if ($this->_descriptionDw)
		{
			$messageText = $this->_descriptionDw->get('message');

			// note: this doesn't actually strip the BB code - it will fix the BB code in the snippet though
			$parser = XenForo_BbCode_Parser::create(XenForo_BbCode_Formatter_Base::create('XenForo_BbCode_Formatter_BbCode_AutoLink', false));
			$snippet = $parser->render(XenForo_Helper_String::wholeWordTrim($messageText, 500));
		}
		else
		{
			$snippet = '';
		}

		$version = ($this->_versionDw ? $this->_versionDw->get('version_string') : '');

		$message = new XenForo_Phrase('resource_message_create_resource', array(
			'title' => $this->get('title'),
			'tagLine' => $this->get('tag_line'),
			'username' => $this->get('username'),
			'userId' => $this->get('user_id'),
			'snippet' => $snippet,
			'version' => $version,
			'resourceLink' => XenForo_Link::buildPublicLink('canonical:resources', $this->getMergedData())
		), false);

		$postWriter = $threadDw->getFirstMessageDw();
		$postWriter->set('message', $message->render());
		$postWriter->setExtraData(XenForo_DataWriter_DiscussionMessage_Post::DATA_FORUM, $forum);
		$postWriter->setOption(XenForo_DataWriter_DiscussionMessage::OPTION_PUBLISH_FEED, false);

		if (!$threadDw->save())
		{
			return false;
		}

		$this->set('discussion_thread_id',
			$threadDw->get('thread_id'), '', array('setAfterPreSave' => true)
		);
		$postSaveChanges['discussion_thread_id'] = $threadDw->get('thread_id');

		$this->getModelFromCache('XenForo_Model_Thread')->markThreadRead(
			$threadDw->getMergedData(), $forum, XenForo_Application::$time
		);

		$this->getModelFromCache('XenForo_Model_ThreadWatch')->setThreadWatchStateWithUserDefault(
			$this->get('user_id'), $threadDw->get('thread_id'),
			$this->getExtraData(self::DATA_THREAD_WATCH_DEFAULT)
		);

		return $threadDw->get('thread_id');
	}

	protected function _resourceRemoved()
	{
		if ($this->get('discussion_thread_id'))
		{
			$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread', XenForo_DataWriter::ERROR_SILENT);
			if ($threadDw->setExistingData($this->get('discussion_thread_id')) && $threadDw->get('discussion_type') == 'resource')
			{
				switch ($this->getOption(self::OPTION_DELETE_THREAD_ACTION))
				{
					case 'delete':
						$threadDw->set('discussion_state', 'deleted');
						break;

					case 'close':
						$threadDw->set('discussion_open', 0);
						break;
				}

				if ($this->getOption(self::OPTION_DELETE_THREAD_TITLE_TEMPLATE))
				{
					$threadTitle = str_replace(
						'{title}', $threadDw->get('title'),
						$this->getOption(self::OPTION_DELETE_THREAD_TITLE_TEMPLATE)
					);
					$threadDw->set('title', $threadTitle);
				}

				$threadDw->save();

				if ($this->getOption(self::OPTION_DELETE_ADD_POST))
				{
					$forum = $this->getModelFromCache('XenForo_Model_Forum')->getForumById($threadDw->get('node_id'));
					if ($forum)
					{
						$messageState = $this->getModelFromCache('XenForo_Model_Post')->getPostInsertMessageState(
							$threadDw->getMergedData(), $forum
						);
					}
					else
					{
						$messageState = 'visible';
					}

					$user = $this->_getUserModel()->getUserById($this->get('user_id'));
					if ($user)
					{
						$this->set('username', $user['username'], '', array('setAfterPreSave' => true));
					}

					$message = new XenForo_Phrase('resource_message_delete_resource', array(
						'title' => $this->get('title'),
						'tagLine' => $this->get('tag_line'),
						'username' => $this->get('username'),
						'userId' => $this->get('user_id'),
						'resourceLink' => XenForo_Link::buildPublicLink('canonical:resources', $this->getMergedData())
					), false);

					$writer = XenForo_DataWriter::create('XenForo_DataWriter_DiscussionMessage_Post');
					$writer->bulkSet(array(
						'user_id' => $this->get('user_id'),
						'username' => $this->get('username'),
						'message_state' => $messageState,
						'thread_id' => $threadDw->get('thread_id'),
						'message' => strval($message)
					));
					$writer->save();
				}
			}
		}

		if ($this->get('user_id'))
		{
			$this->_db->query('
				UPDATE xf_user
				SET resource_count = resource_count - 1
				WHERE user_id = ?
			', $this->get('user_id'));
		}

		$catDw = $this->_getCategoryDwForUpdate();
		if ($catDw)
		{
			$catDw->resourceRemoved($this);
			$catDw->save();
		}
	}

	protected function _resourceMadeVisible(array &$postSaveChanges)
	{
		if (!$this->get('discussion_thread_id'))
		{
			$catDw = $this->_getCategoryDwForUpdate();

			$nodeId = $this->getOption(self::OPTION_CREATE_THREAD_NODE_ID);
			if ($nodeId === null)
			{
				$nodeId = $catDw->get('thread_node_id');
			}
			$prefixId = $this->getOption(self::OPTION_CREATE_THREAD_PREFIX_ID);
			if ($prefixId === null)
			{
				$prefixId = $catDw->get('thread_prefix_id');
			}

			$threadId = $this->_insertDiscussionThread($nodeId, $prefixId);
			if ($threadId)
			{
				$this->set('discussion_thread_id',
					$threadId, '', array('setAfterPreSave' => true)
				);
				$postSaveChanges['discussion_thread_id'] = $threadId;
			}
		}
		else
		{
			$threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread', XenForo_DataWriter::ERROR_SILENT);
			if ($threadDw->setExistingData($this->get('discussion_thread_id')) && $threadDw->get('discussion_type') == 'resource')
			{
				switch ($this->getOption(self::OPTION_DELETE_THREAD_ACTION))
				{
					case 'delete':
						$threadDw->set('discussion_state', 'visible');
						break;

					case 'close':
						$threadDw->set('discussion_open', 1);
						break;
				}

				$title = $this->_stripTemplateComponents($threadDw->get('title'), $this->getOption(self::OPTION_DELETE_THREAD_TITLE_TEMPLATE));
				$threadDw->set('title', $title);
				$threadDw->save();
			}
		}

		if ($this->get('user_id') && $this->get('resource_state') == 'visible')
		{
			$this->_db->query('
				UPDATE xf_user
				SET resource_count = resource_count + 1
				WHERE user_id = ?
			', $this->get('user_id'));
		}
	}

	protected function _updateDeletionLog($hardDelete = false)
	{
		if ($hardDelete
			|| ($this->isChanged('resource_state') && $this->getExisting('resource_state') == 'deleted')
		)
		{
			$this->getModelFromCache('XenForo_Model_DeletionLog')->removeDeletionLog(
				'resource', $this->get('resource_id')
			);
		}

		if ($this->isChanged('resource_state') && $this->get('resource_state') == 'deleted')
		{
			$reason = $this->getExtraData(self::DATA_DELETE_REASON);
			$this->getModelFromCache('XenForo_Model_DeletionLog')->logDeletion(
				'resource', $this->get('resource_id'), $reason
			);
		}
	}

	protected function _updateModerationQueue()
	{
		if (!$this->isChanged('resource_state'))
		{
			return;
		}

		if ($this->get('resource_state') == 'moderated')
		{
			$this->getModelFromCache('XenForo_Model_ModerationQueue')->insertIntoModerationQueue(
				'resource', $this->get('resource_id'), $this->get('resource_date')
			);
		}
		else if ($this->getExisting('resource_state') == 'moderated')
		{
			$this->getModelFromCache('XenForo_Model_ModerationQueue')->deleteFromModerationQueue(
				'resource', $this->get('resource_id')
			);
		}
	}

	public function updateUpdateCount($adjust = null)
	{
		if ($adjust === null)
		{
			$this->set('update_count', $this->_db->fetchOne('
				SELECT COUNT(*)
				FROM xf_resource_update
				WHERE resource_id = ?
					AND resource_update_id <> ?
					AND message_state = \'visible\'
			', array($this->get('resource_id'), $this->get('description_update_id'))));
		}
		else
		{
			$this->set('update_count', $this->get('update_count') + $adjust);
		}
	}

	public function updateReviewCount($adjust = null)
	{
		if ($adjust === null)
		{
			$this->set('review_count', $this->_db->fetchOne('
				SELECT COUNT(*)
				FROM xf_resource_rating
				WHERE resource_id = ?
					AND is_review = 1
					AND rating_state = \'visible\'
			', $this->get('resource_id')));
		}
		else
		{
			$this->set('review_count', $this->get('review_count') + $adjust);
		}
	}

	public function updateDownloadCount($adjust = null)
	{
		if ($adjust === null)
		{
			$this->set('download_count', $this->_db->fetchOne('
				SELECT COUNT(DISTINCT user_id)
				FROM xf_resource_download
				WHERE resource_id = ?
			', $this->get('resource_id')));
		}
		else
		{
			$this->set('download_count', $this->get('download_count') + $adjust);
		}
	}

	public function updateRating($adjustSum = null, $adjustCount = null)
	{
		if ($adjustSum === null && $adjustCount === null)
		{
			$rating = $this->_db->fetchRow('
				SELECT COUNT(*) AS total, SUM(rating) AS sum
				FROM xf_resource_rating
				WHERE resource_id = ?
					AND count_rating = 1
					AND rating_state = \'visible\'
			', $this->get('resource_id'));

			$this->set('rating_sum', $rating['sum']);
			$this->set('rating_count', $rating['total']);
		}
		else
		{
			if ($adjustSum !== null)
			{
				$this->set('rating_sum', $this->get('rating_sum') + $adjustSum);
			}
			if ($adjustCount !== null)
			{
				$this->set('rating_count', $this->get('rating_count') + $adjustCount);
			}
		}

		if ($this->get('rating_count'))
		{
			$this->set('rating_avg', $this->get('rating_sum') / $this->get('rating_count'));
		}
		else
		{
			$this->set('rating_avg', 0);
		}

		$this->set('rating_weighted', $this->_getResourceModel()->getWeightedRating(
			$this->get('rating_count'), $this->get('rating_sum')
		));
	}

	public function updateLastUpdate($lastUpdate = null)
	{
		if ($lastUpdate === null)
		{
			// do a recalculation from the DB
			$lastUpdate = intval($this->_db->fetchOne($this->_db->limit(
				'
					SELECT post_date
					FROM xf_resource_update
					WHERE message_state = \'visible\'
						AND resource_id = ?
					ORDER BY post_date DESC
				', 1
			), $this->get('resource_id')));

			$this->set('last_update', $lastUpdate);
		}
		else if ($lastUpdate > $this->get('last_update'))
		{
			$this->set('last_update', $lastUpdate);
		}
	}

	public function updateCurrentVersion($currentVersionId = null)
	{
		if ($currentVersionId === null)
		{
			$currentVersionId = $this->_db->fetchOne($this->_db->limit(
				'
					SELECT resource_version_id
					FROM xf_resource_version
					WHERE resource_id = ?
						AND version_state = \'visible\'
					ORDER BY release_date DESC
				', 1
			), $this->get('resource_id'));
		}

		$this->set('current_version_id', $currentVersionId);
	}

	public function rebuildCounters()
	{
		$this->updateUpdateCount();
		$this->updateReviewCount();
		$this->updateRating();
		$this->updateLastUpdate();
		$this->updateCurrentVersion();

		if ($this->get('user_id')) {
			$user = $this->getModelFromCache('XenForo_Model_User')->getUserById($this->get('user_id'));
			if ($user) {
				$this->set('username', $user['username']);
			} else {
				$this->set('user_id', 0);
			}
		}
	}

	public function getDescriptionDw()
	{
		if (!$this->_descriptionDw)
		{
			$this->_descriptionDw = XenForo_DataWriter::create('XenResource_DataWriter_Update', $this->_errorHandler);
			if ($updateId = $this->get('description_update_id'))
			{
				$this->_descriptionDw->setExistingData($updateId);
			}
		}

		return $this->_descriptionDw;
	}

	public function getVersionDw()
	{
		if (!$this->_versionDw)
		{
			$this->_versionDw = XenForo_DataWriter::create('XenResource_DataWriter_Version', $this->_errorHandler);
			if ($versionId = $this->get('current_version_id'))
			{
				$this->_versionDw->setExistingData($versionId);
			}
		}

		return $this->_versionDw;
	}

	/**
	 * @return XenResource_DataWriter_Category|bool
	 */
	protected function _getCategoryDwForUpdate()
	{
		$dw = XenForo_DataWriter::create('XenResource_DataWriter_Category', XenForo_DataWriter::ERROR_SILENT);
		if ($dw->setExistingData($this->get('resource_category_id')))
		{
			return $dw;
		}
		else
		{
			return false;
		}
	}

	/**
	 * @return XenResource_Model_Resource
	 */
	protected function _getResourceModel()
	{
		return $this->getModelFromCache('XenResource_Model_Resource');
	}

	/**
	 * @return XenResource_Model_Category
	 */
	protected function _getCategoryModel()
	{
		return $this->getModelFromCache('XenResource_Model_Category');
	}

	/**
	 * @return XenResource_Model_ResourceField
	 */
	protected function _getFieldModel()
	{
		return $this->getModelFromCache('XenResource_Model_ResourceField');
	}

	/**
	 * @return XenResource_Model_Prefix
	 */
	protected function _getPrefixModel()
	{
		return $this->getModelFromCache('XenResource_Model_Prefix');
	}
}