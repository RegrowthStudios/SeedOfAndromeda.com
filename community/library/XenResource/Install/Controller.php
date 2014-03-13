<?php

class XenResource_Install_Controller
{
	public static function install($previous)
	{
		$db = XenForo_Application::getDb();

		if (XenForo_Application::$versionId < 1020070)
		{
			// note: this can't be phrased
			throw new XenForo_Exception('This add-on requires XenForo 1.2.0 or higher.', true);
		}

		$tables = self::getTables();
		$data = self::getData();

		if (!$previous)
		{
			foreach ($tables AS $tableSql)
			{
				try
				{
					$db->query($tableSql);
				}
				catch (Zend_Db_Exception $e) {}
			}

			foreach (self::getAlters() AS $alterSql)
			{
				try
				{
					$db->query($alterSql);
				}
				catch (Zend_Db_Exception $e) {}
			}

			foreach (self::getData() AS $dataSql)
			{
				$db->query($dataSql);
			}
		}
		else
		{
			// upgrades
			if ($previous['version_id'] < 1010031)
			{
				// upgrading from 1.0
				try
				{
					$db->query($tables['xf_resource_feature']);
				}
				catch (Zend_Db_Exception $e) {}

				try
				{
					$db->query($tables['xf_resource_field']);
				}
				catch (Zend_Db_Exception $e) {}

				try
				{
					$db->query($tables['xf_resource_field_category']);
				}
				catch (Zend_Db_Exception $e) {}

				try
				{
					$db->query($tables['xf_resource_field_value']);
				}
				catch (Zend_Db_Exception $e) {}

				try
				{
					$db->query($tables['xf_resource_category_prefix']);
				}
				catch (Zend_Db_Exception $e) {}

				try
				{
					$db->query($tables['xf_resource_category_watch']);
				}
				catch (Zend_Db_Exception $e) {}

				try
				{
					$db->query($tables['xf_resource_prefix']);
				}
				catch (Zend_Db_Exception $e) {}

				try
				{
					$db->query($tables['xf_resource_prefix_group']);
				}
				catch (Zend_Db_Exception $e) {}

				try
				{
					$userGroupIds = $db->fetchCol("
						SELECT user_group_id
						FROM xf_user_group
					");
					$categoryUpdates = $db->fetchPairs("
						SELECT resource_category_id, allow_submit_user_group_ids
						FROM xf_resource_category
						WHERE allow_submit_user_group_ids <> '-1'
					");
					foreach ($categoryUpdates AS $categoryId => $groups)
					{
						$allowGroupIds = explode(',', $groups);
						foreach ($userGroupIds AS $userGroupId)
						{
							$db->query("
								INSERT IGNORE INTO xf_permission_entry_content
									(content_type, content_id, user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
								VALUES
									('resource_category', ?, ?, 0, 'resource', 'add', ?, 0)
							", array(
								$categoryId, $userGroupId, in_array($userGroupId, $allowGroupIds) ? 'content_allow' : 'reset'
							));
						}
					}
				}
				catch (Zend_Db_Exception $e) {}

				try
				{
					$db->query("
						ALTER TABLE xf_resource
							ADD custom_resource_fields MEDIUMBLOB NOT NULL,
							ADD prefix_id INT UNSIGNED NOT NULL DEFAULT 0,
							ADD icon_date INT UNSIGNED NOT NULL DEFAULT 0,
							ADD KEY prefix_id (prefix_id)
					");
				}
				catch (Zend_Db_Exception $e) {}

				try
				{
					$db->query("
						ALTER TABLE xf_resource_update
							ADD warning_id INT UNSIGNED NOT NULL DEFAULT 0,
							ADD warning_message VARCHAR(255) NOT NULL DEFAULT ''
					");
				}
				catch (Zend_Db_Exception $e) {}

				try
				{
					$db->query("
						ALTER TABLE xf_resource_rating
							ADD warning_id INT UNSIGNED NOT NULL DEFAULT 0,
							ADD is_anonymous TINYINT UNSIGNED NOT NULL DEFAULT 0
					");
				}
				catch (Zend_Db_Exception $e) {}

				try
				{
					$db->query("
						ALTER TABLE xf_resource_category
							ADD field_cache MEDIUMBLOB NOT NULL,
							DROP allow_submit_user_group_ids,
							ADD prefix_cache MEDIUMBLOB NOT NULL COMMENT 'Serialized data from xf_resource_category_prefix, [group_id][prefix_id] => prefix_id',
							ADD require_prefix TINYINT UNSIGNED NOT NULL DEFAULT '0',
							ADD featured_count SMALLINT UNSIGNED NOT NULL DEFAULT '0'
					");
				}
				catch (Zend_Db_Exception $e) {}
			}

			// this is a bug in the schema only if you installed 1.1.0
			if ($previous['version_id'] == 1010031)
			{
				try
				{
					$db->query("
						ALTER TABLE xf_resource_field
							CHANGE display_group display_group VARCHAR(25) NOT NULL DEFAULT 'above_info'
					");
				}
				catch (Zend_Db_Exception $e) {}
			}

			// always sync these
			try
			{
				$db->query($data['xf_content_type']);
			}
			catch (Zend_Db_Exception $e) {}

			try
			{
				$db->query($data['xf_content_type_field']);
			}
			catch (Zend_Db_Exception $e) {}
		}

		self::applyPermissionDefaults($previous ? $previous['version_id'] : false);

		// this will be rebuilt later, but workaround a < 1.2.3 bug where the current cache isn't updated
		XenForo_Application::set('contentTypes',
			XenForo_Model::create('XenForo_Model_ContentType')->getContentTypesForCache()
		);
	}

	public static function uninstall()
	{
		$db = XenForo_Application::get('db');

		foreach (self::getTables() AS $tableName => $tableSql)
		{
			try
			{
				$db->query("DROP TABLE IF EXISTS `$tableName`");
			}
			catch (Zend_Db_Exception $e) {}
		}

		try
		{
			$db->query("ALTER TABLE xf_user DROP resource_count");
		}
		catch (Zend_Db_Exception $e) {}

		$contentTypes = array('resource', 'resource_category', 'resource_update', 'resource_version', 'resource_rating');
		$contentTypesQuoted = $db->quote($contentTypes);

		XenForo_Db::beginTransaction($db);

		$contentTypeTables = array(
			'xf_attachment',
			'xf_content_type',
			'xf_content_type_field',
			'xf_deletion_log',
			'xf_liked_content',
			'xf_moderation_queue',
			'xf_moderator_log',
			'xf_news_feed',
			'xf_report',
			'xf_user_alert'
		);
		foreach ($contentTypeTables AS $table)
		{
			$db->delete($table, 'content_type IN (' . $contentTypesQuoted . ')');
		}

		$db->delete('xf_admin_permission_entry', "admin_permission_id = 'resourceManager'");
		$db->delete('xf_permission_cache_content', "content_type = 'resource_category'");
		$db->delete('xf_permission_entry', "permission_group_id = 'resource'");
		$db->delete('xf_permission_entry_content', "permission_group_id = 'resource'");

		XenForo_Db::commit($db);

		// this will be rebuilt later, but workaround a < 1.2.3 bug where the current cache isn't updated
		XenForo_Application::set('contentTypes',
			XenForo_Model::create('XenForo_Model_ContentType')->getContentTypesForCache()
		);

		XenForo_Application::setSimpleCacheData('resourcePrefixes', false);
	}

	public static function getTables()
	{
		$tables = array();

		$tables['xf_resource'] = "
			CREATE TABLE IF NOT EXISTS `xf_resource` (
			  `resource_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `title` varchar(100) NOT NULL DEFAULT '',
			  `tag_line` varchar(100) NOT NULL DEFAULT '',
			  `user_id` int(10) unsigned NOT NULL,
			  `username` varchar(100) NOT NULL DEFAULT '',
			  `resource_state` ENUM(  'visible',  'moderated',  'deleted' ) NOT NULL DEFAULT  'visible',
			  `resource_date` int(10) unsigned NOT NULL,
			  `resource_category_id` int(11) NOT NULL,
			  `current_version_id` int(10) unsigned NOT NULL,
			  `description_update_id` int(10) unsigned NOT NULL COMMENT 'Points to the resource update that acts as the description for this resource',
			  `discussion_thread_id` int(10) unsigned NOT NULL COMMENT 'Points to an automatically-created thread for this resource',
			  `external_url` varchar(500) NOT NULL DEFAULT '',
			  is_fileless tinyint unsigned not null default 0,
			  external_purchase_url varchar(500) not null default '',
			  price decimal(10,2) unsigned not null default 0,
			  `currency` VARCHAR( 3 ) NOT NULL DEFAULT '',
			  `download_count` int(10) unsigned NOT NULL DEFAULT '0',
			  `rating_count` int(10) unsigned NOT NULL DEFAULT '0',
			  `rating_sum` int(10) unsigned NOT NULL DEFAULT '0',
			  `rating_avg` float unsigned NOT NULL DEFAULT '0',
			  `rating_weighted` float unsigned NOT NULL DEFAULT '0',
			  update_count int unsigned not null default 0,
			  review_count int unsigned not null default 0,
			  `last_update` int(10) unsigned NOT NULL,
			  alt_support_url varchar(500) not null default '',
			  had_first_visible tinyint unsigned not null default 0,
			  custom_resource_fields MEDIUMBLOB NOT NULL,
			  prefix_id INT UNSIGNED NOT NULL DEFAULT 0,
			  icon_date INT UNSIGNED NOT NULL DEFAULT 0,
			  PRIMARY KEY (`resource_id`),
			  KEY `category_last_update` (`resource_category_id`, last_update),
			  KEY `category_rating_weighted` (`resource_category_id`, rating_weighted),
			  KEY `last_update` (`last_update`),
			  KEY `rating_weighted` (`rating_weighted`),
			  KEY `user_id_last_update` (`user_id`, last_update),
			  KEY discussion_thread_id (discussion_thread_id),
			  KEY prefix_id (prefix_id)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8
		";

		$tables['xf_resource_update'] = "
			CREATE TABLE IF NOT EXISTS `xf_resource_update` (
			  `resource_update_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `resource_id` int(11) NOT NULL,
			  `title` varchar(100) NOT NULL DEFAULT '' COMMENT 'Title field is optional, and is not used in the first post.',
			  `message` mediumtext NOT NULL COMMENT 'Supports BB code',
			  `message_state` enum('visible', 'moderated', 'deleted') NOT NULL DEFAULT 'visible',
			  `post_date` int(10) unsigned NOT NULL,
			  `attach_count` int(10) unsigned NOT NULL DEFAULT '0',
			  `likes` int(10) unsigned NOT NULL DEFAULT '0',
			  `like_users` blob NOT NULL,
			  ip_id int unsigned not null default 0,
			  had_first_visible tinyint unsigned not null default 0,
			  warning_id INT UNSIGNED NOT NULL DEFAULT 0,
			  warning_message VARCHAR(255) NOT NULL DEFAULT '',
			  PRIMARY KEY (`resource_update_id`),
			  KEY `resource_id_post_date` (`resource_id`, post_date)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8
		";

		$tables['xf_resource_category'] = "
			CREATE TABLE IF NOT EXISTS `xf_resource_category` (
				`resource_category_id` int(10) unsigned NOT NULL auto_increment,
				`category_title` varchar(100) NOT NULL,
				category_description text not null,
				`parent_category_id` int(10) unsigned NOT NULL default '0',
				`depth` smallint(5) unsigned NOT NULL default '0',
				`lft` int(10) unsigned NOT NULL default '0',
				`rgt` int(10) unsigned NOT NULL default '0',
				`display_order` int(10) unsigned NOT NULL default '0',
				`resource_count` int(10) unsigned NOT NULL default '0',
				`last_update` int(10) unsigned NOT NULL default '0',
				`last_resource_title` varchar(100) NOT NULL default '',
				`last_resource_id` int(10) unsigned NOT NULL default '0',
				`category_breadcrumb` blob NOT NULL,
				allow_local tinyint unsigned not null default 0,
				allow_external tinyint unsigned not null default 0,
				allow_commercial_external tinyint unsigned not null default 0,
				allow_fileless tinyint unsigned not null default 0,
				thread_node_id int unsigned not null default 0,
				thread_prefix_id int unsigned not null default 0,
				always_moderate_create tinyint unsigned not null default 0,
				always_moderate_update tinyint unsigned not null default 0,
				field_cache MEDIUMBLOB NOT NULL,
				prefix_cache MEDIUMBLOB NOT NULL COMMENT 'Serialized data from xf_resource_category_prefix, [group_id][prefix_id] => prefix_id',
				require_prefix TINYINT UNSIGNED NOT NULL DEFAULT '0',
				featured_count SMALLINT UNSIGNED NOT NULL DEFAULT '0',
				PRIMARY KEY  (`resource_category_id`),
				KEY `parent_category_id_lft` (`parent_category_id`,`lft`),
				KEY `lft_rgt` (`lft`,`rgt`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8
		";

		$tables['xf_resource_category_prefix'] = "
			CREATE TABLE IF NOT EXISTS xf_resource_category_prefix (
				resource_category_id INT UNSIGNED NOT NULL,
				prefix_id INT UNSIGNED NOT NULL,
				PRIMARY KEY (resource_category_id, prefix_id),
				KEY prefix_id (prefix_id)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
		";

		$tables['xf_resource_category_watch'] = "
			CREATE TABLE IF NOT EXISTS xf_resource_category_watch (
				`user_id` int(10) unsigned NOT NULL,
				`resource_category_id` int(10) unsigned NOT NULL,
				`notify_on` enum('','resource','update') NOT NULL,
				`send_alert` tinyint(3) unsigned NOT NULL,
				`send_email` tinyint(3) unsigned NOT NULL,
				`include_children` tinyint(3) unsigned NOT NULL,
				PRIMARY KEY (`user_id`,`resource_category_id`),
				KEY `node_id_notify_on` (`resource_category_id`,`notify_on`)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
		";

		$tables['xf_resource_download'] = "
			CREATE TABLE IF NOT EXISTS `xf_resource_download` (
			  `resource_download_id` int(10) unsigned NOT NULL auto_increment,
			  `resource_version_id` int(10) unsigned NOT NULL,
			  `user_id` int(10) unsigned NOT NULL,
			  `resource_id` int(10) unsigned NOT NULL,
			  `last_download_date` int(10) unsigned NOT NULL,
			  PRIMARY KEY  (`resource_download_id`),
			  UNIQUE KEY `version_user` (`resource_version_id`,`user_id`),
			  KEY `user_resource` (`user_id`,`resource_id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8
		";

		$tables['xf_resource_feature'] = "
			CREATE TABLE IF NOT EXISTS `xf_resource_feature` (
			  `resource_id` int(10) unsigned NOT NULL,
			  `feature_date` int(10) unsigned NOT NULL,
			  PRIMARY KEY  (`resource_id`),
			  KEY `feature_date` (`feature_date`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8
		";

		$tables['xf_resource_field'] = "
			CREATE TABLE IF NOT EXISTS `xf_resource_field` (
			  	field_id VARBINARY(25) NOT NULL,
				display_group VARCHAR(25) NOT NULL DEFAULT 'above_info',
				display_order INT UNSIGNED NOT NULL DEFAULT 1,
				field_type VARCHAR(25) NOT NULL DEFAULT 'textbox',
				field_choices BLOB NOT NULL,
				match_type VARCHAR(25) NOT NULL DEFAULT 'none',
				match_regex VARCHAR(250) NOT NULL DEFAULT '',
				match_callback_class VARCHAR(75) NOT NULL DEFAULT '',
				match_callback_method VARCHAR(75) NOT NULL DEFAULT '',
				max_length INT UNSIGNED NOT NULL DEFAULT 0,
				required TINYINT UNSIGNED NOT NULL DEFAULT 0,
				display_template TEXT NOT NULL,
				PRIMARY KEY (field_id),
				KEY display_group_order (display_group, display_order)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8
		";

		$tables['xf_resource_field_category'] = "
			CREATE TABLE IF NOT EXISTS xf_resource_field_category (
				field_id VARBINARY(25) NOT NULL,
				resource_category_id INT NOT NULL,
				PRIMARY KEY (field_id, resource_category_id),
				KEY resource_category_id (resource_category_id)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
		";

		$tables['xf_resource_field_value'] = "
			CREATE TABLE IF NOT EXISTS xf_resource_field_value (
				resource_id INT UNSIGNED NOT NULL,
				field_id VARBINARY(25) NOT NULL,
				field_value MEDIUMTEXT NOT NULL,
				PRIMARY KEY (resource_id, field_id),
				KEY field_id (field_id)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
		";

		$tables['xf_resource_prefix'] = "
			CREATE TABLE IF NOT EXISTS xf_resource_prefix (
				prefix_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
				prefix_group_id INT UNSIGNED NOT NULL,
				display_order INT UNSIGNED NOT NULL,
				materialized_order INT UNSIGNED NOT NULL COMMENT 'Internally-set order, based on prefix_group.display_order, prefix.display_order',
				css_class VARCHAR(50) NOT NULL DEFAULT '',
				allowed_user_group_ids blob NOT NULL,
				PRIMARY KEY (prefix_id),
				KEY materialized_order (materialized_order)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
		";

		$tables['xf_resource_prefix_group'] = "
			CREATE TABLE IF NOT EXISTS xf_resource_prefix_group (
				prefix_group_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
				display_order INT UNSIGNED NOT NULL,
				PRIMARY KEY (prefix_group_id)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
		";

		$tables['xf_resource_rating'] = "
			CREATE TABLE IF NOT EXISTS `xf_resource_rating` (
			  `resource_rating_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `resource_version_id` int(10) unsigned NOT NULL,
			  `user_id` int(10) unsigned NOT NULL,
			  `rating` tinyint(3) unsigned NOT NULL,
			  `rating_date` int(10) unsigned NOT NULL,
			  `message` mediumtext NOT NULL,
			  `resource_id` int(10) unsigned NOT NULL,
			  `version_string` varchar(50) NOT NULL,
			  `author_response` mediumtext NOT NULL,
			  `is_review` tinyint(3) unsigned NOT NULL DEFAULT '0',
			  `count_rating` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT 'Whether this counts towards the global resource rating.',
			  `rating_state` ENUM(  'visible',  'deleted' ) NOT NULL DEFAULT  'visible',
			  warning_id INT UNSIGNED NOT NULL DEFAULT 0,
			  is_anonymous TINYINT UNSIGNED NOT NULL DEFAULT 0,
			  PRIMARY KEY (`resource_rating_id`),
			  UNIQUE KEY `version_user_id` (`resource_version_id`,`user_id`),
			  KEY `user_id` (`user_id`),
			  KEY `count_rating_resource_id` (`count_rating`,`resource_id`),
			  KEY `resource_id_rating_date` (`resource_id`,`rating_date`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8
		";

		$tables['xf_resource_version'] = "
			CREATE TABLE IF NOT EXISTS `xf_resource_version` (
			  `resource_version_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `resource_id` int(10) unsigned NOT NULL,
			  `resource_update_id` int(10) unsigned NOT NULL,
			  `version_string` varchar(50) NOT NULL,
			  `release_date` int(10) unsigned NOT NULL,
			  download_url varchar(250) NOT NULL DEFAULT '',
			  `download_count` int(10) unsigned NOT NULL DEFAULT '0',
			  `rating_count` int(10) unsigned NOT NULL DEFAULT '0',
			  `rating_sum` int(10) unsigned NOT NULL DEFAULT '0',
			  `version_state` ENUM(  'visible',  'moderated',  'deleted' ) NOT NULL DEFAULT  'visible',
			  had_first_visible tinyint unsigned not null default 0,
			  PRIMARY KEY (`resource_version_id`),
			  KEY `resource_id_release_date` (`resource_id`, release_date)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8
		";

		$tables['xf_resource_watch'] = "
			CREATE TABLE IF NOT EXISTS `xf_resource_watch` (
				`user_id` int(10) unsigned NOT NULL,
				`resource_id` int(10) unsigned NOT NULL,
				`email_subscribe` tinyint(3) unsigned NOT NULL default '0',
				watch_key varchar(16) not null default '',
				PRIMARY KEY  (`user_id`,`resource_id`),
				KEY `resource_id_email_subscribe` (`resource_id`,`email_subscribe`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8
		";

		return $tables;
	}

	public static function getAlters()
	{
		$alters = array();

		$alters['user'] = "
			ALTER TABLE xf_user ADD resource_count INT UNSIGNED NOT NULL DEFAULT 0,
				ADD INDEX resource_count (resource_count)
		";

		return $alters;
	}

	public static function getData()
	{
		$data = array();

		$data['xf_content_type'] = "
			INSERT IGNORE INTO xf_content_type
				(content_type, addon_id, fields)
			VALUES
				('resource', 'XenResource', ''),
				('resource_category', 'XenResource', ''),
				('resource_update', 'XenResource', ''),
				('resource_version', 'XenResource', ''),
				('resource_rating', 'XenResource', '')
		";

		$data['xf_content_type_field'] = "
			INSERT IGNORE INTO xf_content_type_field
				(content_type, field_name, field_value)
			VALUES
				('resource',         'moderation_queue_handler_class', 'XenResource_ModerationQueueHandler_Resource'),
				('resource',         'moderator_log_handler_class',    'XenResource_ModeratorLogHandler_Resource'),
				('resource',         'spam_handler_class',             'XenResource_SpamHandler_Resource'),
				('resource',         'stats_handler_class',            'XenResource_StatsHandler_Resource'),

				('resource_category', 'permission_handler_class',      'XenResource_ContentPermission_Category'),

				('resource_update',  'alert_handler_class',            'XenResource_AlertHandler_Update'),
				('resource_update',  'attachment_handler_class',       'XenResource_AttachmentHandler_Update'),
				('resource_update',  'like_handler_class',             'XenResource_LikeHandler_Update'),
				('resource_update',  'news_feed_handler_class',        'XenResource_NewsFeedHandler_Update'),
				('resource_update',  'search_handler_class',           'XenResource_Search_DataHandler_Update'),
				('resource_update',  'report_handler_class',           'XenResource_ReportHandler_Update'),
				('resource_update',  'moderation_queue_handler_class', 'XenResource_ModerationQueueHandler_Update'),
				('resource_update',  'moderator_log_handler_class',    'XenResource_ModeratorLogHandler_Update'),
				('resource_update',  'warning_handler_class',          'XenResource_WarningHandler_Update'),

				('resource_version', 'attachment_handler_class',       'XenResource_AttachmentHandler_Version'),
				('resource_version', 'moderation_queue_handler_class', 'XenResource_ModerationQueueHandler_Version'),
				('resource_version', 'moderator_log_handler_class',    'XenResource_ModeratorLogHandler_Version'),

				('resource_rating',  'report_handler_class',           'XenResource_ReportHandler_Rating'),
				('resource_rating',  'alert_handler_class',            'XenResource_AlertHandler_Rating'),
				('resource_rating',  'spam_handler_class',             'XenResource_SpamHandler_Rating'),
				('resource_rating',  'warning_handler_class',          'XenResource_WarningHandler_Rating'),
				('resource_rating',  'moderator_log_handler_class',    'XenResource_ModeratorLogHandler_Rating')
		";

		$data['xf_resource_category'] = "
			INSERT INTO `xf_resource_category`
				(`resource_category_id`, `category_title`, `category_description`, `parent_category_id`, `depth`, `lft`, `rgt`, `display_order`, `resource_count`, `last_update`, `last_resource_title`, `last_resource_id`, `category_breadcrumb`, `allow_local`, `allow_external`, `allow_commercial_external`, `allow_fileless`, `thread_node_id`, `thread_prefix_id`, `always_moderate_create`, `always_moderate_update`, field_cache, prefix_cache)
			VALUES
				(1, 'Example Category', 'This is an example resource manager category. You can manage the resource manager categories via the <a href=\"admin.php?resource-categories/\">Admin Control Panel</a>. From there, you can setup more categories or change the resource manager options.', 0, 0, 1, 2, 1, 0, 0, '0', 0, 0x613a303a7b7d, 1, 1, 1, 1, 0, 0, 0, 0, '', '');
		";

		$data['xf_admin_permission_entry'] = "
			INSERT IGNORE INTO xf_admin_permission_entry
				(user_id, admin_permission_id)
			SELECT user_id, 'resourceManager'
			FROM xf_admin_permission_entry
			WHERE admin_permission_id = 'node'
		";

		$data['xf_phrase'] = "
			INSERT IGNORE INTO `xf_phrase`
				(`language_id`, `title`, `phrase_text`, `global_cache`, `addon_id`, `version_id`, `version_string`)
			VALUES
				(0, 'rating', 'Rating', 0, '', 0, ''),
				(0, 'rating_1', 'Terrible', 0, '', 0, ''),
				(0, 'rating_2', 'Poor', 0, '', 0, ''),
				(0, 'rating_3', 'Average', 0, '', 0, ''),
				(0, 'rating_4', 'Good', 0, '', 0, ''),
				(0, 'rating_5', 'Excellent', 0, '', 0, ''),
				(0, 'submit_rating', 'Submit Rating', 0, '', 0, ''),
				(0, 'your_rating_has_been_recorded', 'Your rating has been recorded.', 0, '', 0, '');
		";

		return $data;
	}

	public static function applyPermissionDefaults($previousVersion)
	{
		if (!$previousVersion)
		{
			self::applyGlobalPermission('resource', 'view', 'general', 'viewNode', false);
			self::applyGlobalPermission('resource', 'viewUpdateAttach', 'general', 'viewNode', false);
			self::applyGlobalPermission('resource', 'download', 'forum', 'viewAttach', false);
			self::applyGlobalPermission('resource', 'like', 'forum', 'like', false);
			self::applyGlobalPermission('resource', 'rate', 'forum', 'like', false);
			self::applyGlobalPermission('resource', 'add', 'forum', 'postThread', false);
			self::applyGlobalPermission('resource', 'uploadUpdateAttach', 'forum', 'postThread', false);
			self::applyGlobalPermission('resource', 'updateSelf', 'forum', 'editOwnPost', false);
			self::applyGlobalPermission('resource', 'reviewReply', 'forum', 'editOwnPost', false);
			self::applyGlobalPermission('resource', 'deleteSelf', 'forum', 'deleteOwnPost', false);
			self::applyGlobalPermission('resource', 'viewDeleted', 'forum', 'viewDeleted', true);
			self::applyGlobalPermission('resource', 'deleteAny', 'forum', 'deleteAnyPost', true);
			self::applyGlobalPermission('resource', 'undelete', 'forum', 'undelete', true);
			self::applyGlobalPermission('resource', 'hardDeleteAny', 'forum', 'hardDeleteAnyPost', true);
			self::applyGlobalPermission('resource', 'deleteReviewAny', 'forum', 'deleteAnyPost', true);
			self::applyGlobalPermission('resource', 'editAny', 'forum', 'editAnyPost', true);
			self::applyGlobalPermission('resource', 'reassign', 'forum', 'editAnyPost', true);
			self::applyGlobalPermission('resource', 'viewModerated', 'forum', 'viewModerated', true);
			self::applyGlobalPermission('resource', 'approveUnapprove', 'forum', 'approveUnapprove', true);
		}

		if (!$previousVersion || $previousVersion < 1010031)
		{
			self::applyGlobalPermission('resource', 'featureUnfeature', 'forum', 'stickUnstickThread', true);
			self::applyGlobalPermission('resource', 'warn', 'forum', 'warn', true);
		}
	}

	protected static $_globalModPermCache = null;

	protected static function _getGlobalModPermissions()
	{
		if (self::$_globalModPermCache === null)
		{
			$moderators = XenForo_Application::getDb()->fetchPairs('
				SELECT user_id, moderator_permissions
				FROM xf_moderator
			');
			foreach ($moderators AS &$permissions)
			{
				$permissions = unserialize($permissions);
			}

			self::$_globalModPermCache = $moderators;
		}

		return self::$_globalModPermCache;
	}

	protected static function _updateGlobalModPermissions($userId, array $permissions)
	{
		self::$_globalModPermCache[$userId] = $permissions;

		XenForo_Application::getDb()->query('
			UPDATE xf_moderator
			SET moderator_permissions = ?
			WHERE user_id = ?
		', array(serialize($permissions), $userId));
	}

	public static function applyGlobalPermission($applyGroupId, $applyPermissionId, $dependGroupId = null, $dependPermissionId = null, $checkModerator = true)
	{
		$db = XenForo_Application::getDb();

		XenForo_Db::beginTransaction($db);

		if ($dependGroupId && $dependPermissionId)
		{
			$db->query("
				INSERT IGNORE INTO xf_permission_entry
					(user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
				SELECT user_group_id, user_id, ?, ?, 'allow', 0
				FROM xf_permission_entry
				WHERE permission_group_id = ?
					AND permission_id = ?
					AND permission_value = 'allow'
			", array($applyGroupId, $applyPermissionId, $dependGroupId, $dependPermissionId));
		}
		else
		{
			$db->query("
				INSERT IGNORE INTO xf_permission_entry
					(user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
				SELECT DISTINCT user_group_id, user_id, ?, ?, 'allow', 0
				FROM xf_permission_entry
			", array($applyGroupId, $applyPermissionId));
		}

		if ($checkModerator)
		{
			$moderators = self::_getGlobalModPermissions();
			foreach ($moderators AS $userId => $permissions)
			{
				if (!$dependGroupId || !$dependPermissionId || !empty($permissions[$dependGroupId][$dependPermissionId]))
				{
					$permissions[$applyGroupId][$applyPermissionId] = '1'; // string 1 is stored by the code
					self::_updateGlobalModPermissions($userId, $permissions);
				}
			}
		}

		XenForo_Db::commit($db);
	}
}
