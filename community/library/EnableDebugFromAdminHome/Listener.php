<?php
  
class EnableDebugFromAdminHome_Listener
{
	public static function extendControllers($class, &$extend)
	{
		if ($class == 'XenForo_ControllerAdmin_Home')
		{
			$extend[] = 'EnableDebugFromAdminHome_ControllerAdmin_Home';
		}
		
		if ($class == 'XenForo_ControllerAdmin_Tools')
		{
			$extend[] = 'EnableDebugFromAdminHome_ControllerAdmin_Tools';
		}		
	}
	
	public static function templateHook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
	{
		if ($hookName == 'admin_sidebar_home')
		{
			$visitor = XenForo_Visitor::getInstance();
			
			if ($visitor->hasAdminPermission('enableDebugMode'))
			{
				$params = $template->getParams();
				$params += $hookParams;
				
				$contents = $template->create('home_debug_switch', $params) . $contents;
			}
		}
	}
	
	public static function initDependencies(XenForo_Dependencies_Abstract $dependencies, array $data)
	{
		$debugState = XenForo_Application::getSimpleCacheData('debugMode');
		
		XenForo_Application::setDebugMode($debugState);
		
		if ($debugState)
		{
			XenForo_Application::getDb()->setProfiler(true);
		}
	}	
}