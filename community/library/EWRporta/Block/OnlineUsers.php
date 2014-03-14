<?php

class EWRporta_Block_OnlineUsers extends XenForo_Model
{
	public function getBypass($params)
	{
		$visitor = XenForo_Visitor::getInstance();
		$sessionModel = $this->getModelFromCache('XenForo_Model_Session');

		$onlineUsers = $sessionModel->getSessionActivityQuickList(
			$visitor->toArray(),
			array('cutOff' => array('>', $sessionModel->getOnlineStatusTimeout())),
			($visitor['user_id'] ? $visitor->toArray() : null)
		);

		$onlineUsers['most_users'] = XenForo_Application::getSimpleCacheData('EWRporta_MostUsers');

		if (empty($onlineUsers['most_users']) || $onlineUsers['total'] > $onlineUsers['most_users']['total'])
		{
			$onlineUsers['most_users'] = array('total' => $onlineUsers['total'], 'time' => XenForo_Application::$time);
            XenForo_Application::setSimpleCacheData('EWRporta_MostUsers', $onlineUsers['most_users']);
		}

		if (!$params['option']['staff'])
		{
			foreach ($onlineUsers['records'] AS &$user)
			{
				$user['is_staff'] = false;
			}
		}

		return $onlineUsers;
	}
}