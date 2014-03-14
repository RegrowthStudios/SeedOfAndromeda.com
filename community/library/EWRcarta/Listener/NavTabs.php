<?php

class EWRcarta_Listener_NavTabs
{
	public static function listen(array &$extraTabs, $selectedTabId)
	{
		$permsModel = XenForo_Model::create('EWRcarta_Model_Perms');
		$perms = $permsModel->getPermissions();

		$indexModel = XenForo_Model::create('EWRcarta_Model_Lists');
		$index = $indexModel->getIndex();

		$extraTabs['wiki'] = array(
			'title' => new XenForo_Phrase('wiki'),
			'href' => XenForo_Link::buildPublicLink('full:wiki'),
			'position' => 'middle',
			'linksTemplate' => 'EWRcarta_Navtabs',
			'perms' => $perms,
			'index' => $index,
		);
	}
}