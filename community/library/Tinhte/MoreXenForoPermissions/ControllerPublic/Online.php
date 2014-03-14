<?php

class Tinhte_MoreXenForoPermissions_ControllerPublic_Online extends XFCP_Tinhte_MoreXenForoPermissions_ControllerPublic_Online
{
	protected function _preDispatch($action)
	{
		parent::_preDispatch($action);
		
		switch ($action)
		{
			case 'Index':
			case 'UserIp':
			case 'GuestIp':
				if (!XenForo_Visitor::getInstance()->hasPermission('TinhTe_MXP', 'controller_online'))
				{
					throw $this->getNoPermissionResponseException();
				}
				break;
		}
	}
}