<?php

class EWRporta_Listener_NavTabs
{
	public static function listen(array &$extraTabs, $selectedTabId)
	{
		if (XenForo_Application::get('options')->EWRporta_shownavtab)
		{
			$permsModel = XenForo_Model::create('EWRporta_Model_Perms');
			$perms = $permsModel->getPermissions();

			$extraTabs['portal'] = array(
				'title' => new XenForo_Phrase('home'),
				'href' => XenForo_Link::buildPublicLink('full:portal'),
				'position' => 'home',
				'linksTemplate' => 'EWRporta_Navtabs',
				'perms' => $perms,
			);
		}
	}
}