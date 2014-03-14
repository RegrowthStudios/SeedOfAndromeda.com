<?php
  
class EnableDebugFromAdminHome_Install
{
	public static function uninstaller()
	{
		$debugState = XenForo_Application::setSimpleCacheData('debugMode', false);
		
		XenForo_Application::setDebugMode(false);		
	}
}