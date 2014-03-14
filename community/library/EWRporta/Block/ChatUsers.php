<?php

class EWRporta_Block_ChatUsers extends XenForo_Model
{
	public function getModule()
	{
		if ((!$addon = $this->getModelFromCache('XenForo_Model_AddOn')->getAddOnById('EWRhabla')) || empty($addon['active']))
		{
			return "killModule";
		}

		$options = XenForo_Application::get('options');
		$server = $options->EWRhabla_server;
		$channel = $options->EWRhabla_channel;
		$chatUsers = array('total' => 0);

		if (!$chatUsers['rooms'] = $this->getModelFromCache('EWRhabla_Model_Chat')->fetchUsers($server, $channel))
		{
			return array('rooms' => array());
		}

		foreach ($chatUsers['rooms'] AS $room)
		{
			$chatUsers['total'] += $room['count'];
		}

		$chatUsers['most_users'] = XenForo_Application::getSimpleCacheData('EWRporta_ChatUsers');

		if (empty($chatUsers['most_users']) || $chatUsers['total'] > $chatUsers['most_users']['total'])
		{
			$chatUsers['most_users'] = array('total' => $chatUsers['total'], 'time' => XenForo_Application::$time);
            XenForo_Application::setSimpleCacheData('EWRporta_ChatUsers', $chatUsers['most_users']);
		}

		return $chatUsers;
	}
}