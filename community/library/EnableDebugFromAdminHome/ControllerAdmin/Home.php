<?php

class EnableDebugFromAdminHome_ControllerAdmin_Home extends XFCP_EnableDebugFromAdminHome_ControllerAdmin_Home
{
	public function actionIndex()
	{
		$parent = parent::actionIndex();
		
		$debugState = XenForo_Application::getSimpleCacheData('debugMode');
			
		$parent->params['debugState'] = $debugState;
		
		return $parent;
	}
}
