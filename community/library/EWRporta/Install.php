<?php

class EWRporta_Install
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
		$strVersion = $existingAddOn ? ($existingAddOn['version_id'] + 1) : 50;

		if ($strVersion < 50)
		{
			throw new XenForo_Exception('You must uninstall the previous version of <b>[8wayRun.Com] XenPorta (Portal)</b> before you install this new version...', true);
		}

		$install = self::getInstance();

		for ($i = $strVersion; $i <= $endVersion; $i++)
		{
			$method = '_install_'.$i;

			if (method_exists($install, $method))
			{
				$install->$method();
			}
		}

		$blocksModel = XenForo_Model::create('EWRporta_Model_Blocks');
		$rootDir = XenForo_Application::getInstance()->getRootDir();

		if ($handle = opendir($xmlDir = $rootDir.'/library/EWRporta/XML'))
		{
			while (false !== ($file = readdir($handle)))
			{
				if (stristr($file,'xml'))
				{
					$blocksModel->installBlockXmlFromFile($xmlDir.'/'.$file);
				}
			}
			opendir($xmlDir);
		}

		if ($handle = opendir($xmlDir = $rootDir.'/library/EWRporta/Block/XML'))
		{
			while (false !== ($file = readdir($handle)))
			{
				if (stristr($file,'xml'))
				{
					$blockId = str_ireplace('.xml', '', $file);

					if ($blocksModel->getBlockById($blockId))
					{
						$blocksModel->installBlockXmlFromFile($xmlDir.'/'.$file);
					}
				}
			}
			opendir($xmlDir);
		}
	}

	protected function _install_50()
	{
 		$this->_getDb()->query("
			CREATE TABLE IF NOT EXISTS `EWRporta_blocks` (
				`block_id`						varchar(25) NOT NULL,
				`title`							varchar(75) NOT NULL,
				`version_string`				varchar(30) NOT NULL,
				`version_id`					int(10) unsigned NOT NULL DEFAULT '0',
				`url`							varchar(100) NOT NULL,
				`install_callback_class`		varchar(75) NOT NULL,
				`install_callback_method`		varchar(75) NOT NULL,
				`uninstall_callback_class`		varchar(75) NOT NULL,
				`uninstall_callback_method`		varchar(75) NOT NULL,
				`cache`							varchar(255) NOT NULL DEFAULT '+10 minutes',
				`display`						enum('show','hide') NOT NULL,
				`groups`						varchar(255) NOT NULL,
				`locked`						tinyint(3) unsigned NOT NULL DEFAULT '0',
				`active`						tinyint(3) unsigned NOT NULL DEFAULT '1',
				PRIMARY KEY (`block_id`),
				KEY `title` (`title`)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
		");

 		$this->_getDb()->query("
			CREATE TABLE IF NOT EXISTS `EWRporta_caches` (
				`block_id`				varchar(25) NOT NULL,
				`date`					int(10) unsigned NOT NULL,
				`results`				mediumtext NOT NULL,
				PRIMARY KEY (`block_id`)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
		");

 		$this->_getDb()->query("
			CREATE TABLE IF NOT EXISTS `EWRporta_layouts` (
				`layout_id`				varchar(25) NOT NULL,
				`blocks`				mediumblob NOT NULL,
				PRIMARY KEY (`layout_id`)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
		");

 		$this->_getDb()->query("
			CREATE TABLE IF NOT EXISTS `EWRporta_options` (
				`option_id`				varchar(50) NOT NULL,
				`title`					varchar(100) NOT NULL,
				`explain`				mediumtext NOT NULL,
				`option_value`			mediumblob NOT NULL,
				`edit_format`			enum('textbox','spinbox','onoff','radio','select','checkbox','template','callback') NOT NULL,
				`edit_format_params`	mediumtext NOT NULL,
				`data_type`				enum('string','integer','numeric','array','boolean','positive_integer','unsigned_integer','unsigned_numeric') NOT NULL,
				`sub_options`			mediumtext NOT NULL,
				`validation_class`		varchar(75) NOT NULL,
				`validation_method`		varchar(50) NOT NULL,
				`display_order`			int(10) unsigned NOT NULL DEFAULT '0',
				`block_id`				varchar(25) NOT NULL,
				PRIMARY KEY (`option_id`)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
		");

 		$this->_getDb()->query("DROP TABLE IF EXISTS `EWRporta_promotes`");
 		$this->_getDb()->query("
			CREATE TABLE IF NOT EXISTS `EWRporta_promotes` (
				`thread_id`				int(10) unsigned NOT NULL,
				`promote_date`			int(10) unsigned NOT NULL,
				`promote_icon`			enum('default','avatar','attach','image','medio','disabled') NOT NULL DEFAULT 'default',
				`promote_data`			varchar(1024) NOT NULL DEFAULT '0',
				PRIMARY KEY (`thread_id`)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
		");
	}

	protected function _install_52()
	{
 		$this->_getDb()->query("
			CREATE TABLE IF NOT EXISTS `EWRporta_categories` (
				`style_id`				int(10) unsigned NOT NULL,
				`category_id`			int(10) unsigned NOT NULL AUTO_INCREMENT,
				`category_slug`			varchar(255) NOT NULL,
				`category_name`			varchar(255) NOT NULL,
				`category_type`			enum('major', 'minor') NOT NULL DEFAULT 'major',
				PRIMARY KEY (`category_id`),
				UNIQUE KEY (`category_slug`)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
		");

 		$this->_getDb()->query("
			CREATE TABLE IF NOT EXISTS `EWRporta_catlinks` (
				`category_id`			int(10) unsigned NOT NULL,
				`thread_id`				int(10) unsigned NOT NULL,
				`catlink_id`			int(10) unsigned NOT NULL AUTO_INCREMENT,
				PRIMARY KEY (`catlink_id`),
				UNIQUE KEY `category_id` (`category_id`,`thread_id`)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
		");
	}

	protected function _install_58()
	{
		$this->addColumnIfNotExist("EWRporta_categories", "style_id", "int(10) unsigned NOT NULL");
		$this->addColumnIfNotExist("EWRporta_categories", "category_type", "enum('major', 'minor') NOT NULL DEFAULT 'major'");
	}

	public static function uninstallCode($addOnData)
	{
		$endVersion = $addOnData['version_id'];
		$uninstall = self::getInstance();

		if ($endVersion < 50)
		{
			$uninstall->_uninstall_0();
		}
		else
		{
			$uninstall->_uninstall_50();
		}
	}

	protected function _uninstall_0()
	{
		$db = XenForo_Application::get('db');

		$modules = XenForo_Model::create('EWRporta_Model_Modules')->getModules();

		foreach ($modules AS $module)
		{
			XenForo_Model::create('EWRporta_Model_Modules')->deleteModule($module);
		}

 		$db->query("
			DROP TABLE IF EXISTS
				`EWRporta_cache`,
				`EWRporta_modules`,
				`EWRporta_promotes`,
				`EWRporta_settings`
		");
	}

	protected function _uninstall_50()
	{
		$blocks = XenForo_Model::create('EWRporta_Model_Blocks')->getAllBlocks();

		foreach ($blocks AS $block)
		{
			XenForo_Model::create('EWRporta_Model_Blocks')->uninstallBlock($block);
		}

 		$this->_getDb()->query("
			DROP TABLE IF EXISTS
				`EWRporta_blocks`,
				`EWRporta_caches`,
				`EWRporta_layouts`,
				`EWRporta_options`,
				`EWRporta_promotes`
		");
	}

	public function addColumnIfNotExist($table, $field, $attr)
	{
		if ($this->_getDb()->fetchRow('SHOW columns FROM `'.$table.'` WHERE Field = ?', $field))
		{
			return false;
		}
		
		return $this->_getDb()->query("ALTER TABLE `".$table."` ADD `".$field."` ".$attr);
	}
}