<?php

class EWRporta_Block_Install_Donations
{
	private static $_instance;
	protected $_db;

	public static final function getInstance()
	{
		if (!self::$_instance)
		{
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	protected function _getDb()
	{
		if ($this->_db === null)
		{
			$this->_db = XenForo_Application::get('db');
		}

		return $this->_db;
	}

	public static function installCode($existingAddOn, $addOnData)
	{
		$endVersion = $addOnData['version_id'];
		$strVersion = $existingAddOn ? ($existingAddOn['version_id'] + 1) : 1;

		$install = self::getInstance();

		for ($i = $strVersion; $i <= $endVersion; $i++)
		{
			$method = '_install_'.$i;

			if (method_exists($install, $method))
			{
				$install->$method();
			}
		}
	}

	protected function _install_1()
	{
 		$this->_getDb()->query("
			CREATE TABLE IF NOT EXISTS `EWRporta_donations` (
				`donation_id`				int(10) unsigned NOT NULL AUTO_INCREMENT,
				`drive_id`					varchar(25) NOT NULL,
				`amount`					float NOT NULL DEFAULT '0',
				`user_id`					int(10) unsigned NOT NULL,
				`transaction_id`			varchar(50) NOT NULL,
				`transaction_type`			enum('payment','cancel','info','error') NOT NULL,
				`message`					varchar(255) NOT NULL DEFAULT '',
				`transaction_details`		mediumblob NOT NULL,
				`log_date`					int(10) unsigned NOT NULL DEFAULT '0',
				PRIMARY KEY (`donation_id`)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
		");
	}

	public static function uninstallCode()
	{
		$uninstall = self::getInstance();
		$uninstall->_uninstall_0();
	}

	protected function _uninstall_0()
	{
 		$this->_getDb()->query("DROP TABLE IF EXISTS `EWRporta_donations`");
	}
}