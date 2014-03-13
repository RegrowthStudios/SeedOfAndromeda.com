<?php

class LiamOfficialPosts_ControllerPublic_Thread extends XFCP_LiamOfficialPosts_ControllerPublic_Thread
{

	public function actionIndex()
	{
		$response = parent::actionIndex();
		
		if ($response instanceof XenForo_ControllerResponse_View)
		{
			$visitor = XenForo_Visitor::getInstance();
			$response->params['markOfficial'] = $visitor->hasNodePermission($response->params['forum']['node_id'], 'markPostOfficial');
		}
		
		return $response;
	}

	public function actionReply()
	{
		$response = parent::actionReply();
		
		$visitor = XenForo_Visitor::getInstance();
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		$ftpHelper = $this->getHelper('ForumThreadPost');
		list ($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		$nodeId = $forum['node_id'];
		
		if ($response instanceof XenForo_ControllerResponse_View)
		{
			$visitor = XenForo_Visitor::getInstance();
			$response->params['markOfficial'] = $visitor->hasNodePermission($response->params['forum']['node_id'], 'markPostOfficial');
		}
		
		return $response;
	}

	public function actionAddReply()
	{
		$threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);
		
		$visitor = XenForo_Visitor::getInstance();
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		list ($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);
		
		$nodeId = $forum['node_id'];
		
		if ($visitor->hasNodePermission($nodeId, 'markPostOfficial'))
		{
			$officialPost = $this->_input->filterSingle('official_post', XenForo_Input::BOOLEAN);
			XenForo_Application::set('liam_officialpost', $officialPost);
		}
		
		return parent::actionAddReply();
	}

}