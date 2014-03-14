<?php

class EWRporta_ControllerPublic_Thread extends XFCP_EWRporta_ControllerPublic_Thread
{
	public $perms;

	public function actionIndex()
	{
		$response = parent::actionIndex();
		$options = XenForo_Application::get('options');
		$format = $this->_input->filterSingle('format', XenForo_Input::STRING);
		$response->params['thread']['format'] = $format;

		if ($format != 'default')
		{
			if ($response instanceof XenForo_ControllerResponse_View && $options->EWRporta_globalize['article']
				&& (
					in_array($response->params['forum']['node_id'], $this->getModelFromCache('EWRporta_Model_Promotes')->getPromoteForums())
					|| $this->getModelFromCache('EWRporta_Model_Promotes')->getPromoteByThreadId($response->params['thread']['thread_id'])
				))
			{
				$response->params['isArticle'] = true;
				$response->params['layout1'] = 'article-'.$response->params['thread']['thread_id'];
				$response->params['layout2'] = 'article';
				$response->params['layout3'] = 'portal';
				$response->params['categories'] = $this->getModelFromCache('EWRporta_Model_Categories')->getCategoryLinks($response->params['thread']);

				return $this->responseView('XenForo_ViewPublic_Thread_View', 'EWRporta_ArticleView', $response->params);
			}

			if ($response instanceof XenForo_ControllerResponse_View && $options->EWRporta_globalize['thread'])
			{
				$response->params['layout1'] = 'thread-'.$response->params['thread']['thread_id'];
				$response->params['layout2'] = 'thread-forum-'.$response->params['forum']['node_id'];
				$response->params['layout3'] = 'thread';
			}
		}

		return $response;
	}

	public function actionCategory()
	{
		if (!$this->perms['promote']) { return $this->responseNoPermission(); }

		if ($this->_request->isPost())
		{
			$input = $this->_input->filter(array(
				'thread_id' => XenForo_Input::UINT,
				'newlinks' => XenForo_Input::ARRAY_SIMPLE,
				'catlinks' => XenForo_Input::ARRAY_SIMPLE,
				'oldlinks' => XenForo_Input::ARRAY_SIMPLE
			));

			$this->getModelFromCache('EWRporta_Model_Categories')->updateCategories($input);
			return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('threads', $input));
		}

		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);

		$viewParams = array(
			'thread' => $thread,
			'catlinks' => $this->getModelFromCache('EWRporta_Model_Categories')->getCategoryLinks($thread),
			'categories' => $this->getModelFromCache('EWRporta_Model_Categories')->getCategoryNolinks($thread),
		);

		return $this->responseView('EWRporta_ViewPublic_Category', 'EWRporta_Category', $viewParams);
	}

	public function actionPromote()
	{
		if (!$this->perms['promote']) { return $this->responseNoPermission(); }

		$input = $this->_input->filter(array(
			'thread_id' => XenForo_Input::UINT,
			'promote_date' => XenForo_Input::UINT,
			'promote_icon' => XenForo_Input::STRING,
			'attach_data' => XenForo_Input::UINT,
			'image_data' => XenForo_Input::STRING,
			'medio_data' => XenForo_Input::UINT,
			'date' => XenForo_Input::STRING,
			'hour' => XenForo_Input::UINT,
			'mins' => XenForo_Input::UINT,
			'ampm' => XenForo_Input::STRING,
			'zone' => XenForo_Input::STRING,
			'delete' => XenForo_Input::STRING,
		));

		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($input['thread_id']);

		if ($this->_request->isPost())
		{
			$this->getModelFromCache('EWRporta_Model_Promotes')->updatePromotion($input);
			$this->getModelFromCache('EWRporta_Model_Caches')->emptyBlockCache(array('block_id'=>'RecentFeatures'));
			$this->getModelFromCache('EWRporta_Model_Caches')->emptyBlockCache(array('block_id'=>'RecentNews'));
		}
		else
		{
			$threadPromote = $this->getModelFromCache('EWRporta_Model_Promotes')->getPromoteByThreadId($thread['thread_id']);

			$visitor = XenForo_Visitor::getInstance();
			$datetime = $threadPromote ? $threadPromote['promote_date'] : $thread['post_date'];
			$datetime = new DateTime(date('r', $datetime));
			$datetime->setTimezone(new DateTimeZone($visitor['timezone']));
			$datetime = explode('.', $datetime->format('Y-m-d.h.i.A.T'));

			$datetime = array(
				'date' => $datetime[0],
				'hour' => $datetime[1],
				'mins' => $datetime[2],
				'meri' => $datetime[3],
				'zone' => $datetime[4]
			);

			$icons = $this->getModelFromCache('EWRporta_Model_Promotes')->getPromoteIcons($thread);

			$viewParams = array(
				'thread' => $thread,
				'icons' => $icons,
				'threadPromote' => $threadPromote,
				'datetime' => $datetime,
				'nodeBreadCrumbs' => $ftpHelper->getNodeBreadCrumbs($forum),
			);

			return $this->responseView('EWRporta_ViewPublic_Promote', 'EWRporta_Promote', $viewParams);
		}

		return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, XenForo_Link::buildPublicLink('threads', $thread));
	}

	protected function _preDispatch($action)
	{
		parent::_preDispatch($action);

		$this->perms = $this->getModelFromCache('EWRporta_Model_Perms')->getPermissions();
	}
}