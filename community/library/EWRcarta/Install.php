<?php

class EWRcarta_Install
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
			CREATE TABLE IF NOT EXISTS `EWRcarta_cache` (
				`page_id`			int(10) unsigned					NOT NULL,
				`cache_date`		int(10) unsigned					NOT NULL,
				`cache_content`		mediumtext							NOT NULL,
				PRIMARY KEY (`page_id`)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
		");

 		$this->_getDb()->query("
			CREATE TABLE IF NOT EXISTS `EWRcarta_history` (
				`page_id`			int(10) unsigned					NOT NULL,
				`user_id`			int(10) unsigned					NOT NULL,
				`username`			varchar(50)							NOT NULL,
				`history_id`		int(10) unsigned					NOT NULL AUTO_INCREMENT,
				`history_date`		int(10) unsigned					NOT NULL,
				`history_type`		enum('bbcode', 'html', 'phpfile')	NOT NULL,
				`history_content`	mediumtext							NOT NULL,
				`history_current`	int(1) unsigned						NOT NULL DEFAULT '0',
				`history_revert`	int(1) unsigned						NOT NULL DEFAULT '0',
				`history_ip`		int(10) unsigned					NOT NULL DEFAULT '0',
				PRIMARY KEY (`history_id`),
				INDEX (`page_id`),
				INDEX (`user_id`)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
		");
		
 		$this->_getDb()->query("
			CREATE TABLE IF NOT EXISTS `EWRcarta_pages` (
				`page_id`			int(10) unsigned					NOT NULL AUTO_INCREMENT,
				`page_slug`			varchar(100)						NOT NULL,
				`page_name`			varchar(100)						NOT NULL,
				`page_date` 		int(10) unsigned					NOT NULL,
				`page_type` 		enum('bbcode', 'html', 'phpfile')	NOT NULL DEFAULT 'bbcode',
				`page_content`		mediumtext							NOT NULL,
				`page_parent`		int(10) unsigned					NOT NULL DEFAULT '0',
				`page_index`		int(10) unsigned					NOT NULL DEFAULT '0',
				`page_protect`		int(1) unsigned						NOT NULL DEFAULT '0',
				`page_sidebar`		int(1) unsigned						NOT NULL DEFAULT '1',
				`page_sublist`		int(1) unsigned						NOT NULL DEFAULT '1',
				`page_likes`		int(10) unsigned					NOT NULL DEFAULT '0',
				`page_like_users`	blob								NOT NULL,
				`page_views`		int(10) unsigned					NOT NULL DEFAULT '0',
				`thread_id`			int(10) unsigned					NOT NULL,
				PRIMARY KEY (`page_id`),
				UNIQUE KEY (`page_slug`),
				INDEX (`page_parent`),
				INDEX (`page_index`)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
		");

 		$this->_getDb()->query("INSERT IGNORE INTO `EWRcarta_pages` (`page_slug`, `page_name`, `page_date`, `page_content`)
			VALUES ('index', 'Wiki Index', '0', 'This is your temporary index page! You can change the title of this page, but you can not change it\'s slug, or it\'s parent node.')");

 		$this->_getDb()->query("
			CREATE TABLE IF NOT EXISTS `EWRcarta_templates` (
				`template_name`		varchar(100)						NOT NULL,
				`template_content`	mediumtext							NOT NULL,
				PRIMARY KEY (`template_name`)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
		");

		$this->_getDb()->query("INSERT IGNORE INTO `xf_content_type` (`content_type`, `addon_id`, `fields`) VALUES ('wiki', 'EWRcarta', '')");
		$this->_getDb()->query("INSERT IGNORE INTO `xf_content_type_field` (`content_type`, `field_name`, `field_value`) VALUES ('wiki', 'attachment_handler_class', 'EWRcarta_AttachmentHandler_Wiki')");
		$this->_getDb()->query("INSERT IGNORE INTO `xf_content_type_field` (`content_type`, `field_name`, `field_value`) VALUES ('wiki', 'like_handler_class', 'EWRcarta_LikeHandler_Wiki')");
		$this->_getDb()->query("INSERT IGNORE INTO `xf_content_type_field` (`content_type`, `field_name`, `field_value`) VALUES ('wiki', 'news_feed_handler_class', 'EWRcarta_NewsFeedHandler_Wiki')");
		$this->_getDb()->query("INSERT IGNORE INTO `xf_content_type_field` (`content_type`, `field_name`, `field_value`) VALUES ('wiki', 'search_handler_class', 'EWRcarta_SearchHandler_Wiki')");
		XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();
	}

	protected function _install_37()
	{
		$this->addColumnIfNotExist("EWRcarta_history", "history_revert", "int(1) unsigned NOT NULL DEFAULT '0'");
	}

	protected function _install_38()
	{
		$this->addColumnIfNotExist("EWRcarta_pages", "thread_id", "int(10) unsigned NOT NULL DEFAULT '0'");
		
 		$this->_getDb()->query("
			CREATE TABLE IF NOT EXISTS `EWRcarta_read` (
				`page_read_id` 	int(10) unsigned 	NOT NULL AUTO_INCREMENT,
				`user_id` 			int(10) unsigned 	NOT NULL,
				`page_id` 			int(10) unsigned 	NOT NULL,
				`page_read_date` 	int(10) unsigned 	NOT NULL,
				PRIMARY KEY (`page_read_id`),
				UNIQUE KEY `user_id_page_id` (`user_id`,`page_id`),
				KEY `page_id` (`page_id`),
				KEY `page_read_date` (`page_read_date`)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
		");

 		$this->_getDb()->query("
			CREATE TABLE IF NOT EXISTS `EWRcarta_watch` (
				`user_id`			int(10) unsigned	NOT NULL,
				`page_id`			int(10) unsigned	NOT NULL,
				`email_subscribe`	int(3) unsigned		NOT NULL,
				PRIMARY KEY (`user_id`,`page_id`),
				KEY `page_id_email_subscribe` (`page_id`,`email_subscribe`)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
		");
		
		$this->_getDb()->query("INSERT IGNORE INTO `xf_content_type_field` (`content_type`, `field_name`, `field_value`) VALUES ('wiki', 'alert_handler_class', 'EWRcarta_AlertHandler_Wiki')");
		XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();
	}

	protected function _install_39()
	{
		$this->addColumnIfNotExist("EWRcarta_pages", "page_groups", "varchar(255) NOT NULL");
		$this->addColumnIfNotExist("EWRcarta_pages", "page_users", "varchar(255) NOT NULL");
		$this->addColumnIfNotExist("EWRcarta_pages", "page_admins", "varchar(255) NOT NULL");
	}

	public function addColumnIfNotExist($table, $field, $attr)
	{
		if ($this->_getDb()->fetchRow('SHOW columns FROM `'.$table.'` WHERE Field = ?', $field))
		{
			return false;
		}
		
		return $this->_getDb()->query("ALTER TABLE `".$table."` ADD `".$field."` ".$attr);
	}

	public static function uninstallCode()
	{
		$uninstall = self::getInstance();
		$uninstall->_uninstall_0();
	}

	protected function _uninstall_0()
	{
 		$this->_getDb()->query("
			DROP TABLE IF EXISTS
				`EWRcarta_cache`,
				`EWRcarta_history`,
				`EWRcarta_pages`,
				`EWRcarta_templates`;
		");

		$this->_getDb()->query("DELETE IGNORE FROM `xf_content_type` WHERE content_type = 'wiki'");
		$this->_getDb()->query("DELETE IGNORE FROM `xf_content_type_field` WHERE content_type = 'wiki'");
		XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();
	}
}