<?php

class EWRcarta_ControllerPublic_Wiki extends XenForo_ControllerPublic_Abstract
{
	public $perms;

	public function actionIndex()
	{
		$pageSlug = $this->_input->filterSingle('page_slug', XenForo_Input::STRING);
		$pageSlug = $pageSlug ? $pageSlug : 'index';

		$redirect = $this->_input->filterSingle('redirect', XenForo_Input::STRING);
		$noRedirect = $this->_input->filterSingle('noRedirect', XenForo_Input::UINT);

		$this->canonicalizeRequestUrl(XenForo_Link::buildPublicLink('wiki', array('page_slug' => $pageSlug)));

		if (!$page = $this->getModelFromCache('EWRcarta_Model_Pages')->getPageBySlug($pageSlug))
		{
			$viewParams = array(
				'perms' => $this->perms,
				'page' => array('page_slug' => $pageSlug),
				'sidebar' => $this->getModelFromCache('EWRcarta_Model_Parser')->parseSidebar($page),
			);
			
			return $this->responseView('EWRcarta_ViewPublic_PageNull', 'EWRcarta_PageNull', $viewParams);
		}
		elseif (empty($redirect) && empty($noRedirect))
		{
			if (preg_match('#\[redirect]([A-Za-z0-9\-]+)\[/redirect]#si', $page['page_content'], $matches))
			{
				$redir = array('page_slug' => $matches[1]);
				$extra = array('redirect' => $page['page_slug']);

				return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki', $redir, $extra));
			}
		}
		elseif ($redirect)
		{
			$redirect = $this->getModelFromCache('EWRcarta_Model_Pages')->getPageBySlug($redirect);
		}

		$breadCrumbs = array_reverse($this->getModelFromCache('EWRcarta_Model_Lists')->getCrumbs($page));
		array_pop($breadCrumbs);
		$subList = $page['page_sublist'] ? $this->getModelFromCache('EWRcarta_Model_Lists')->getPageList($page['page_id']) : array();

		$page['attachments'] = $this->getModelFromCache('XenForo_Model_Attachment')->getAttachmentsByContentId('wiki', $page['page_id']);
		$page['attachments'] = $this->getModelFromCache('XenForo_Model_Attachment')->prepareAttachments($page['attachments']);
		
		if (!empty($page['page_groups']) || !empty($page['page_users']) || !empty($page['page_admins']))
		{
			$visitor = XenForo_Visitor::getInstance();
				
			if (!empty($page['page_groups']))
			{
				$groups = explode(',', $page['page_groups']);
				$member = false;

				foreach ($groups AS $group)
				{
					if ($visitor->isMemberOf($group)) { $this->perms['edit'] = true; break; }
				}
			}
			
			if (!empty($page['page_users']))
			{
				$userIDs = explode(',', $page['page_users']);
				if (in_array($visitor['user_id'], $userIDs)) { $this->perms['edit'] = true; }
			}
			
			if (!empty($page['page_admins']))
			{
				$userIDs = explode(',', $page['page_admins']);
				if (in_array($visitor['user_id'], $userIDs)) { $this->perms['edit'] = true; }
			}
		}

		$viewParams = array(
			'perms' => $this->perms,
			'redirect' => $redirect,
			'page' => $this->getModelFromCache('EWRcarta_Model_Pages')->updateViews($page),
			'thread' => !empty($page['thread_id']) ? $this->getModelFromCache('XenForo_Model_Thread')->getThreadById($page['thread_id']) : false,
			'subList' => $subList,
			'breadCrumbs' => $breadCrumbs,
			'canViewAttachments' => true,
		);

		if ($page['page_sidebar'])
		{
			$viewParams['sidebar'] = $this->getModelFromCache('EWRcarta_Model_Parser')->parseSidebar($page);
			return $this->responseView('EWRcarta_ViewPublic_PageView', 'EWRcarta_PageView', $viewParams);
		}
		else
		{
			return $this->responseView('EWRcarta_ViewPublic_PageView', 'EWRcarta_PageView_NoSide', $viewParams);
		}
	}
	
	public function actionAttachments()
	{
		$pageSlug = $this->_input->filterSingle('page_slug', XenForo_Input::STRING);

		if (!$page = $this->getModelFromCache('EWRcarta_Model_Pages')->getPageBySlug($pageSlug))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki'));
		}

		$page['attachments'] = $this->getModelFromCache('XenForo_Model_Attachment')->getAttachmentsByContentId('wiki', $page['page_id']);
		$page['attachments'] = $this->getModelFromCache('XenForo_Model_Attachment')->prepareAttachments($page['attachments']);
		
