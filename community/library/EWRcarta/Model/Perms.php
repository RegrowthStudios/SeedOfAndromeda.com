<?php

class EWRcarta_Model_Perms extends XenForo_Model
{
	public function getPermissions(array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);

		$perms['view'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'EWRcarta', 'canView') ? true : false);
		$perms['history'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'EWRcarta', 'canHistory') ? true : false);
		$perms['like'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'EWRcarta', 'canLike') ? true : false);
		$perms['edit'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'EWRcarta', 'canEdit') ? true : false);
		$perms['create'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'EWRcarta', 'canCreate') ? true : false);
		$perms['manage'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'EWRcarta', 'canManage') ? true : false);
		$perms['delete'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'EWRcarta', 'canDelete') ? true : false);
		$perms['attach'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'EWRcarta', 'canAttach') ? true : false);
		$perms['admin'] = (XenForo_Permission::hasPermission($viewingUser['permissions'], 'EWRcarta', 'canAdmin') ? true : false);
		$perms['masks'] = false;

		return $perms;
	}
}