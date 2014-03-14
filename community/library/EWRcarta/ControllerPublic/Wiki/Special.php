<?php

class EWRcarta_ControllerPublic_Wiki_Special extends XenForo_ControllerPublic_Abstract
{
	public $perms;
	public $slugs;

	public function actionIndex()
	{
		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL_PERMANENT, XenForo_Link::buildPublicLink('wiki'));
	}

	public function actionPages()
	{
		$fullList = $this->getModelFromCache('EWRcarta_Model_Lists')->getPageList(0, $blank, 0, false, true);
		$pageCount = $this->getModelFromCache('EWRcarta_Model_Pages')->getPageCount();

		$count = 0;

		foreach ($fullList AS $key => $letter)
		{
			$count += count($letter);

			if ($count > $pageCount/3)
			{
				$fullList[$key.$count] = "break";
				$count = 0;
			}
		}

		ksort($fullList);

		$viewParams = array(
			'fullList' => $fullList,
			'pageCount' => $pageCount,
		);

		return $this->responseView('EWRcarta_ViewPublic_Pages', 'EWRcarta_Pages', $viewParams);
	}

	public function actionCreatePage()
	{
		if (!$this->perms['create']) { return $this->responseNoPermission(); }

		$input = array(
			'page_name' => '',
			'page_content' => '',
			'page_type' => $this->_input->filterSingle('page_type', XenForo_Input::STRING),
			'page_index' => 0,
			'page_protect' => 0,
			'page_sidebar' => 1,
			'page_sublist' => 1,
			'page_groups' => '',
			'page_users' => '',
			'page_admins' => '',
		);

		$attachmentParams = array(
			'hash' => md5(uniqid('', true)),
			'content_type' => 'wiki',
			'content_data' => array('page_id' => '')
		);
		$attachmentConstraints = $this->getModelFromCache('XenForo_Model_Attachment')->getAttachmentConstraints();
		$attachments = array();

		if ($this->_request->isPost())
		{
			$input = $this->_input->filter(array(
				'attachment_hash' => XenForo_Input::STRING,
				'page_name' => XenForo_Input::STRING,
				'page_slug' => XenForo_Input::STRING,
				'page_type' => XenForo_Input::STRING,
				'page_parent' => XenForo_Input::UINT,
				'submit' => XenForo_Input::STRING,
			)) + $input;
			$input['page_content'] = $this->getHelper('Editor')->getMessageText('page_content', $this->_input);
			
			if ($this->perms['admin'])
			{
				$input = $this->_input->filter(array(
					'page_index' => XenForo_Input::UINT,
					'page_protect' => XenForo_Input::UINT,
					'page_sidebar' => XenForo_Input::UINT,
					'page_sublist' => XenForo_Input::UINT,
					'page_groups' => array(XenForo_Input::UINT, array('array' => true)),
					'usernames' => XenForo_Input::STRING,
					'administrators' => XenForo_Input::STRING,
				)) + $input;
				$input['page_groups'] = implode(',', $input['page_groups']);
			}

			if ($input['page_content'] && $input['submit'])
			{
				if (!XenForo_Captcha_Abstract::validateDefault($this->_input))
				{
					return $this->responseCaptchaFailed();
				}

				$page = $this->getModelFromCache('EWRcarta_Model_Pages')->updatePage($input);
				return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki', $page));
			}

			if ($input['attachment_hash'])
			{
				$attachmentParams['hash'] = $input['attachment_hash'];
				$attachments = $this->getModelFromCache('XenForo_Model_Attachment')->getAttachmentsByTempHash($attachmentParams['hash']); 
			}
		}

		if ($input['page_type'] == 'phpfile' && !$this->perms['admin'])
		{
			$input['page_type'] = 'bbcode';
		}

		$viewParams = array(
            'attachmentParams' => $attachmentParams,
			'attachments' => $this->getModelFromCache('XenForo_Model_Attachment')->prepareAttachments($attachments),
            'attachmentConstraints' => $attachmentConstraints,
			'perms' => $this->perms,
			'input' => $input,
			'captcha' => XenForo_Captcha_Abstract::createDefault(),
			'groups' => $this->getModelFromCache('XenForo_Model_UserGroup')->getUserGroupOptions($input['page_groups']),
			'fullList' => $this->getModelFromCache('EWRcarta_Model_Lists')->getPageList(),
		);

		return $this->responseView('EWRcarta_ViewPublic_PageCreate', 'EWRcarta_PageCreate', $viewParams);
	}

	public function actionRecent()
	{
		if (!$this->perms['history']) { return $this->responseNoPermission(); }

		$start = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
		$stop = 50;

		$viewParams = array(
			'perms' => $this->perms,
			'start' => $start,
			'stop' => $stop,
			'count' => $this->getModelFromCache('EWRcarta_Model_History')->getHistoryCount(),
			'fullList' => $this->getModelFromCache('EWRcarta_Model_History')->getHistoryList($start, $stop),
		);

		return $this->responseView('EWRcarta_ViewPublic_Recent', 'EWRcarta_Recent', $viewParams);
	}

	public function actionAdministrate()
	{
		if (!$this->perms['admin']) { return $this->responseNoPermission(); }

		$viewParams = array(
			'fullList' => $this->getModelFromCache('EWRcarta_Model_Lists')->getTemplates(),
		);

		return $this->responseView('EWRcarta_ViewPublic_Administrate', 'EWRcarta_Administrate', $viewParams);
	}

	public function actionEditTemplate()
	{
		if (!$this->perms['admin']) { return $this->responseNoPermission(); }

		if (!$template = $this->getModelFromCache('EWRcarta_Model_Templates')->getTemplateBySlug($this->slugs[2]))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki'));
		}

		if ($this->_request->isPost())
		{
			$input = $this->_input->filter(array(
				'template_name' => XenForo_Input::STRING,
				'template_newname' => XenForo_Input::STRING,
				'submit' => XenForo_Input::STRING,
			));

			if ($input['template_content'] = $this->getHelper('Editor')->getMessageText('template_content', $this->_input))
			{
				$this->getModelFromCache('EWRcarta_Model_Templates')->updateTemplate($input);

				return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki/special/administrate'));
			}
		}

		return $this->responseView('EWRcarta_ViewPublic_TemplateEdit', 'EWRcarta_TemplateEdit', array('template' => $template));
	}

	public function actionDeleteTemplate()
	{
		if (!$this->perms['admin']) { return $this->responseNoPermission(); }

		if ($template = $this->getModelFromCache('EWRcarta_Model_Templates')->getTemplateBySlug($this->slugs[2]))
		{
			if ($this->_request->isPost())
			{
				$this->getModelFromCache('EWRcarta_Model_Templates')->deleteTemplate($template);
			}
			else
			{
				return $this->responseView('EWRcarta_ViewPublic_TemplateDelete', 'EWRcarta_TemplateDelete', array('template' => $template));
			}
		}

		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki/special/administrate'));
	}

	public function actionCreateTemplate()
	{
		if (!$this->perms['admin']) { return $this->responseNoPermission(); }

		if ($this->_request->isPost())
		{
			$input = $this->_input->filter(array(
				'template_name' => XenForo_Input::STRING,
				'template_newname' => XenForo_Input::STRING,
				'submit' => XenForo_Input::STRING,
			));

			if ($input['template_content'] = $this->getHelper('Editor')->getMessageText('template_content', $this->_input))
			{
				$this->getModelFromCache('EWRcarta_Model_Templates')->updateTemplate($input);

				return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki/special/administrate'));
			}
		}

		return $this->responseView('EWRcarta_ViewPublic_TemplateCreate', 'EWRcarta_TemplateCreate');
	}

	public function actionEmptyCache()
	{
		if (!$this->perms['admin']) { return $this->responseNoPermission(); }

		$this->getModelFromCache('EWRcarta_Model_Cache')->emptyCache();

		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki/special/administrate'));
	}

	public function actionEmptyHistory()
	{
		if (!$this->perms['admin']) { return $this->responseNoPermission(); }

		$this->getModelFromCache('EWRcarta_Model_History')->emptyHistory();

		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki/special/recent'));
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
		$this->slugs = explode('/', $this->_routeMatch->getMinorSection());

		if (!$this->perms['view']) { throw $this->getNoPermissionResponseException(); }
	}
}