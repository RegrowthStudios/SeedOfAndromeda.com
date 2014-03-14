<?php

class EWRporta_ControllerAdmin_Options extends XenForo_ControllerAdmin_Abstract
{
	protected function _preDispatch($action)
	{
		$this->assertAdminPermission('addOn');

		if (!$this->getModelFromCache('EWRporta_Model_Blocks')->canEditBlocks())
		{
			throw new XenForo_Exception(new XenForo_Phrase('you_cannot_edit_option_or_group_definitions'), true);
		}
	}

	public function actionAdd()
	{
		$blocks = $this->getModelFromCache('EWRporta_Model_Blocks')->getAllBlocks();
		$blockOptions = array();

		foreach ($blocks AS $block)
		{
			$blockOptions[$block['block_id']] = $block['block_id'].' - '.$block['title'];
		}

		$option = array(
			'edit_format' => 'textbox',
			'data_type' => 'string',
			'display_order' => 1
		);

		$viewParams = array(
			'option' => $option,
			'blockOptions' => $blockOptions,
			'blockSelected' => $this->_input->filterSingle('block_id', XenForo_Input::STRING)
		);

		return $this->responseView('EWRporta_ViewAdmin_AddOption', 'EWRporta_EditOption', $viewParams);
	}

	public function actionEdit()
	{
		$optionId = $this->_input->filterSingle('option_id', XenForo_Input::STRING);

		if (!$option = $this->getModelFromCache('EWRporta_Model_Options')->getOptionById($optionId))
		{
			throw $this->responseException($this->responseError(new XenForo_Phrase('requested_option_not_found'), 404));
		}

		$blocks = $this->getModelFromCache('EWRporta_Model_Blocks')->getAllBlocks();
		$blockOptions = array();

		foreach ($blocks AS $block)
		{
			$blockOptions[$block['block_id']] = $block['block_id'].' - '.$block['title'];
		}

		$viewParams = array(
			'option' => $option,
			'blockOptions' => $blockOptions,
			'blockSelected' => $option['block_id']
		);

		return $this->responseView('EWRporta_ViewAdmin_EditOption', 'EWRporta_EditOption', $viewParams);
	}

	public function actionSave()
	{
		$this->_assertPostOnly();

		$input = $this->_input->filter(array(
			'option_id' => XenForo_Input::STRING,
			'block_id' => XenForo_Input::STRING,
			'title' => XenForo_Input::STRING,
			'explain' => XenForo_Input::STRING,
			'edit_format' => XenForo_Input::STRING,
			'edit_format_params' => XenForo_Input::STRING,
			'data_type' => XenForo_Input::STRING,
			'sub_options' => XenForo_Input::STRING,
			'validation_class' => XenForo_Input::STRING,
			'validation_method' => XenForo_Input::STRING,
			'display_order' => XenForo_Input::UINT
		));

		$originalId = $this->_input->filterSingle('original_option_id', XenForo_Input::STRING);

		$this->getModelFromCache('EWRporta_Model_Options')->updateOption($input, $originalId);

		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/blocks/options', $input));
	}

	public function actionDelete()
	{
		$optionId = $this->_input->filterSingle('option_id', XenForo_Input::STRING);

		if (!$option = $this->getModelFromCache('EWRporta_Model_Options')->getOptionById($optionId))
		{
			throw $this->responseException($this->responseError(new XenForo_Phrase('requested_option_not_found'), 404));
		}

		if ($this->isConfirmedPost())
		{
			$dw = XenForo_DataWriter::create('EWRporta_DataWriter_Options');
			$dw->setExistingData($option);
			$dw->delete();

			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/blocks/options', $option));
		}
		else
		{
			$viewParams = array(
				'option' => $option
			);

			return $this->responseView('EWRporta_ViewAdmin_DeleteOption', 'EWRporta_DeleteOption', $viewParams);
		}
	}
}