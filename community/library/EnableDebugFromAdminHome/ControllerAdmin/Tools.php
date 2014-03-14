<?php

class EnableDebugFromAdminHome_ControllerAdmin_Tools extends XFCP_EnableDebugFromAdminHome_ControllerAdmin_Tools
{
	public function actionToggleDebug()
	{
		$this->_assertPostOnly();
		
		$this->assertAdminPermission('enableDebugMode');
		
		$debugToggle = $this->_input->filterSingle('enable_debug', XenForo_Input::UINT);
		
		XenForo_Application::setSimpleCacheData('debugMode', $debugToggle);
		
		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
			XenForo_Link::buildAdminLink('index')
		);
	}
}