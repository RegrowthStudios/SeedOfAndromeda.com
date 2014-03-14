<?php

/**
 * @extends XenForo_ControllerPublic_Member
 */
class XenResource_Listener_Proxy_ControllerMember extends XFCP_XenResource_Listener_Proxy_ControllerMember
{
	protected function _getNotableMembers($type, $limit)
	{
		/** @var $this XenForo_ControllerPublic_Member */

		if ($type == 'resources' && XenForo_Visitor::getInstance()->hasPermission('resource', 'view'))
		{
			$userModel = $this->_getUserModel();

			$notableCriteria = array(
				'is_banned' => 0
			);
			return array($userModel->getUsers($notableCriteria, array(
				'join' => XenForo_Model_User::FETCH_USER_FULL,
				'limit' => $limit,
				'order' => 'resource_count',
				'direction' => 'desc'
			)), 'resource_count');
		}

		return parent::_getNotableMembers($type, $limit);
	}
}