		$viewParams = array(
			'perms' => $this->perms,
			'page' => $page,
			'canViewAttachments' => true,
		);
		
		if ($this->_noRedirect())
		{
			return $this->responseView('EWRcarta_ViewPublic_PageAttachments', 'EWRcarta_PageAttachments_Simple', $viewParams);
		}
		else
		{
			$viewParams['breadCrumbs'] = array_reverse($this->getModelFromCache('EWRcarta_Model_Lists')->getCrumbs($page));
			return $this->responseView('EWRcarta_ViewPublic_PageAttachments', 'EWRcarta_PageAttachments', $viewParams);
		}
	}

	public function actionHistory()
	{
		if (!$this->perms['history']) { return $this->responseNoPermission(); }

		$pageSlug = $this->_input->filterSingle('page_slug', XenForo_Input::STRING);

		if (!$page = $this->getModelFromCache('EWRcarta_Model_Pages')->getPageBySlug($pageSlug))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki'));
		}

		$start = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
		$stop = 50;

		$viewParams = array(
			'perms' => $this->perms,
			'page' => $page,
			'start' => $start,
			'stop' => $stop,
			'count' => $this->getModelFromCache('EWRcarta_Model_History')->getHistoryCount($page),
			'fullList' => $this->getModelFromCache('EWRcarta_Model_History')->getHistoryList($start, $stop, $page),
		);

		if ($this->_noRedirect())
		{
			return $this->responseView('EWRcarta_ViewPublic_PageHistory', 'EWRcarta_PageHistory_Simple', $viewParams);
		}
		else
		{
			$viewParams['breadCrumbs'] = array_reverse($this->getModelFromCache('EWRcarta_Model_Lists')->getCrumbs($page));
			return $this->responseView('EWRcarta_ViewPublic_PageHistory', 'EWRcarta_PageHistory', $viewParams);
		}
	}
	
	public function actionEditors()
	{
		if (!$this->perms['history']) { return $this->responseNoPermission(); }

		$pageSlug = $this->_input->filterSingle('page_slug', XenForo_Input::STRING);

		if (!$page = $this->getModelFromCache('EWRcarta_Model_Pages')->getPageBySlug($pageSlug))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki'));
		}

		$viewParams = array(
			'perms' => $this->perms,
			'page' => $page,
			'editors' => $this->getModelFromCache('EWRcarta_Model_History')->getEditorsList($page),
		);

		if ($this->_noRedirect())
		{
			return $this->responseView('EWRcarta_ViewPublic_PageEditors', 'EWRcarta_PageEditors_Simple', $viewParams);
		}
		else
		{
			$viewParams['breadCrumbs'] = array_reverse($this->getModelFromCache('EWRcarta_Model_Lists')->getCrumbs($page));
			return $this->responseView('EWRcarta_ViewPublic_PageEditors', 'EWRcarta_PageEditors', $viewParams);
		}
	}
	
	public function actionThread()
	{
		$pageSlug = $this->_input->filterSingle('page_slug', XenForo_Input::STRING);

		if (!$page = $this->getModelFromCache('EWRcarta_Model_Pages')->getPageBySlug($pageSlug))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki'));
		}
		
		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('threads', $page));
	}
	
	public function actionCreateThread()
	{
		$this->_assertPostOnly();
		
		if (!$this->perms['admin']) { return $this->responseNoPermission(); }

		$pageSlug = $this->_input->filterSingle('page_slug', XenForo_Input::STRING);

		if (!$page = $this->getModelFromCache('EWRcarta_Model_Pages')->getPageBySlug($pageSlug))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki'));
		}
		
		if ($thread = $this->getModelFromCache('XenForo_Model_Thread')->getThreadById($page['thread_id']))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki', $page));
		}
		
		$nodeID = $this->_input->filterSingle('wiki_node', XenForo_Input::UINT);
		$this->getModelFromCache('EWRcarta_Model_Threads')->buildThread($page, $nodeID);
		
		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki/thread', $page));
	}

	public function actionEdit()
	{
		$pageSlug = $this->_input->filterSingle('page_slug', XenForo_Input::STRING);

		if (!$page = $this->getModelFromCache('EWRcarta_Model_Pages')->getPageBySlug($pageSlug))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki'));
		}

		if (!empty($page['page_groups']) || !empty($page['page_users']) || !empty($page['page_admins']))
		{
			$visitor = XenForo_Visitor::getInstance();
				
			if (!empty($page['page_groups']))
			{
				$groups = explode(',', $page['page_groups']);
				$member = false;

				foreach ($groups AS $group)
				{
					if ($visitor->isMemberOf($group)) { $this->perms['edit'] = true; $override = true; break; }
				}
			}
			
			if (!empty($page['page_users']))
			{
				$userIDs = explode(',', $page['page_users']);
				if (in_array($visitor['user_id'], $userIDs)) { $this->perms['edit'] = true; $override = true; }
				
				$users = $this->getModelFromCache('XenForo_Model_User')->getUsersByIds($userIDs);
				$usernames = array();
				
				foreach ($users AS $user)
				{
					$usernames[] = $user['username'];
				}
				
				$page['page_users'] = implode(', ', $usernames);
			}
			
			if (!empty($page['page_admins']))
			{
				$userIDs = explode(',', $page['page_admins']);
				if (in_array($visitor['user_id'], $userIDs)) { $this->perms['edit'] = true; $this->perms['masks'] = true; $override = true; }
				
				$users = $this->getModelFromCache('XenForo_Model_User')->getUsersByIds($userIDs);
				$usernames = array();
				
				foreach ($users AS $user)
				{
					$usernames[] = $user['username'];
				}
				
				$page['page_admins'] = implode(', ', $usernames);
			}
		}
		
		if (!$this->perms['edit']) { return $this->responseNoPermission(); }
		if (!$this->perms['admin'] && $page['page_protect'] && empty($override)) { return $this->responseNoPermission(); }
		
		$page['timestamp'] = XenForo_Application::$time;

		$attachmentParams = array(
			'hash' => md5(uniqid('', true)),
			'content_type' => 'wiki',
			'content_data' => array('page_id' => $page['page_id'])
		);
		$attachmentConstraints = $this->getModelFromCache('XenForo_Model_Attachment')->getAttachmentConstraints();
		$attachments = $this->getModelFromCache('XenForo_Model_Attachment')->getAttachmentsByContentId('wiki', $page['page_id']);

		if ($this->_request->isPost())
		{
			$input = $this->_input->filter(array(
				'attachment_hash' => XenForo_Input::STRING,
				'page_name' => XenForo_Input::STRING,
				'page_slugNew' => XenForo_Input::STRING,
				'page_type' => XenForo_Input::STRING,
				'page_parent' => XenForo_Input::UINT,
				'timestamp' => XenForo_Input::UINT,
				'submit' => XenForo_Input::STRING,
			));
			$input['page_id'] = $page['page_id'];
			$input['page_content'] = $this->getHelper('Editor')->getMessageText('page_content', $this->_input);
			$input['page_slug'] = $input['page_slugNew'];
			
			if ($this->perms['admin'])
			{
				$input = $this->_input->filter(array(
					'page_index' => XenForo_Input::UINT,
					'page_protect' => XenForo_Input::UINT,
					'page_sidebar' => XenForo_Input::UINT,
					'page_sublist' => XenForo_Input::UINT,
				)) + $input;
				
				$page['page_index'] = $input['page_index'];
				$page['page_protect'] = $input['page_protect'];
				$page['page_sidebar'] = $input['page_sidebar'];
				$page['page_sublist'] = $input['page_sublist'];
			}
			
			if ($this->perms['admin'] || $this->perms['masks'])
			{
				$input = $this->_input->filter(array(
					'page_groups' => array(XenForo_Input::UINT, array('array' => true)),
					'usernames' => XenForo_Input::STRING,
					'administrators' => XenForo_Input::STRING,
				)) + $input;
				$input['page_groups'] = implode(',', $input['page_groups']);
				
				$page['page_groups'] = $input['page_groups'];
				$page['page_users'] = $input['usernames'];
				$page['page_admins'] = $input['administrators'];
			}

			if ($input['page_content'] && $input['submit'])
			{
				if ($page['page_date'] > $input['timestamp'])
				{
					throw new XenForo_Exception(new XenForo_Phrase('page_has_been_edited'), true);
				}
			
				if (!XenForo_Captcha_Abstract::validateDefault($this->_input))
				{
					return $this->responseCaptchaFailed();
				}

				$page = $this->getModelFromCache('EWRcarta_Model_Pages')->updatePage($input);
				return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki', $page));
			}

			$attachmentParams['hash'] = $input['attachment_hash'];
			$page['page_name'] = $input['page_name'];
			$page['page_type'] = $input['page_type'];
			$page['page_parent'] = $input['page_parent'];
			$page['page_content'] = $input['page_content'];
			$page['timestamp'] = $input['timestamp'];

			$attachments += $this->getModelFromCache('XenForo_Model_Attachment')->getAttachmentsByTempHash($attachmentParams['hash']); 
		}

		$children = array($page['page_id'] => $page);
		$children = $this->getModelFromCache('EWRcarta_Model_Lists')->getPageList($page['page_id'], $children);
		$fullList = $this->getModelFromCache('EWRcarta_Model_Lists')->getPageList();

		foreach ($fullList AS &$list)
		{
			$list['disabled'] = array_key_exists($list['page_id'], $children) ? true : false;
		}
		
		$forums = array();

		foreach (XenForo_Application::get('options')->EWRcarta_wikiforum AS $forum)
		{
			if ($forum = $this->getModelFromCache('XenForo_Model_Forum')->getForumById($forum))
			{
				$forums[] = $forum;
			}
		}

		$viewParams = array(
            'attachmentParams' => $attachmentParams,
			'attachments' => $this->getModelFromCache('XenForo_Model_Attachment')->prepareAttachments($attachments),
            'attachmentConstraints' => $attachmentConstraints,
			'perms' => $this->perms,
			'page' => $page,
			'input' => !empty($input) ? $input : false,
			'forums' => !empty($forums) ? $forums : false,
			'thread' => !empty($page['thread_id']) ? $this->getModelFromCache('XenForo_Model_Thread')->getThreadById($page['thread_id']) : false,
			'captcha' => XenForo_Captcha_Abstract::createDefault(),
			'fullList' => $fullList,
			'groups' => $this->getModelFromCache('XenForo_Model_UserGroup')->getUserGroupOptions($page['page_groups']),
			'breadCrumbs' => array_reverse($this->getModelFromCache('EWRcarta_Model_Lists')->getCrumbs($page)),
		);

		return $this->responseView('EWRcarta_ViewPublic_PageEdit', 'EWRcarta_PageEdit', $viewParams);
	}

	public function actionDelete()
	{
		if (!$this->perms['delete']) { return $this->responseNoPermission(); }

		$pageSlug = $this->_input->filterSingle('page_slug', XenForo_Input::STRING);
		$pageParent = $this->_input->filterSingle('category_parent', XenForo_Input::UINT);

		if ($page = $this->getModelFromCache('EWRcarta_Model_Pages')->getPageBySlug($pageSlug))
		{
			if ($this->_request->isPost())
			{
				$page['page_parent'] = $this->_input->filterSingle('page_parent', XenForo_Input::UINT);

				$this->getModelFromCache('EWRcarta_Model_Pages')->deletePage($page);
			}
			else
			{
				$children = array($page['page_id'] => $page);
				$children = $this->getModelFromCache('EWRcarta_Model_Lists')->getPageList($page['page_id'], $children);
				$fullList = $this->getModelFromCache('EWRcarta_Model_Lists')->getPageList();

				foreach ($fullList AS &$list)
				{
					$list['disabled'] = array_key_exists($list['page_id'], $children) ? true : false;
				}

				$viewParams = array(
					'page' => $page,
					'fullList' => $fullList,
				);

				return $this->responseView('EWRcarta_ViewPublic_PageDelete', 'EWRcarta_PageDelete', $viewParams);
			}
		}

		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL_PERMANENT, XenForo_Link::buildPublicLink('wiki'));
	}

	public function actionLikes()
	{
		$pageSlug = $this->_input->filterSingle('page_slug', XenForo_Input::STRING);

		if (!$page = $this->getModelFromCache('EWRcarta_Model_Pages')->getPageBySlug($pageSlug))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki'));
		}

		$likes = $this->getModelFromCache('XenForo_Model_Like')->getContentLikes('wiki', $page['page_id']);

		if (!$likes)
		{
			return $this->responseError(new XenForo_Phrase('no_one_has_liked_this_post_yet'));
		}

		$viewParams = array(
			'page' => $page,
			'breadCrumbs' => array_reverse($this->getModelFromCache('EWRcarta_Model_Lists')->getCrumbs($page)),
			'likes' => $likes
		);

		return $this->responseView('EWRcarta_ViewPublic_PageLikes', 'EWRcarta_PageLikes', $viewParams);
	}

	public function actionLike()
	{
		if (!$this->perms['like']) { return $this->responseNoPermission(); }

		$pageSlug = $this->_input->filterSingle('page_slug', XenForo_Input::STRING);

		if (!$page = $this->getModelFromCache('EWRcarta_Model_Pages')->getPageBySlug($pageSlug))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki'));
		}

		$viewingID = XenForo_Visitor::getUserId();
		$existingLike = $this->getModelFromCache('XenForo_Model_Like')->getContentLikeByLikeUser('wiki', $page['page_id'], $viewingID);

		if ($this->_request->isPost())
		{
			if ($existingLike)
			{
				$latestUsers = $this->getModelFromCache('XenForo_Model_Like')->unlikeContent($existingLike);
			}
			else
			{
				$latestUsers = $this->getModelFromCache('XenForo_Model_Like')->likeContent('wiki', $page['page_id'], 0);
			}

			$liked = ($existingLike ? false : true);

			if ($this->_noRedirect() && $latestUsers !== false)
			{
				$page['likeUsers'] = $latestUsers;
				$page['likes'] += ($liked ? 1 : -1);
				$page['like_date'] = ($liked ? XenForo_Application::$time : 0);

				$viewParams = array(
					'page' => $page,
					'liked' => $liked,
				);

				return $this->responseView('EWRcarta_ViewPublic_PageLikeConfirmed', '', $viewParams);
			}
			else
			{
				return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki', $page));
			}
		}
		else
		{
			$viewParams = array(
				'page' => $page,
				'like' => $existingLike,
				'breadCrumbs' => array_reverse($this->getModelFromCache('EWRcarta_Model_Lists')->getCrumbs($page)),
			);

			return $this->responseView('EWRcarta_ViewPublic_PageLike', 'EWRcarta_PageLike', $viewParams);
		}
	}
	
	public function actionWatchConfirm()
	{
		if (!XenForo_Visitor::getUserId()) { return $this->responseNoPermission(); }
		
		$pageSlug = $this->_input->filterSingle('page_slug', XenForo_Input::STRING);
		if (!$page = $this->getModelFromCache('EWRcarta_Model_Pages')->getPageBySlug($pageSlug))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki'));
		}
		
		$pageWatch = $this->getModelFromCache('EWRcarta_Model_PageWatch')->getUserPageWatchByPageId(XenForo_Visitor::getUserId(), $page['page_id']);

		$viewParams = array(
			'page' => $page,
			'pageWatch' => $pageWatch,
		);

		return $this->responseView('EWRcarta_ViewPublic_PageWatch', 'EWRcarta_PageWatch', $viewParams);
	}
	
	public function actionWatch()
	{
		if (!XenForo_Visitor::getUserId()) { return $this->responseNoPermission(); }
		
		$this->_assertPostOnly();
		
		$pageSlug = $this->_input->filterSingle('page_slug', XenForo_Input::STRING);
		if (!$page = $this->getModelFromCache('EWRcarta_Model_Pages')->getPageBySlug($pageSlug))
		{
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('wiki'));
		}

		if ($this->_input->filterSingle('stop', XenForo_Input::STRING))
		{
			$newState = '';
		}
		else if ($this->_input->filterSingle('email_subscribe', XenForo_Input::UINT))
		{
			$newState = 'watch_email';
		}
		else
		{
			$newState = 'watch_no_email';
		}
		
		$this->getModelFromCache('EWRcarta_Model_PageWatch')->setPageWatchState(XenForo_Visitor::getUserId(), $page['page_id'], $newState);

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildPublicLink('wiki', $page),
			null,
			array('linkPhrase' => ($newState ? new XenForo_Phrase('unwatch_page') : new XenForo_Phrase('watch_page')))
		);
	}

	public static function getSessionActivityDetailsForList(array $activities)
	{
		$pageSlugs = array();
		foreach ($activities AS $activity)
		{
			if (!empty($activity['params']['page_slug']))
			{
				$pageSlugs[$activity['params']['page_slug']] = $activity['params']['page_slug'];
			}
		}

		$pageData = array();
		if ($pageSlugs)
		{
			$pageModel = XenForo_Model::create('EWRcarta_Model_Pages');
			$pages = $pageModel->getPagesBySlugs($pageSlugs);

			foreach ($pages AS $page)
			{
				$pageData[$page['page_slug']] = array(
					'title' => $page['page_name'],
					'url' => XenForo_Link::buildPublicLink('wiki', $page)
				);
			}
		}

        $output = array();
        foreach ($activities as $key => $activity)
		{
			$page = false;
			if (!empty($activity['params']['page_slug']))
			{
				$pageSlug = $activity['params']['page_slug'];
				if (isset($pageData[$pageSlug]))
				{
					$page = $pageData[$pageSlug];
				}
			}

			if ($page)
			{
				$output[$key] = array(new XenForo_Phrase('viewing_wiki'), $page['title'], $page['url'], false);
			}
			else
			{
				$output[$key] = new XenForo_Phrase('viewing_wiki');
			}
        }

        return $output;
	}

	protected function _preDispatch($action)
	{
		parent::_preDispatch($action);

		$this->perms = $this->getModelFromCache('EWRcarta_Model_Perms')->getPermissions();

		if (!$this->perms['view']) { throw $this->getNoPermissionResponseException(); }
	}
}