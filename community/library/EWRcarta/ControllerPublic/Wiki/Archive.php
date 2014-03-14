<?php

class EWRcarta_ControllerPublic_Wiki_Archive extends XenForo_ControllerPublic_Abstract
{
	public $perms;

	public function actionIndex()
	{
		$histID = $this->_input->filterSingle('history_id', XenForo_Input::UINT);

		if (!$history = $this->getModelFromCache('EWRcarta_Model_History')->getHistory(array('history_id' => $histID)))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki'));
		}

		if (!$page = $this->getModelFromCache('EWRcarta_Model_Pages')->getPageByID($history['page_id']))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki'));
		}

		$subList = $page['page_sublist'] ? $this->getModelFromCache('EWRcarta_Model_Lists')->getPageList($page['page_id']) : array();

		$viewParams = array(
			'perms' => $this->perms,
			'page' => $page,
			'history' => $history,
			'related' => $this->getModelFromCache('EWRcarta_Model_Lists')->getRelated($page),
			'subList' => $subList,
			'breadCrumbs' => array_reverse($this->getModelFromCache('EWRcarta_Model_Lists')->getCrumbs($page)),
		);

		if ($page['page_sidebar'])
		{
			$viewParams['sidebar'] = $this->getModelFromCache('EWRcarta_Model_Parser')->parseSidebar($page);
			return $this->responseView('EWRcarta_ViewPublic_PageArchive', 'EWRcarta_PageArchive', $viewParams);
		}
		else
		{
			return $this->responseView('EWRcarta_ViewPublic_PageArchive', 'EWRcarta_PageArchive_NoSide', $viewParams);
		}
	}

	public function actionCompare()
	{
		$histID = $this->_input->filterSingle('history_id', XenForo_Input::UINT);

		if (!$history = $this->getModelFromCache('EWRcarta_Model_History')->getHistory(array('history_id' => $histID)))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki'));
		}

		if (!$page = $this->getModelFromCache('EWRcarta_Model_Pages')->getPageByID($history['page_id']))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki'));
		}

		$diff = new Diff_Compare(explode("\n", $history['history_content']), explode("\n", $page['page_content']));
		$renderer = new Diff_Renderer_Html_SideBySide;

		$viewParams = array(
			'perms' => $this->perms,
			'page' => $page,
			'history' => $history,
			'compare' => $diff->Render($renderer),
			'breadCrumbs' => array_reverse($this->getModelFromCache('EWRcarta_Model_Lists')->getCrumbs($page)),
		);

		return $this->responseView('EWRcarta_ViewPublic_PageCompare', 'EWRcarta_PageCompare', $viewParams);
	}
	
	public function actionRevert()
	{
		$histID = $this->_input->filterSingle('history_id', XenForo_Input::UINT);

		if (!$history = $this->getModelFromCache('EWRcarta_Model_History')->getHistory(array('history_id' => $histID)))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki'));
		}

		if (!$page = $this->getModelFromCache('EWRcarta_Model_Pages')->getPageByID($history['page_id']))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki'));
		}
		
		if ($this->_request->isPost())
		{
			$page['page_type'] = $history['history_type'];
			$page['page_content'] = $history['history_content'];
			$page['attachment_hash'] = 0;
			
			$this->getModelFromCache('EWRcarta_Model_Pages')->updatePage($page, true);
			$this->getModelFromCache('EWRcarta_Model_History')->markReverts($history);
			
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki', $page));
		}
		else
		{
			$viewParams = array(
				'page' => $page,
				'history' => $history,
			);

			return $this->responseView('EWRcarta_ViewPublic_PageArchiveRevert', 'EWRcarta_PageArchiveRevert', $viewParams);
		}
				
		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki/history', $page));
	}
	
	public function actionDelete()
	{
		$histID = $this->_input->filterSingle('history_id', XenForo_Input::UINT);

		if (!$history = $this->getModelFromCache('EWRcarta_Model_History')->getHistory(array('history_id' => $histID)))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki'));
		}

		if (!$page = $this->getModelFromCache('EWRcarta_Model_Pages')->getPageByID($history['page_id']))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki'));
		}
		
		if ($this->_request->isPost())
		{
			$this->getModelFromCache('EWRcarta_Model_History')->deleteHistory($history);
		}
		else
		{
			$viewParams = array(
				'page' => $page,
				'history' => $history,
			);

			return $this->responseView('EWRcarta_ViewPublic_PageArchiveDelete', 'EWRcarta_PageArchiveDelete', $viewParams);
		}
				
		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki/history', $page));
	}

	public function actionIpInfo()
	{
		if (!$this->perms['admin']) { return $this->responseNoPermission(); }

		$histID = $this->_input->filterSingle('history_id', XenForo_Input::UINT);

		if (!$history = $this->getModelFromCache('EWRcarta_Model_History')->getHistory(array('history_id' => $histID)))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki'));
		}

		if (!$page = $this->getModelFromCache('EWRcarta_Model_Pages')->getPageByID($history['page_id']))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki'));
		}

		if (!$history['ip_id'] = $history['history_ip'])
		{
			return $this->responseError(new XenForo_Phrase('no_ip_information_available'));
		}

		$viewParams = array(
			'page' => $page,
			'history' => $history,
			'ipInfo' => $this->getModelFromCache('XenForo_Model_Ip')->getContentIpInfo($history),
		);

		return $this->responseView('EWRcarta_ViewPublic_PageIpInfo', 'EWRcarta_PageIpInfo', $viewParams);
	}

	public static function getSessionActivityDetailsForList(array $activities)
	{
        $output = array();
        foreach ($activities as $key => $activity)
		{
			$output[$key] = new XenForo_Phrase('viewing_wiki');
        }

        return $output;
	}

	protected function _preDispatch($action)
	{
		parent::_preDispatch($action);

		$this->perms = $this->getModelFromCache('EWRcarta_Model_Perms')->getPermissions();

		if (!$this->perms['history']) { throw $this->getNoPermissionResponseException(); }
	}
}