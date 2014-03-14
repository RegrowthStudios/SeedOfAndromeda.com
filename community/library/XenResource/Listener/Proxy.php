<?php

class XenResource_Listener_Proxy
{
	protected static $_addedUsernameChange = false;

	public static function loadModeratorModel($class, array &$extend)
	{
		$extend[] = 'XenResource_Listener_Proxy_ModelModerator';
	}

	public static function loadUserModel($class, array &$extend)
	{
		$extend[] = 'XenResource_Listener_Proxy_ModelUser';

		if (!self::$_addedUsernameChange)
		{
			self::$_addedUsernameChange = true;
			XenForo_Model_User::$userContentChanges['xf_resource'] = array(array('user_id', 'username'));
			XenForo_Model_User::$userContentChanges['xf_resource_download'] = array(array('user_id'));
			XenForo_Model_User::$userContentChanges['xf_resource_rating'] = array(array('user_id'));
			XenForo_Model_User::$userContentChanges['xf_resource_watch'] = array(array('user_id'));
		}
	}

	public static function loadMemberController($class, array &$extend)
	{
		$extend[] = 'XenResource_Listener_Proxy_ControllerMember';
	}

	public static function loadWatchedController($class, array &$extend)
	{
		$extend[] = 'XenResource_Listener_Proxy_ControllerWatched';
	}

	public static function dependencies(XenForo_Dependencies_Abstract $dependencies, array $data)
	{
		XenForo_Template_Helper_Core::$helperCallbacks['resourcefieldtitle'] = array('XenResource_ViewPublic_Helper_Resource', 'getResourceFieldTitle');
		XenForo_Template_Helper_Core::$helperCallbacks['resourcefieldvalue'] = array('XenResource_ViewPublic_Helper_Resource', 'getResourceFieldValueHtml');
		XenForo_Template_Helper_Core::$helperCallbacks['resourceprefix'] = array('XenResource_ViewPublic_Helper_Resource', 'getResourcePrefixTitle');
		XenForo_Template_Helper_Core::$helperCallbacks['resourceprefixgroup'] = array('XenResource_ViewPublic_Helper_Resource', 'getResourcePrefixGroupTitle');
		XenForo_Template_Helper_Core::$helperCallbacks['resourceiconurl'] = array('XenResource_ViewPublic_Helper_Resource', 'getResourceIconUrl');
	}

	public static function userCriteria($rule, array $data, array $user, &$returnValue)
	{
		switch ($rule)
		{
			case 'resource_count':
				if (isset($user['resource_count']) && $user['resource_count'] >= $data['resources'])
				{
					$returnValue = true;
				}
			break;
		}
	}
}