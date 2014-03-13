<?php 
class XenPlaza_XPThreadEditingReason_Install
{
	public static function addColumn($table, $field, $attr)
	{
		if (!self::checkIfExist($table, $field)) {
			$db = XenForo_Application::get('db');
			return $db->query("ALTER TABLE `" . $table . "` ADD `" . $field . "` " . $attr);
		}
	}

	public static function checkIfExist($table, $field)
	{
		$db = XenForo_Application::get('db');
		if ($db->fetchRow('SHOW columns FROM `' . $table . '` WHERE Field = ?', $field)) {
			return true;
		}
		else {
			return false;
		}
	}
	public static function install(){
		if (!self::checkIfExist('xf_post', 'XP_edit_reason')) {
			self::addColumn('xf_post', 'XP_edit_reason', "  VARCHAR( 100 ) NOT NULL DEFAULT  ''  ");
		}
		if (!self::checkIfExist('xf_post', 'XP_edit_date')) {
			self::addColumn('xf_post', 'XP_edit_date', "  INT( 10 ) UNSIGNED NOT NULL DEFAULT  '0' ");
		}
		if (!self::checkIfExist('xf_post', 'XP_editor')) {
			self::addColumn('xf_post', 'XP_editor', "  INT( 10 ) UNSIGNED NOT NULL DEFAULT  '0' ");
		}
		return true;
	}
	public static function uninstall(){
		return true;
	}
}

?>