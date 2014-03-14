<?php

class XenResource_Listener_Proxy_ModelModerator extends XFCP_XenResource_Listener_Proxy_ModelModerator
{
	public function getGeneralModeratorInterfaceGroupIds()
	{
		$ids = parent::getGeneralModeratorInterfaceGroupIds();
		$ids[] = 'resourceModeratorPermissions';

		return $ids;
	}
}