<?php

class LiamOfficialPosts_ControllerPublic_Forum extends XFCP_LiamOfficialPosts_ControllerPublic_Forum
{

	public function actionCreateThread()
	{
		$response = parent::actionCreateThread();
		
		if ($response instanceof XenForo_ControllerResponse_View)
		{
			$visitor = XenForo_Visitor::getInstance();
			$response->params['markOfficial'] = $visitor->hasNodePermission($response->params['forum']['node_id'], 'markPostOfficial');
		}
		
		return $response;
	}

	public function actionAddThread()
	{
		$visitor = XenForo_Visitor::getInstance();
		$nodeId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);
		
		if ($visitor->hasNodePermission($nodeId, 'markPostOfficial'))
		{
			$officialPost = $this->_input->filterSingle('official_thread', XenForo_Input::BOOLEAN);
			XenForo_Application::set('liam_officialpost', $officialPost);
		}
		
		return parent::actionAddThread();
	}

}