<?php

class EWRcarta_Model_PageWatch extends XenForo_Model
{
	public function getUserPageWatchByPageId($userID, $pageID)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
				FROM EWRcarta_watch
			WHERE user_id = ?
				AND page_id = ?
		', array($userID, $pageID));
	}
	
	public function getUserPageWatchByPageIds($userID, array $pageIDs)
	{
		if (!$pageIDs)
		{
			return array();
		}

		return $this->fetchAllKeyed('
			SELECT *
			FROM EWRcarta_watch
			WHERE user_id = ?
				AND page_id IN (' . $this->_getDb()->quote($pageIDs) . ')
		', 'page_id', $userID);
	}
	
	public function getUsersWatchingPage($pageID)
	{
		$autoReadDate = XenForo_Application::$time - (XenForo_Application::get('options')->readMarkingDataLifetime * 86400);
		
		return $this->fetchAllKeyed('
			SELECT EWRcarta_watch.*, EWRcarta_read.*, xf_user.*,
				GREATEST(COALESCE(EWRcarta_read.page_read_date, 0), ' . $autoReadDate . ') AS page_read_date
			FROM EWRcarta_watch
				INNER JOIN xf_user ON (xf_user.user_id = EWRcarta_watch.user_id AND xf_user.user_state = \'valid\' AND xf_user.is_banned = 0)
				LEFT JOIN EWRcarta_read ON (EWRcarta_read.page_id = EWRcarta_watch.page_id AND EWRcarta_read.user_id = xf_user.user_id)
			WHERE EWRcarta_watch.page_id = ?
		', 'user_id', array($pageID));
	}
	
	public function getPagesWatchedByUser($userID, $start, $stop, $newOnly)
	{
		if ($newOnly)
		{
			$cutoff = XenForo_Application::$time - (XenForo_Application::get('options')->readMarkingDataLifetime * 86400);
			$newOnlyClause = '
				AND EWRcarta_pages.last_comment_date > ' . $cutoff . '
				AND EWRcarta_pages.last_comment_date > COALESCE(EWRcarta_read.page_read_date, 0)
			';
		}
		else
		{
			$newOnlyClause = '';
		}
		
		$start = ($start - 1) * $stop;
		
		$pages = $this->_getDb()->fetchAll("
			SELECT EWRcarta_watch.*, EWRcarta_pages.*
			FROM EWRcarta_watch
				INNER JOIN EWRcarta_pages ON (EWRcarta_pages.page_id = EWRcarta_watch.page_id)
				LEFT JOIN EWRcarta_read ON (EWRcarta_watch.page_id = EWRcarta_read.page_id AND EWRcarta_read.user_id = ?)
			WHERE EWRcarta_watch.user_id = ?
				$newOnlyClause
			ORDER BY EWRcarta_pages.page_date DESC, EWRcarta_pages.page_id DESC
			LIMIT ?, ?
		", array($userID, $userID, $start, $stop));

		foreach ($pages AS &$page)
		{
			$page['lastCommentInfo'] = array(
				'post_date' => $page['last_comment_date'],
				'post_id' => $page['last_comment_id'],
				'user_id' => $page['last_comment_user_id'],
				'username' => $page['last_comment_username']
			);
		}

        return $pages;
	}
	
	public function countPagesWatchedByUser($userID)
	{
		$count = $this->_getDb()->fetchRow("
			SELECT COUNT(*) AS total
			FROM EWRcarta_watch
				INNER JOIN EWRcarta_pages ON (EWRcarta_pages.page_id = EWRcarta_watch.page_id)
			WHERE EWRcarta_watch.user_id = ?
		", array($userID));

		return $count['total'];
	}
	
	public function setPageWatchState($userID, $pageID, $state, $overWrite = true)
	{
		$pageWatch = $this->getUserPageWatchByPageId($userID, $pageID);
		
		if ($pageWatch && !$overWrite)
		{
			return true;
		}

		switch ($state)
		{
			case 'watch_email':
			case 'watch_no_email':
				$dw = XenForo_DataWriter::create('EWRcarta_DataWriter_PageWatch');
				if ($pageWatch)
				{
					$dw->setExistingData($pageWatch, true);
				}
				else
				{
					$dw->set('user_id', $userID);
					$dw->set('page_id', $pageID);
				}
				$dw->set('email_subscribe', ($state == 'watch_email' ? 1 : 0));
				$dw->save();
				return true;

			case '':
				if ($pageWatch)
				{
					$dw = XenForo_DataWriter::create('EWRcarta_DataWriter_PageWatch');
					$dw->setExistingData($pageWatch, true);
					$dw->delete();
				}
				return true;

			default:
				return false;
		}
	}
}