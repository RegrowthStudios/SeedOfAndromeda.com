<?php

class EWRporta_ControllerAdmin_Blocks extends XenForo_ControllerAdmin_Abstract
{
	protected function _preDispatch($action)
	{
		$this->assertAdminPermission('option');
	}

	public function actionIndex()
	{
		$viewParams = array(
			'blocks' => $this->getModelFromCache('EWRporta_Model_Blocks')->getAllBlocks(),
			'canEdit' => $this->getModelFromCache('EWRporta_Model_Blocks')->canEditBlocks()
		);

		return $this->responseView('EWRporta_ViewAdmin_Blocks', 'EWRporta_Blocks', $viewParams);
	}

	public function actionAdd()
	{
		$viewParams = array(
			'block' => array()
		);

		return $this->responseView('EWRporta_ViewAdmin_EditBlock', 'EWRporta_EditBlock', $viewParams);
	}

	public function actionEdit()
	{
		$blockId = $this->_input->filterSingle('block_id', XenForo_Input::STRING);

		if (!$block = $this->getModelFromCache('EWRporta_Model_Blocks')->getBlockById($blockId))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/blocks'));
		}

		$viewParams = array(
			'block' => $block
		);

		return $this->responseView('EWRporta_ViewAdmin_EditBlock', 'EWRporta_EditBlock', $viewParams);
	}

	public function actionSave()
	{
		$this->_assertPostOnly();

		$input = $this->_input->filter(array(
			'block_id' => XenForo_Input::STRING,
			'title' => XenForo_Input::STRING,
			'version_string' => XenForo_Input::STRING,
			'version_id' => XenForo_Input::UINT,
			'url' => XenForo_Input::STRING,
			'install_callback_class'    => XenForo_Input::STRING,
			'install_callback_method'   => XenForo_Input::STRING,
			'uninstall_callback_class'  => XenForo_Input::STRING,
			'uninstall_callback_method' => XenForo_Input::STRING
		));

		$originalId = $this->_input->filterSingle('original_block_id', XenForo_Input::STRING);

		$this->getModelFromCache('EWRporta_Model_Blocks')->updateBlock($input, $originalId);

		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/blocks/options', $input));
	}

	public function actionOptions()
	{
		$blockId = $this->_input->filterSingle('block_id', XenForo_Input::STRING);

		if (!$block = $this->getModelFromCache('EWRporta_Model_Blocks')->getBlockById($blockId))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/blocks'));
		}

		if ($block['cache'] != 'now')
		{
			preg_match('#\+(\d+)\s(\w+)#i', $block['cache'], $matches);
			$block['cached'] = 'checked';
			$block['cache_time'] = $matches[1];
			$block['cache_unit'] = $matches[2];

			switch ($block['cache_unit'])
			{
				case "days":	$block['unit']['days'] = 'selected="selected"'; break;
				case "hours":	$block['unit']['hour'] = 'selected="selected"'; break;
				default:		$block['unit']['mins'] = 'selected="selected"';
			}
		}

		$options = $this->getModelFromCache('EWRporta_Model_Options')->getOptionsByBlock($blockId);

		$viewParams = array(
			'block' => $block,
			'groups' => $this->getModelFromCache('XenForo_Model_UserGroup')->getUserGroupOptions($block['groups']),
			'options' => $this->getModelFromCache('EWRporta_Model_Options')->prepareOptions($options),
			'canEdit' => $this->getModelFromCache('EWRporta_Model_Blocks')->canEditBlocks(),
			'blocks' => $this->getModelFromCache('EWRporta_Model_Blocks')->getAllBlocks()
		);

		return $this->responseView('EWRporta_ViewAdmin_BlockOptions', 'EWRporta_BlockOptions', $viewParams);
	}

	public function actionUpdate()
	{
		$this->_assertPostOnly();

		$input = $this->_input->filter(array(
			'block_id' => XenForo_Input::STRING,
			'options' => XenForo_Input::ARRAY_SIMPLE,
			'options_listed' => array(XenForo_Input::STRING, array('array' => true)),
			'display' => XenForo_Input::STRING,
			'cache' => XenForo_Input::UINT,
			'cache_time' => XenForo_Input::UINT,
			'cache_unit' => XenForo_Input::STRING,
			'locked' => XenForo_Input::UINT,
			'groups' => array(XenForo_Input::UINT, array('array' => true)),
		));

		$input['cache'] = $input['cache'] ? '+'.$input['cache_time'].' '.$input['cache_unit'] : 'now';

		foreach ($input['options_listed'] AS $optionName)
		{
			if (!isset($input['options'][$optionName]))
			{
				$input['options'][$optionName] = '';
			}
		}

		$this->getModelFromCache('EWRporta_Model_Options')->updateOptions($input);
		$this->getModelFromCache('EWRporta_Model_Caches')->emptyBlockCache($input);

		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/blocks/options', $input));
	}

	public function actionEmpty()
	{
		$blockId = $this->_input->filterSingle('block_id', XenForo_Input::STRING);

		if ($block = $this->getModelFromCache('EWRporta_Model_Blocks')->getBlockById($blockId))
		{
			$this->getModelFromCache('EWRporta_Model_Caches')->emptyBlockCache($block);
		}

		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/blocks'));
	}

	public function actionInstallConfirm()
	{
		return $this->responseView('EWRporta_ViewAdmin_InstallBlock', 'EWRporta_InstallBlock');
	}

	public function actionInstall()
	{
		$this->_assertPostOnly();

		$fileTransfer = new Zend_File_Transfer_Adapter_Http();
		if ($fileTransfer->isUploaded('upload_file'))
		{
			$fileInfo = $fileTransfer->getFileInfo('upload_file');
			$fileName = $fileInfo['upload_file']['tmp_name'];
		}
		else
		{
			$fileName = $this->_input->filterSingle('server_file', XenForo_Input::STRING);
		}

		$this->getModelFromCache('EWRporta_Model_Blocks')->installBlockXmlFromFile($fileName);

		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/blocks'));
	}

	public function actionExport()
	{
		$blockId = $this->_input->filterSingle('block_id', XenForo_Input::STRING);

		if (!$block = $this->getModelFromCache('EWRporta_Model_Blocks')->getBlockById($blockId))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/blocks'));
		}

		$this->_routeMatch->setResponseType('xml');

		$viewParams = array(
			'block' => $block,
			'xml' => $this->getModelFromCache('EWRporta_Model_Blocks')->exportBlock($block),
		);

		return $this->responseView('EWRporta_ViewAdmin_ExportBlock', '', $viewParams);
	}

	public function actionDelete()
	{
		$blockId = $this->_input->filterSingle('block_id', XenForo_Input::STRING);

		if (!$block = $this->getModelFromCache('EWRporta_Model_Blocks')->getBlockById($blockId))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/blocks'));
		}

		if ($this->isConfirmedPost())
		{
			$this->getModelFromCache('EWRporta_Model_Blocks')->uninstallBlock($block);
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/blocks'));
		}
		else
		{
			$viewParams = array(
				'block' => $block
			);

			return $this->responseView('EWRporta_ViewAdmin_DeleteBlock', 'EWRporta_DeleteBlock', $viewParams);
		}
	}

	protected function _switchAddOnActiveStateAndGetResponse($blockId, $activeState)
	{
		$dw = XenForo_DataWriter::create('EWRporta_DataWriter_Blocks');
		$dw->setExistingData($blockId);
		$dw->set('active', $activeState);
		$dw->save();

		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/blocks') . $this->getLastHash($blockId));
	}

	public function actionEnable()
	{
		$this->_checkCsrfFromToken($this->_input->filterSingle('_xfToken', XenForo_Input::STRING));

		$blockId = $this->_input->filterSingle('block_id', XenForo_Input::STRING);
		return $this->_switchAddOnActiveStateAndGetResponse($blockId, 1);
	}

	public function actionDisable()
	{
		$this->_checkCsrfFromToken($this->_input->filterSingle('_xfToken', XenForo_Input::STRING));

		$blockId = $this->_input->filterSingle('block_id', XenForo_Input::STRING);
		return $this->_switchAddOnActiveStateAndGetResponse($blockId, 0);
	}

	public function actionToggle()
	{
		$this->_assertPostOnly();

		$blockExists = $this->_input->filterSingle('blockExists', array(XenForo_Input::UINT, 'array' => true));
		$blocks = $this->_input->filterSingle('block', array(XenForo_Input::UINT, 'array' => true));

		foreach ($this->getModelFromCache('EWRporta_Model_Blocks')->getAllBlocks() AS $blockId => $block)
		{
			if (isset($blockExists[$blockId]))
			{
				$blockActive = (isset($blocks[$blockId]) && $blocks[$blockId] ? 1 : 0);

				if ($block['active'] != $blockActive)
				{
					$dw = XenForo_DataWriter::create('EWRporta_DataWriter_Blocks');
					$dw->setExistingData($blockId);
					$dw->set('active', $blockActive);
					$dw->save();
				}
			}
		}

		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/blocks'));
	}
}