<?php

class XenResource_ControllerAdmin_Field extends XenForo_ControllerAdmin_Abstract
{
	protected function _preDispatch($action)
	{
		$this->assertAdminPermission('resourceManager');
	}

	/**
	 * Displays a list of custom resource fields.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionIndex()
	{
		$fieldModel = $this->_getFieldModel();

		$fields = $fieldModel->prepareResourceFields($fieldModel->getResourceFields());

		$viewParams = array(
			'fieldsGrouped' => $fieldModel->groupResourceFields($fields),
			'fieldCount' => count($fields),
			'fieldGroups' => $fieldModel->getResourceFieldGroups(),
			'fieldTypes' => $fieldModel->getResourceFieldTypes()
		);

		return $this->responseView('XenResource_ViewAdmin_Field_List', 'resource_field_list', $viewParams);
	}

	/**
	 * Gets the add/edit form response for a field.
	 *
	 * @param array $field
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	protected function _getFieldAddEditResponse(array $field)
	{
		$fieldModel = $this->_getFieldModel();

		$typeMap = $fieldModel->getResourceFieldTypeMap();
		$validFieldTypes = $fieldModel->getResourceFieldTypes();

		if (!empty($field['field_id']))
		{
			$selCategoryIds = $this->_getFieldModel()->getCategoryAssociationsByField($field['field_id']);

			$masterTitle = $fieldModel->getResourceFieldMasterTitlePhraseValue($field['field_id']);
			$masterDescription = $fieldModel->getResourceFieldMasterDescriptionPhraseValue($field['field_id']);

			$existingType = $typeMap[$field['field_type']];
			foreach ($validFieldTypes AS $typeId => $type)
			{
				if ($typeMap[$typeId] != $existingType)
				{
					unset($validFieldTypes[$typeId]);
				}
			}
		}
		else
		{
			$selCategoryIds = array();
			$masterTitle = '';
			$masterDescription = '';
			$existingType = false;
		}

		if (!$selCategoryIds)
		{
			$selCategoryIds = array(0);
		}

		$viewParams = array(
			'field' => $field,
			'masterTitle' => $masterTitle,
			'masterDescription' => $masterDescription,
			'masterFieldChoices' => $fieldModel->getResourceFieldChoices($field['field_id'], $field['field_choices'], true),

			'fieldGroups' => $fieldModel->getResourceFieldGroups(),
			'validFieldTypes' => $validFieldTypes,
			'fieldTypeMap' => $typeMap,
			'existingType' => $existingType,

			'categories' => $this->_getCategoryModel()->getAllCategories(),
			'selCategoryIds' => $selCategoryIds,
		);

		return $this->responseView('XenResource_ViewAdmin_Field_Edit', 'resource_field_edit', $viewParams);
	}

	/**
	 * Displays form to add a custom resource field.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionAdd()
	{
		return $this->_getFieldAddEditResponse(array(
			'field_id' => null,
			'display_group' => 'above_info',
			'display_order' => 1,
			'field_type' => 'textbox',
			'field_choices' => '',
			'match_type' => 'none',
			'match_regex' => '',
			'match_callback_class' => '',
			'match_callback_method' => '',
			'max_length' => 0,
			'required' => 0,
			'display_template' => ''
		));
	}

	/**
	 * Displays form to edit a custom resource field.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionEdit()
	{
		$field = $this->_getFieldOrError($this->_input->filterSingle('field_id', XenForo_Input::STRING));
		return $this->_getFieldAddEditResponse($field);
	}

	/**
	 * Saves a custom resource field.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionSave()
	{
		$fieldId = $this->_input->filterSingle('field_id', XenForo_Input::STRING);

		$newFieldId = $this->_input->filterSingle('new_field_id', XenForo_Input::STRING);
		$dwInput = $this->_input->filter(array(
			'display_group' => XenForo_Input::STRING,
			'display_order' => XenForo_Input::UINT,
			'field_type' => XenForo_Input::STRING,
			'match_type' => XenForo_Input::STRING,
			'match_regex' => XenForo_Input::STRING,
			'match_callback_class' => XenForo_Input::STRING,
			'match_callback_method' => XenForo_Input::STRING,
			'max_length' => XenForo_Input::UINT,
			'required' => XenForo_Input::UINT,
			'display_template' => XenForo_Input::STRING
		));
		$categoryIds = $this->_input->filterSingle('resource_category_ids', XenForo_Input::UINT, array('array' => true));

		$dw = XenForo_DataWriter::create('XenResource_DataWriter_ResourceField');
		if ($fieldId)
		{
			$dw->setExistingData($fieldId);
		}
		else
		{
			$dw->set('field_id', $newFieldId);
		}

		$dw->bulkSet($dwInput);

		$dw->setExtraData(XenResource_DataWriter_ResourceField::DATA_CATEGORY_IDS, $categoryIds);

		$dw->setExtraData(
			XenResource_DataWriter_ResourceField::DATA_TITLE,
			$this->_input->filterSingle('title', XenForo_Input::STRING)
		);
		$dw->setExtraData(
			XenResource_DataWriter_ResourceField::DATA_DESCRIPTION,
			$this->_input->filterSingle('description', XenForo_Input::STRING)
		);

		$fieldChoices = $this->_input->filterSingle('field_choice', XenForo_Input::STRING, array('array' => true));
		$fieldChoicesText = $this->_input->filterSingle('field_choice_text', XenForo_Input::STRING, array('array' => true));
		$fieldChoicesCombined = array();
		foreach ($fieldChoices AS $key => $choice)
		{
			if (isset($fieldChoicesText[$key]))
			{
				$fieldChoicesCombined[$choice] = $fieldChoicesText[$key];
			}
		}

		$dw->setFieldChoices($fieldChoicesCombined);

		$dw->save();

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildAdminLink('resource-fields') . $this->getLastHash($dw->get('field_id'))
		);
	}

	/**
	 * Deletes a custom resource field.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionDelete()
	{
		if ($this->isConfirmedPost())
		{
			return $this->_deleteData(
				'XenResource_DataWriter_ResourceField', 'field_id',
				XenForo_Link::buildAdminLink('resource-fields')
			);
		}
		else
		{
			$field = $this->_getFieldOrError($this->_input->filterSingle('field_id', XenForo_Input::STRING));

			$viewParams = array(
				'field' => $field
			);

			return $this->responseView('XenResource_ViewAdmin_Field_Delete', 'resource_field_delete', $viewParams);
		}
	}

	/**
	 * Gets the specified field or throws an exception.
	 *
	 * @param string $id
	 *
	 * @return array
	 */
	protected function _getFieldOrError($id)
	{
		$field = $this->getRecordOrError(
			$id, $this->_getFieldModel(), 'getResourceFieldById',
			'requested_field_not_found'
		);

		return $this->_getFieldModel()->prepareResourceField($field);
	}

	/**
	 * @return XenResource_Model_ResourceField
	 */
	protected function _getFieldModel()
	{
		return $this->getModelFromCache('XenResource_Model_ResourceField');
	}

	/**
	 * @return XenResource_Model_Category
	 */
	protected function _getCategoryModel()
	{
		return $this->getModelFromCache('XenResource_Model_Category');
	}
}