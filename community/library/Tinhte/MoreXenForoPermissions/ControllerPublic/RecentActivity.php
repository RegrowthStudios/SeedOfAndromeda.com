<?php

class Tinhte_MoreXenForoPermissions_ControllerPublic_RecentActivity extends XFCP_Tinhte_MoreXenForoPermissions_ControllerPublic_RecentActivity
{
	protected function _preDispatch($action)
	{
		parent::_preDispatch($action);
	
		switch ($action)
		{
			case 'Index':
				if (!XenForo_Visitor::getInstance()->hasPermission('TinhTe_MXP', 'recent_activity'))
				{
					throw $this->getNoPermissionResponseException();
				}
				break;
		}
	}
}