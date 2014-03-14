<?php

class EWRcarta_Model_History extends XenForo_Model
{
	public function getHistoryByID($historyID)
	{
		if (!$history = $this->_getDb()->fetchRow("
			SELECT *
				FROM EWRcarta_history
			WHERE history_id = ?
		", $historyID))
		{
			return false;
		}

		return $history;
	}

	public function getHistory($history)
	{
		$history = $this->_getDb()->fetchRow("
			SELECT EWRcarta_history.*, EWRcarta_pages.*, xf_user.*,
				IF(NOT ISNULL(xf_user.user_id), xf_user.username, EWRcarta_history.username) AS username
				FROM EWRcarta_history
				LEFT JOIN xf_user ON (xf_user.user_id = EWRcarta_history.user_id)
				LEFT JOIN EWRcarta_pages ON (EWRcarta_pages.page_id = EWRcarta_history.page_id)
			WHERE history_id = ?
		", $history['history_id']);

		return $history;
	}
	
	public function getHistoryByPage($page)
	{
		$history = $this->_getDb()->fetchRow("
			SELECT EWRcarta_history.*, EWRcarta_pages.*, xf_user.*,
				IF(NOT ISNULL(xf_user.user_id), xf_user.username, EWRcarta_history.username) AS username
				FROM EWRcarta_history
				LEFT JOIN EWRcarta_pages ON (EWRcarta_pages.page_id = EWRcarta_history.page_id)
				LEFT JOIN xf_user ON (xf_user.user_id = EWRcarta_history.user_id)
			WHERE EWRcarta_history.page_id = ?
			ORDER BY history_date DESC
		", $page['page_id']);

		return $history;
	}

	public function getHistoryList($start, $stop, $page = 0)
	{
		$onlyPage = $page ? 'WHERE EWRcarta_history.page_id = '.$page['page_id'] : '';

		$start = ($start - 1) * $stop;

        $history = $this->_getDb()->fetchAll("
			SELECT EWRcarta_history.*, EWRcarta_pages.*, xf_user.*, xf_ip.ip,
				IF(NOT ISNULL(xf_user.user_id), xf_user.username, EWRcarta_history.username) AS username
				FROM EWRcarta_history
				LEFT JOIN EWRcarta_pages ON (EWRcarta_pages.page_id = EWRcarta_history.page_id)
				LEFT JOIN xf_user ON (xf_user.user_id = EWRcarta_history.user_id)
				LEFT JOIN xf_ip ON (xf_ip.ip_id = EWRcarta_history.history_ip)
			$onlyPage
			ORDER BY history_date DESC
			LIMIT ?, ?
		", array($start, $stop));

		foreach ($history AS &$edit)
		{
			$edit['history_ip'] = long2ip($edit['ip']);
			$edit['size'] = number_format(strlen($edit['history_content']));
		}

		return $history;
	}

	public function getHistoryCount($page = 0)
	{
		$onlyPage = $page ? 'WHERE page_id = '.$page['page_id'] : '';

        $count = $this->_getDb()->fetchRow("
			SELECT COUNT(*) AS total
				FROM EWRcarta_history
			$onlyPage
		");

		return $count['total'];
	}
	
	public function getEditorsList($page = 0)
	{
		$onlyPage = $page ? 'WHERE EWRcarta_history.page_id = '.$page['page_id'] : '';

        $editors = $this->_getDb()->fetchAll("
			SELECT xf_user.*, COUNT(EWRcarta_history.user_id) as count, MAX(EWRcarta_history.history_date) as date,
				IF(NOT ISNULL(xf_user.user_id), xf_user.username, EWRcarta_history.username) AS username
				FROM EWRcarta_history
				LEFT JOIN xf_user ON (xf_user.user_id = EWRcarta_history.user_id)
			$onlyPage
			GROUP BY EWRcarta_history.user_id
			ORDER BY count DESC, date DESC
		");
		
		return $editors;
	}

	public function updateHistory($page)
	{
		$this->_getDb()->query("
			UPDATE EWRcarta_history
			SET history_current = '0'
			WHERE page_id = ?
		", $page['page_id']);

		$dw = XenForo_DataWriter::create('EWRcarta_DataWriter_History');
		$dw->bulkSet(array(
			'page_id'    => $page['page_id'],
			'history_date' => $page['page_date'],
			'history_type' => $page['page_type'],
			'history_content' => $page['page_content'],
			'history_current' => '1',
			'history_revert' => '0',
		));
		$dw->save();
		$userID = $dw->get('user_id');
		$histID = $dw->get('history_id');
		$histIP = $this->getModelFromCache('XenForo_Model_Ip')->logIp($userID, 'wiki', $histID, 'update');

		$dw = XenForo_DataWriter::create('EWRcarta_DataWriter_History');
		$dw->setExistingData(array('history_id' => $histID));
		$dw->set('history_ip', $histIP);
		$dw->save();

		return true;
	}
	
	public function markReverts($history)
	{
		$this->_getDb()->query("
			UPDATE EWRcarta_history
			SET history_revert = '1'
			WHERE page_id = ?
				AND history_date > ?
				AND history_current = 0
		", array($history['page_id'], $history['history_date']));
		
		return true;
	}

	public function deleteHistory($history)
	{
		$this->_getDb()->query("DELETE FROM EWRcarta_history WHERE history_id = ? AND history_current = 0", $history['history_id']);

		return true;
	}

	public function emptyHistory()
	{
		$oldDate = XenForo_Application::$time - 2419200;
	
		$this->_getDb()->query("
			DELETE FROM EWRcarta_history
			WHERE history_current = 0
				AND history_date < ?
		", $oldDate);

		return true;
	}
}