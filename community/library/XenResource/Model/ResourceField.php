<?php

class XenResource_Model_ResourceField extends XenForo_Model
{
	/**
	 * Gets a custom resource field by ID.
	 *
	 * @param string $fieldId
	 *
	 * @return array|false
	 */
	public function getResourceFieldById($fieldId)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM xf_resource_field
			WHERE field_id = ?
		', $fieldId);
	}

	public function getResourceFieldsInCategories(array $categoryIds)
	{
		if (!$categoryIds)
		{
			return array();
		}

		$db = $this->_getDb();

		return $db->fetchAll("
			SELECT field.*, field_category.resource_category_id
			FROM xf_resource_field AS field
			INNER JOIN xf_resource_field_category AS field_category ON
				(field.field_id = field_category.field_id)
			WHERE field_category.resource_category_id IN (" . $db->quote($categoryIds) . ")
			ORDER BY field.display_order
		");
	}

	public function getFieldIdsInCategory($categoryId)
	{
		return $this->_getDb()->fetchPairs("
			SELECT field_id, field_id
			FROM xf_resource_field_category
			WHERE resource_category_id = ?
		", $categoryId);
	}

	public function getResourceFieldsForEdit($categoryId, $resourceId = 0)
	{
		$fetchOptions = array(
			'categoryId' => $categoryId,
			'valueResourceId' => $resourceId
		);
		return $this->getResourceFields(array(), $fetchOptions);
	}

	/**
	 * Gets custom resource fields that match the specified criteria.
	 *
	 * @param array $conditions
	 * @param array $fetchOptions
	 *
	 * @return array [field id] => info
	 */
	public function getResourceFields(array $conditions = array(), array $fetchOptions = array())
	{
		$whereClause = $this->prepareResourceFieldConditions($conditions, $fetchOptions);
		$joinOptions = $this->prepareResourceFieldFetchOptions($fetchOptions);

		return $this->fetchAllKeyed('
			SELECT resource_field.*
				' . $joinOptions['selectFields'] . '
			FROM xf_resource_field AS resource_field
			' . $joinOptions['joinTables'] . '
			WHERE ' . $whereClause . '
			ORDER BY resource_field.display_group, resource_field.display_order
		', 'field_id');
	}

	/**
	 * Prepares a set of conditions to select fields against.
	 *
	 * @param array $conditions List of conditions.
	 * @param array $fetchOptions The fetch options that have been provided. May be edited if criteria requires.
	 *
	 * @return string Criteria as SQL for where clause
	 */
	public function prepareResourceFieldConditions(array $conditions, array &$fetchOptions)
	{
		$db = $this->_getDb();
		$sqlConditions = array();

		return $this->getConditionsForClause($sqlConditions);
	}

	/**
	 * Prepares join-related fetch options.
	 *
	 * @param array $fetchOptions
	 *
	 * @return array Containing 'selectFields' and 'joinTables' keys.
	 */
	public function prepareResourceFieldFetchOptions(array $fetchOptions)
	{
		$selectFields = '';
		$joinTables = '';

		$db = $this->_getDb();

		if (!empty($fetchOptions['categoryId']))
		{
			$joinTables .= '
				INNER JOIN xf_resource_field_category AS field_category ON
					(field_category.field_id = resource_field.field_id AND field_category.resource_category_id = ' . $db->quote($fetchOptions['categoryId']) . ')';
		}

		if (!empty($fetchOptions['valueResourceId']))
		{
			$selectFields .= ',
				field_value.field_value';
			$joinTables .= '
				LEFT JOIN xf_resource_field_value AS field_value ON
					(field_value.field_id = resource_field.field_id AND field_value.resource_id = ' . $db->quote($fetchOptions['valueResourceId']) . ')';
		}

		return array(
			'selectFields' => $selectFields,
			'joinTables'   => $joinTables
		);
	}

	/**
	 * Groups resource fields by their display group.
	 *
	 * @param array $fields
	 *
	 * @return array [display group][key] => info
	 */
	public function groupResourceFields(array $fields)
	{
		$return = array();

		foreach ($fields AS $fieldId => $field)
		{
			$return[$field['display_group']][$fieldId] = $field;
		}

		return $return;
	}

	/**
	 * Prepares a resource field for display.
	 *
	 * @param array $field
	 * @param boolean $getFieldChoices If true, gets the choice options for this field (as phrases)
	 * @param mixed $fieldValue If not null, the value for the field; if null, pulled from field_value
	 * @param boolean $valueSaved If true, considers the value passed to be saved; should be false on registration
	 *
	 * @return array Prepared field
	 */
	public function prepareResourceField(array $field, $getFieldChoices = false, $fieldValue = null, $valueSaved = true)
	{
		$field['isMultiChoice'] = ($field['field_type'] == 'checkbox' || $field['field_type'] == 'multiselect');
		$field['isChoice'] = ($field['isMultiChoice'] || $field['field_type'] == 'radio' || $field['field_type'] == 'select');

		if ($fieldValue === null && isset($field['field_value']))
		{
			$fieldValue = $field['field_value'];
		}
		if ($field['isMultiChoice'])
		{
			if (is_string($fieldValue))
			{
				$fieldValue = @unserialize($fieldValue);
			}
			else if (!is_array($fieldValue))
			{
				$fieldValue = array();
			}
		}
		$field['field_value'] = $fieldValue;

		$field['title'] = new XenForo_Phrase($this->getResourceFieldTitlePhraseName($field['field_id']));
		$field['description'] = new XenForo_Phrase($this->getResourceFieldDescriptionPhraseName($field['field_id']));

		$field['hasValue'] = $valueSaved && ((is_string($fieldValue) && $fieldValue !== '') || (!is_string($fieldValue) && $fieldValue));

		if ($getFieldChoices)
		{
			$field['fieldChoices'] = $this->getResourceFieldChoices($field['field_id'], $field['field_choices']);
		}

		return $field;
	}

	/**
	 * Prepares a list of resource fields for display.
	 *
	 * @param array $fields
	 * @param boolean $getFieldChoices If true, gets the choice options for these fields (as phrases)
	 * @param array $fieldValues List of values for the specified fields; if skipped, pulled from field_value in array
	 * @param boolean $valueSaved If true, considers the value passed to be saved; should be false on registration
	 *
	 * @return array
	 */
	public function prepareResourceFields(array $fields, $getFieldChoices = false, array $fieldValues = array(), $valueSaved = true)
	{
		foreach ($fields AS &$field)
		{
			$value = isset($fieldValues[$field['field_id']]) ? $fieldValues[$field['field_id']] : null;
			$field = $this->prepareResourceField($field, $getFieldChoices, $value, $valueSaved);
		}

		return $fields;
	}

	/**
	 * Gets the field choices for the given field.
	 *
	 * @param string $fieldId
	 * @param string|array $choices Serialized string or array of choices; key is choide ID
	 * @param boolean $master If true, gets the master phrase values; otherwise, phrases
	 *
	 * @return array Choices
	 */
	public function getResourceFieldChoices($fieldId, $choices, $master = false)
	{
		if (!is_array($choices))
		{
			$choices = ($choices ? @unserialize($choices) : array());
		}

		if (!$master)
		{
			foreach ($choices AS $value => &$text)
			{
				$text = new XenForo_Phrase($this->getResourceFieldChoicePhraseName($fieldId, $value));
			}
		}

		return $choices;
	}

	/**
	 * Verifies that the value for the specified field is valid.
	 *
	 * @param array $field
	 * @param mixed $value
	 * @param mixed $error Returned error message
	 *
	 * @return boolean
	 */
	public function verifyResourceFieldValue(array $field, &$value, &$error = '')
	{
		$error = false;

		switch ($field['field_type'])
		{
			case 'textbox':
				$value = preg_replace('/\r?\n/', ' ', strval($value));
				// break missing intentionally

			case 'textarea':
			case 'bbcode':
				$value = trim(strval($value));

				if ($field['field_type'] == 'bbcode')
				{
					$value = XenForo_Helper_String::autoLinkBbCode($value);
				}

				if ($field['max_length'] && utf8_strlen($value) > $field['max_length'])
				{
					$error = new XenForo_Phrase('please_enter_value_using_x_characters_or_fewer', array('count' => $field['max_length']));
					return false;
				}

				$matched = true;

				if ($value !== '')
				{
					switch ($field['match_type'])
					{
						case 'number':
							$matched = preg_match('/^[0-9]+(\.[0-9]+)?$/', $value);
							break;

						case 'alphanumeric':
							$matched = preg_match('/^[a-z0-9_]+$/i', $value);
							break;

						case 'email':
							$matched = Zend_Validate::is($value, 'EmailAddress');
							break;

						case 'url':
							if ($value === 'http://')
							{
								$value = '';
								break;
							}
							if (substr(strtolower($value), 0, 4) == 'www.')
							{
								$value = 'http://' . $value;
							}
							$matched = Zend_Uri::check($value);
							break;

						case 'regex':
							$matched = preg_match('#' . str_replace('#', '\#', $field['match_regex']) . '#sU', $value);
							break;

						case 'callback':
							$matched = call_user_func_array(
								array($field['match_callback_class'], $field['match_callback_method']),
								array($field, &$value, &$error)
							);

						default:
							// no matching
					}
				}

				if (!$matched)
				{
					if (!$error)
					{
						$error = new XenForo_Phrase('please_enter_value_that_matches_required_format');
					}
					return false;
				}
				break;

			case 'radio':
			case 'select':
				$choices = unserialize($field['field_choices']);
				$value = strval($value);

				if (!isset($choices[$value]))
				{
					$value = '';
				}
				break;

			case 'checkbox':
			case 'multiselect':
				$choices = unserialize($field['field_choices']);
				if (!is_array($value))
				{
					$value = array();
				}

				$newValue = array();

				foreach ($value AS $key => $choice)
				{
					$choice = strval($choice);
					if (isset($choices[$choice]))
					{
						$newValue[$choice] = $choice;
					}
				}

				$value = $newValue;
				break;
		}

		return true;
	}

	/**
	 * Gets the possible resource field groups. Used to display in form in ACP.
	 *
	 * @return array [group] => keys: value, label, hint (optional)
	 */
	public function getResourceFieldGroups()
	{
		return array(
			'above_info' => array(
				'value' => 'above_info',
				'label' => new XenForo_Phrase('above_resource_description')
			),
			'below_info' => array(
				'value' => 'below_info',
				'label' => new XenForo_Phrase('below_resource_description')
			),
			'extra_tab' => array(
				'value' => 'extra_tab',
				'label' => new XenForo_Phrase('extra_information_tab')
			),
			'new_tab' => array(
				'value' => 'new_tab',
				'label' => new XenForo_Phrase('own_tab')
			)
		);
	}

	/**
	 * Gets the possible resource field types.
	 *
	 * @return array [type] => keys: value, label, hint (optional)
	 */
	public function getResourceFieldTypes()
	{
		return array(
			'textbox' => array(
				'value' => 'textbox',
				'label' => new XenForo_Phrase('single_line_text_box')
			),
			'textarea' => array(
				'value' => 'textarea',
				'label' => new XenForo_Phrase('multi_line_text_box')
			),
			'bbcode' => array(
				'value' => 'bbcode',
				'label' => new XenForo_Phrase('rich_text_box'),
			),
			'select' => array(
				'value' => 'select',
				'label' => new XenForo_Phrase('drop_down_selection')
			),
			'radio' => array(
				'value' => 'radio',
				'label' => new XenForo_Phrase('radio_buttons')
			),
			'checkbox' => array(
				'value' => 'checkbox',
				'label' => new XenForo_Phrase('check_boxes')
			),
			'multiselect' => array(
				'value' => 'multiselect',
				'label' => new XenForo_Phrase('multiple_choice_drop_down_selection')
			)
		);
	}

	/**
	 * Maps resource fields to their high level type "group". Field types can be changed only
	 * within the group.
	 *
	 * @return array [field type] => type group
	 */
	public function getResourceFieldTypeMap()
	{
		return array(
			'textbox' => 'text',
			'textarea' => 'text',
			'bbcode' => 'text',
			'radio' => 'single',
			'select' => 'single',
			'checkbox' => 'multiple',
			'multiselect' => 'multiple'
		);
	}

	/**
	 * Gets the field's title phrase name.
	 *
	 * @param string $fieldId
	 *
	 * @return string
	 */
	public function getResourceFieldTitlePhraseName($fieldId)
	{
		return 'resource_field_' . $fieldId;
	}

	/**
	 * Gets the field's description phrase name.
	 *
	 * @param string $fieldId
	 *
	 * @return string
	 */
	public function getResourceFieldDescriptionPhraseName($fieldId)
	{
		return 'resource_field_' . $fieldId . '_desc';
	}

	/**
	 * Gets a field choices's phrase name.
	 *
	 * @param string $fieldId
	 * @param string $choice
	 *
	 * @return string
	 */
	public function getResourceFieldChoicePhraseName($fieldId, $choice)
	{
		return 'resource_field_' . $fieldId . '_choice_' . $choice;
	}

	/**
	 * Gets a field's master title phrase text.
	 *
	 * @param string $id
	 *
	 * @return string
	 */
	public function getResourceFieldMasterTitlePhraseValue($id)
	{
		$phraseName = $this->getResourceFieldTitlePhraseName($id);
		return $this->_getPhraseModel()->getMasterPhraseValue($phraseName);
	}

	/**
	 * Gets a field's master description phrase text.
	 *
	 * @param string $id
	 *
	 * @return string
	 */
	public function getResourceFieldMasterDescriptionPhraseValue($id)
	{
		$phraseName = $this->getResourceFieldDescriptionPhraseName($id);
		return $this->_getPhraseModel()->getMasterPhraseValue($phraseName);
	}

	public function getCategoryAssociationsByField($fieldId)
	{
		return $this->_getDb()->fetchCol('
			SELECT resource_category_id
			FROM xf_resource_field_category
			WHERE field_id = ?
		', $fieldId);
	}

	public function rebuildFieldCategoryAssociationCache($categoryIds)
	{
		if (!is_array($categoryIds))
		{
			$categoryIds = array($categoryIds);
		}
		if (!$categoryIds)
		{
			return;
		}

		$db = $this->_getDb();

		$newCache = array();

		foreach ($this->getResourceFieldsInCategories($categoryIds) AS $field)
		{
			$newCache[$field['resource_category_id']][$field['display_group']][$field['field_id']] = $field['field_id'];
		}

		XenForo_Db::beginTransaction($db);

		foreach ($categoryIds AS $categoryId)
		{
			$update = (isset($newCache[$categoryId]) ? serialize($newCache[$categoryId]) : '');

			$db->update('xf_resource_category', array(
				'field_cache' => $update
			), 'resource_category_id = ' . $db->quote($categoryId));
		}

		XenForo_Db::commit($db);
	}

	/**
	 * Gets the resource field values for the given resource.
	 *
	 * @param integer $resourceId
	 *
	 * @return array [field id] => value (may be string or array)
	 */
	public function getResourceFieldValues($resourceId)
	{
		$fields = $this->_getDb()->fetchAll('
			SELECT v.*, field.field_type
			FROM xf_resource_field_value AS v
			INNER JOIN xf_resource_field AS field ON (field.field_id = v.field_id)
			WHERE v.resource_id = ?
		', $resourceId);

		$values = array();
		foreach ($fields AS $field)
		{
			if ($field['field_type'] == 'checkbox' || $field['field_type'] == 'multiselect')
			{
				$values[$field['field_id']] = @unserialize($field['field_value']);
			}
			else
			{
				$values[$field['field_id']] = $field['field_value'];
			}
		}

		return $values;
	}

	public function getResourceFieldCache()
	{
		if (XenForo_Application::isRegistered('resourceFieldsInfo'))
		{
			return XenForo_Application::get('resourceFieldsInfo');
		}

		$info = $this->_getDataRegistryModel()->get('resourceFieldsInfo');
		if (!is_array($info))
		{
			$info = $this->rebuildResourceFieldCache();
		}
		XenForo_Application::set('resourceFieldsInfo', $info);

		return $info;
	}

	/**
	 * Rebuilds the cache of resource field info for front-end display
	 *
	 * @return array
	 */
	public function rebuildResourceFieldCache()
	{
		$cache = array();
		foreach ($this->getResourceFields() AS $fieldId => $field)
		{
			$cache[$fieldId] = XenForo_Application::arrayFilterKeys($field, array(
				'field_id',
				'field_type',
				'display_group',
			));

			foreach (array('display_template') AS $optionalField)
			{
				if (!empty($field[$optionalField]))
				{
					$cache[$fieldId][$optionalField] = $field[$optionalField];
				}
			}
		}

		$this->_getDataRegistryModel()->set('resourceFieldsInfo', $cache);
		return $cache;
	}

	/**
	 * @return XenForo_Model_Phrase
	 */
	protected function _getPhraseModel()
	{
		return $this->getModelFromCache('XenForo_Model_Phrase');
	}
}