<?php

class EWRporta_ControllerAdmin_Layouts extends XenForo_ControllerAdmin_Abstract
{
	protected function _preDispatch($action)
	{
		$this->assertAdminPermission('node');
	}

	public function actionIndex()
	{
		$viewParams = array(
			'layouts' => $this->getModelFromCache('EWRporta_Model_Layouts')->getLayouts()
		);

		return $this->responseView('EWRporta_ViewAdmin_Layouts', 'EWRporta_Layouts', $viewParams);
	}

	public function actionEdit()
	{
		$layoutId = $this->_input->filterSingle('layout_id', XenForo_Input::STRING);
		$layoutType = $this->_input->filterSingle('layout_type', XenForo_Input::STRING);

		if (!empty($layoutType))
		{
			$layoutId = $layoutId ? $layoutType.'-'.$layoutId : $layoutType;
		}

		if (empty($layoutId))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/layouts'));
		}

		$isPortal = (substr($layoutId, 0, 8) === 'articles') || $layoutId == 'portal' ? true : false;

		$blocks = $this->getModelFromCache('EWRporta_Model_Blocks')->getBlocks(false, $layoutId, 'portal');

		$_blocks = array(
			'top-left' => array(),
			'top-right' => array(),
			'mid-left' => array(),
			'mid-right' => array(),
			'btm-left' => array(),
			'btm-right' => array(),
			'sidebar' => array(),
			'disabled' => array()
		);

		foreach ($blocks AS $block)
		{
			if ($isPortal)
			{
				switch ($block['position'])
				{
					case 'mid-left':	$_blocks['mid-left'][] = $block;	continue 2;
					case 'mid-right':	$_blocks['mid-right'][] = $block;	continue 2;
				}
			}

			switch ($block['position'])
			{
				case 'top-left':	$_blocks['top-left'][] = $block;	break;
				case 'top-right':	$_blocks['top-right'][] = $block;	break;
				case 'btm-left':	$_blocks['btm-left'][] = $block;	break;
				case 'btm-right':	$_blocks['btm-right'][] = $block;	break;
				case 'sidebar':		$_blocks['sidebar'][] = $block;		break;
				default:			$_blocks['disabled'][] = $block;
			}
		}

		$viewParams = array(
			'layout' => array('layout_id' => $layoutId),
			'blocks' => $_blocks,
			'isPortal' => $isPortal,
			'content' => strtoupper($layoutId),
		);

		return $this->responseView('EWRporta_ViewAdmin_EditLayout', 'EWRporta_EditLayout', $viewParams);
	}

	public function actionSave()
	{
		$this->_assertPostOnly();

		$input = $this->_input->filter(array(
			'layout_id' => XenForo_Input::STRING,
			'blocks' => XenForo_Input::ARRAY_SIMPLE,
		));

		$this->getModelFromCache('EWRporta_Model_Layouts')->updateLayout($input);

		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/layouts/edit', $input));
	}

	public function actionInstallConfirm()
	{
		return $this->responseView('EWRporta_ViewAdmin_InstallLayout', 'EWRporta_InstallLayout');
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

		$this->getModelFromCache('EWRporta_Model_Layouts')->installLayoutXmlFromFile($fileName);

		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/layouts'));
	}

	public function actionExport()
	{
		$layoutId = $this->_input->filterSingle('layout_id', XenForo_Input::STRING);

		if (!$layout = $this->getModelFromCache('EWRporta_Model_Layouts')->getLayoutById($layoutId))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/layouts'));
		}

		$this->_routeMatch->setResponseType('xml');

		$viewParams = array(
			'layout' => $layout,
			'xml' => $this->getModelFromCache('EWRporta_Model_Layouts')->exportLayout($layout),
		);

		return $this->responseView('EWRporta_ViewAdmin_ExportLayout', '', $viewParams);
	}

	public function actionDelete()
	{
		$layoutId = $this->_input->filterSingle('layout_id', XenForo_Input::STRING);

		if (!$layout = $this->getModelFromCache('EWRporta_Model_Layouts')->getLayoutById($layoutId))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/layouts'));
		}

		if ($this->isConfirmedPost())
		{
			$this->getModelFromCache('EWRporta_Model_Layouts')->resetLayout($layout);
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildAdminLink('ewrporta/layouts'));
		}
		else
		{
			$viewParams = array(
				'layout' => $layout
			);

			return $this->responseView('EWRporta_ViewAdmin_DeleteLayout', 'EWRporta_DeleteLayout', $viewParams);
		}
	}
}