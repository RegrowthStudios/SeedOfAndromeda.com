<?php

class LiamOfficialPosts_ControllerPublic_Post extends XFCP_LiamOfficialPosts_ControllerPublic_Post
{

	public function actionEdit()
	{
		$response = parent::actionEdit();
		
		if ($response instanceof XenForo_ControllerResponse_View)
		{
			$visitor = XenForo_Visitor::getInstance();
			$response->params['markOfficial'] = $visitor->hasNodePermission($response->params['forum']['node_id'], 'markPostOfficial');
		}
		
		return $response;
	}

	public function actionSave()
	{
		$this->_assertPostOnly();
		
		$postId = $this->_input->filterSingle('post_id', XenForo_Input::UINT);
		$ftpHelper = $this->getHelper('ForumThreadPost');
		list ($post, $thread, $forum) = $ftpHelper->assertPostValidAndViewable($postId);
		
		$visitor = XenForo_Visitor::getInstance();
		$nodeId = $forum['node_id'];
		
		if ($visitor->hasNodePermission($nodeId, 'markPostOfficial'))
		{
			$officialPost = $this->_input->filterSingle('official_post', XenForo_Input::BOOLEAN);
			XenForo_Application::set('liam_officialpost', $officialPost);
		}
		
		return parent::actionSave();
	}

}