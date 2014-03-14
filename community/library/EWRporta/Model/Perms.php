<?php

class EWRporta_Model_Perms extends XenForo_Model
{
	public function getPermissions(array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);

		$perms['custom'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'EWRporta', 'canCustom') ? true : false);
		$perms['promote'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'EWRporta', 'canPromote') ? true : false);

		return $perms;
	}
}