<?php

class XenResource_DataWriter_ResourceField extends XenForo_DataWriter
{
	/**
	 * Constant for extra data that holds the value for the phrase
	 * that is the title of this field.
	 *
	 * This value is required on inserts.
	 *
	 * @var string
	 */
	const DATA_TITLE = 'phraseTitle';

	/**
	 * Constant for extra data that holds the value for the phrase
	 * that is the description of this field.
	 *
	 * @var string
	 */
	const DATA_DESCRIPTION = 'phraseDescription';

	const DATA_CATEGORY_IDS = 'categoryIds';

	/**
	 * Title of the phrase that will be created when a call to set the
	 * existing data fails (when the data doesn't exist).
	 *
	 * @var string
	 */
	protected $_existingDataErrorPhrase = 'requested_field_not_found';

	/**
	 * List of choices, if this is a choice field. Interface to set field_choices properly.
	 *
	 * @var null|array
	 */
	protected $_fieldChoices = null;

	/**
	* Gets the fields that are defined for the table. See parent for explanation.
	*
	* @return array
	*/
	protected function _getFields()
	{
		return array(
			'xf_resource_field' => array(
				'field_id'              => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 25,
						'verification' => array('$this', '_verifyFieldId'), 'requiredError' => 'please_enter_valid_field_id'
				),
				'display_group'         => array('type' => self::TYPE_STRING, 'default' => 'above_info',
					'allowedValues' => array('above_info', 'below_info', 'extra_tab', 'new_tab')
				),
				'display_order'         => array('type' => self::TYPE_UINT, 'default' => 1),
				'field_type'            => array('type' => self::TYPE_STRING, 'default' => 'textbox',
					'allowedValues' => array('textbox', 'textarea', 'bbcode', 'select', 'radio', 'checkbox', 'multiselect')
				),
				'field_choices'         => array('type' => self::TYPE_SERIALIZED, 'default' => ''),
				'match_type'            => array('type' => self::TYPE_STRING, 'default' => 'none',
					'allowedValues' => array('none', 'number', 'alphanumeric', 'email', 'url', 'regex', 'callback')
				),
				'match_regex'           => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 250),
				'match_callback_class'  => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 75),
				'match_callback_method' => array('type' => self::TYPE_STRING, 'default' => '', 'maxLength' => 75),
				'max_length'            => array('type' => self::TYPE_UINT, 'default' => 0),
				'required'              => array('type' => self::TYPE_BOOLEAN, 'default' => 0),
				'display_template'      => array('type' => self::TYPE_STRING, 'default' => '')
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
		if (!$id = $this->_getExistingPrimaryKey($data, 'field_id'))
		{
			return false;
		}

