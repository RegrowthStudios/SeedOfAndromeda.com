<?php

abstract class LiamOfficialPosts_Addon
{

	public static function install($installedAddon)
	{
		if (XenForo_Application::$versionId < 1020070)
		{
			throw new XenForo_Exception('This addon required XenForo 1.2 or later.', true);
		}
		
		$version = is_array($installedAddon) ? $installedAddon['version_id'] : 0;
		

		if ($version == 0)
		{
			$db = XenForo_Application::getDb();
			$db->query("ALTER TABLE `xf_post` ADD COLUMN `official_post` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Added by Official Posts'");
		}
	}

	public static function uninstall()
	{
		$db = XenForo_Application::getDb();
		$db->query("ALTER TABLE `xf_post` DROP COLUMN `official_post`");
	}

	public static function extend($class, array &$extend)
	{
		switch ($class) {
			case "XenForo_DataWriter_DiscussionMessage_Post":
				$extend[] = 'LiamOfficialPosts_DataWriter_DiscussionMessage_Post';
				break;
			case "XenForo_ControllerPublic_Forum":
				$extend[] = 'LiamOfficialPosts_ControllerPublic_Forum';
				break;
			case "XenForo_ControllerPublic_Thread":
				$extend[] = 'LiamOfficialPosts_ControllerPublic_Thread';
				break;
			case "XenForo_ControllerPublic_Post":
				$extend[] = 'LiamOfficialPosts_ControllerPublic_Post';
				break;
		}
	}

}