<?php

class EWRcarta_Model_Cache extends XenForo_Model
{
	public function getCacheByID($cacheID)
	{
		$cache = $this->_getDb()->fetchRow("
			SELECT *
				FROM EWRcarta_cache
			WHERE page_id = ?
		", $cacheID);

		return $cache;
	}

	public function getCache($page)
	{
		$cache = $this->_getDb()->fetchRow("
			SELECT *
				FROM EWRcarta_cache
			WHERE page_id = ?
		", $page['page_id']);

		return $cache;
	}

	public function emptyCache()
	{
		$this->_getDb()->query("
			TRUNCATE EWRcarta_cache
		");

		return true;
	}
}