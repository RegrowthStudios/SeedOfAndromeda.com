<?php

class EWRporta_Model_Caches extends XenForo_Model
{
	public function getCaches()
	{
		return $this->fetchAllKeyed("SELECT * FROM EWRporta_caches", 'block_id');
	}

	public function getCacheByBlockId($blockId)
	{
		if (!$cache = $this->_getDb()->fetchRow("
			SELECT *
				FROM EWRporta_caches
			WHERE block_id = ?
		", $blockId))
		{
			return false;
		}

		return $cache;
	}

	public function deleteCacheByBlock($blockId)
	{
		$db = $this->_getDb();
		$db->delete('EWRporta_caches', 'block_id = ' . $db->quote($blockId));

		return;
	}

	public function buildBlockCache($block, $results)
	{
		$dw = XenForo_DataWriter::create('EWRporta_DataWriter_Caches', XenForo_DataWriter::ERROR_SILENT);
		if ($cache = $this->getCacheByBlockId($block['block_id']))
		{
			$dw->setExistingData($cache);
		}
		else
		{
			$dw->set('block_id', $block['block_id']);
		}
		$dw->set('results', $results);
		$dw->save();

		return true;
	}

	public function emptyBlockCache($block)
	{
		$this->_getDb()->query("
			UPDATE EWRporta_caches
			SET date = 0
			WHERE block_id = ?
		", $block['block_id']);
		
		/*
		if ($cache = $this->getCacheByBlockId($block['block_id']))
		{
			$dw = XenForo_DataWriter::create('EWRporta_DataWriter_Caches');
			$dw->setExistingData($cache);
			$dw->delete();
		}
		*/
		
		return true;
	}
}