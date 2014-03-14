<?php

class Tinhte_MoreXenForoPermissions_ControllerPublic_Member extends XFCP_Tinhte_MoreXenForoPermissions_ControllerPublic_Member
{
	protected function _preDispatch($action)
	{
		parent::_preDispatch($action);
	
		switch ($action)
		{
			case 'Index':
				$userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
				$username = $this->_input->filterSingle('username', XenForo_Input::STRING);
				
				if ((!$userId && !$this->_input->inRequest('user_id') && $username === '') &&
						!XenForo_Visitor::getInstance()->hasPermission('TinhTe_MXP', 'member_list'))
				{
					throw $this->getNoPermissionResponseException();
				}
				break;
		}
	}
}