		return array('xf_resource_field' => $this->_getFieldModel()->getResourceFieldById($id));
	}

	/**
	* Gets SQL condition to update the existing record.
	*
	* @return string
	*/
	protected function _getUpdateCondition($tableName)
	{
		return 'field_id = ' . $this->_db->quote($this->getExisting('field_id'));
	}

	/**
	 * Verifies that the ID contains valid characters and does not already exist.
	 *
	 * @param $id
	 *
	 * @return boolean
	 */
	protected function _verifyFieldId(&$id)
	{
		if (preg_match('/[^a-zA-Z0-9_]/', $id))
		{
			$this->error(new XenForo_Phrase('please_enter_an_id_using_only_alphanumeric'), 'field_id');
			return false;
		}

		if ($id !== $this->getExisting('field_id') && $this->_getFieldModel()->getResourceFieldById($id))
		{
			$this->error(new XenForo_Phrase('field_ids_must_be_unique'), 'field_id');
			return false;
		}

		return true;
	}

	/**
	 * Sets the choices for this field.
	 *
	 * @param array $choices [choice key] => text
	 */
	public function setFieldChoices(array $choices)
	{
		foreach ($choices AS $value => &$text)
		{
			if ($value === '')
			{
				unset($choices[$value]);
				continue;
			}

			$text = strval($text);

			if ($text === '')
			{
				$this->error(new XenForo_Phrase('please_enter_text_for_each_choice'), 'field_choices');
				return false;
			}

			if (preg_match('#[^a-z0-9_]#i', $value))
			{
				$this->error(new XenForo_Phrase('please_enter_an_id_using_only_alphanumeric'), 'field_choices');
				return false;
			}

			if (strlen($value) > 25)
			{
				$this->error(new XenForo_Phrase('please_enter_value_using_x_characters_or_fewer', array('count' => 25)));
				return false;
			}
		}

		$this->_fieldChoices = $choices;
		$this->set('field_choices', $choices);

		return true;
	}

	/**
	 * Pre-save behaviors.
	 */
	protected function _preSave()
	{
		if ($this->isChanged('match_callback_class') || $this->isChanged('match_callback_method'))
		{
			$class = $this->get('match_callback_class');
			$method = $this->get('match_callback_method');

			if (!$class || !$method)
			{
				$this->set('match_callback_class', '');
				$this->set('match_callback_method', '');
			}
			else if (!XenForo_Application::autoload($class) || !method_exists($class, $method))
			{
				$this->error(new XenForo_Phrase('please_enter_valid_callback_method_x_y', array('class' => $class, 'method' => $method)), 'callback_method');
			}
		}

		if ($this->isUpdate() && $this->isChanged('field_type'))
		{
			$typeMap = $this->_getFieldModel()->getResourceFieldTypeMap();
			if ($typeMap[$this->get('field_type')] != $typeMap[$this->getExisting('field_type')])
			{
				$this->error(new XenForo_Phrase('you_may_not_change_field_to_different_type_after_it_has_been_created'), 'field_type');
			}
		}

		if (in_array($this->get('field_type'), array('select', 'radio', 'checkbox', 'multiselect')))
		{
			if (($this->isInsert() && !$this->_fieldChoices) || (is_array($this->_fieldChoices) && !$this->_fieldChoices))
			{
				$this->error(new XenForo_Phrase('please_enter_at_least_one_choice'), 'field_choices', false);
			}
		}
		else
		{
			$this->setFieldChoices(array());
		}

		$titlePhrase = $this->getExtraData(self::DATA_TITLE);
		if ($titlePhrase !== null && strlen($titlePhrase) == 0)
		{
			$this->error(new XenForo_Phrase('please_enter_valid_title'), 'title');
		}
	}

	/**
	 * Post-save handling.
	 */
	protected function _postSave()
	{
		$fieldId = $this->get('field_id');

		if ($this->isUpdate() && $this->isChanged('field_id'))
		{
			$this->_renameMasterPhrase(
				$this->_getTitlePhraseName($this->getExisting('field_id')),
				$this->_getTitlePhraseName($fieldId)
			);

			$this->_renameMasterPhrase(
				$this->_getDescriptionPhraseName($this->getExisting('field_id')),
				$this->_getDescriptionPhraseName($fieldId)
			);
		}

		$titlePhrase = $this->getExtraData(self::DATA_TITLE);
		if ($titlePhrase !== null)
		{
			$this->_insertOrUpdateMasterPhrase(
				$this->_getTitlePhraseName($fieldId), $titlePhrase,
				'', array('global_cache' => 1)
			);
		}

		$descriptionPhrase = $this->getExtraData(self::DATA_DESCRIPTION);
		if ($descriptionPhrase !== null)
		{
			$this->_insertOrUpdateMasterPhrase(
				$this->_getDescriptionPhraseName($fieldId), $descriptionPhrase
			);
		}

		if (is_array($this->_fieldChoices))
		{
			$this->_deleteExistingChoicePhrases();

			foreach ($this->_fieldChoices AS $choice => $text)
			{
				$this->_insertOrUpdateMasterPhrase(
					$this->_getChoicePhraseName($fieldId, $choice), $text,
					'', array('global_cache' => 1)
				);
			}
		}

		$updateCategoryIds = $this->_getFieldModel()->getCategoryAssociationsByField($this->get('field_id'));
		$newCategoryIds = $this->getExtraData(self::DATA_CATEGORY_IDS);
		if (is_array($newCategoryIds))
		{
			$updateCategoryIds = array_merge($updateCategoryIds, $this->_updateCategoryAssociations($newCategoryIds));
		}

		$this->_getFieldModel()->rebuildFieldCategoryAssociationCache($updateCategoryIds);

		$this->_rebuildResourceFieldCache();
	}

	protected function _updateCategoryAssociations(array $categoryIds)
	{
		$categoryIds = array_unique($categoryIds);

		$emptyNodeKey = array_search(0, $categoryIds);
		if ($emptyNodeKey !== false)
		{
			unset($categoryIds[$emptyNodeKey]);
		}

		$db = $this->_db;
		$fieldId = $this->get('field_id');

		$db->delete('xf_resource_field_category', 'field_id = ' . $db->quote($fieldId));

		foreach ($categoryIds AS $categoryId)
		{
			$db->insert('xf_resource_field_category', array(
				'field_id' => $fieldId,
				'resource_category_id' => $categoryId
			));
		}

		return $categoryIds;
	}

	/**
	 * Post-delete behaviors.
	 */
	protected function _postDelete()
	{
		$fieldId = $this->get('field_id');
		$updateCategoryIds = $this->_getFieldModel()->getCategoryAssociationsByField($this->get('field_id'));

		$this->_deleteMasterPhrase($this->_getTitlePhraseName($fieldId));
		$this->_deleteMasterPhrase($this->_getDescriptionPhraseName($fieldId));
		$this->_deleteExistingChoicePhrases();

		$this->_db->delete('xf_resource_field_value', 'field_id = ' . $this->_db->quote($fieldId));
		// note the resource caches aren't rebuilt here; this shouldn't be an issue as we don't enumerate them

		$this->_db->delete('xf_resource_field_category', 'field_id = ' . $this->_db->quote($fieldId));

		$this->_getFieldModel()->rebuildFieldCategoryAssociationCache($updateCategoryIds);
		$this->_rebuildResourceFieldCache();
	}

	/**
	 * Deletes all phrases for existing choices.
	 */
	protected function _deleteExistingChoicePhrases()
	{
		$fieldId = $this->get('field_id');

		$existingChoices = $this->getExisting('field_choices');
		if ($existingChoices && $existingChoices = @unserialize($existingChoices))
		{
			foreach ($existingChoices AS $choice => $text)
			{
				$this->_deleteMasterPhrase($this->_getChoicePhraseName($fieldId, $choice));
			}
		}
	}

	/**
	 * Gets the name of the title phrase for this field.
	 *
	 * @param string $id
	 *
	 * @return string
	 */
	protected function _getTitlePhraseName($id)
	{
		return $this->_getFieldModel()->getResourceFieldTitlePhraseName($id);
	}

	/**
	 * Gets the name of the description phrase for this field.
	 *
	 * @param string $id
	 *
	 * @return string
	 */
	protected function _getDescriptionPhraseName($id)
	{
		return $this->_getFieldModel()->getResourceFieldDescriptionPhraseName($id);
	}

	/**
	 * Gets the name of the choice phrase for a value in this field.
	 *
	 * @param string $fieldId
	 * @param string $choice
	 *
	 * @return string
	 */
	protected function _getChoicePhraseName($fieldId, $choice)
	{
		return $this->_getFieldModel()->getResourceFieldChoicePhraseName($fieldId, $choice);
	}

	protected function _rebuildResourceFieldCache()
	{
		return $this->_getFieldModel()->rebuildResourceFieldCache();
	}

	/**
	 * @return XenResource_Model_ResourceField
	 */
	protected function _getFieldModel()
	{
		return $this->getModelFromCache('XenResource_Model_ResourceField');
	}
}