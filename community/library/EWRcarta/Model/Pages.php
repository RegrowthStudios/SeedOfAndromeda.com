<?php

class EWRcarta_Model_Pages extends XenForo_Model
{
	public function getPageBySlug($pageSlug)
	{
		if (!$page = $this->_getDb()->fetchRow("
			SELECT EWRcarta_pages.*, xf_liked_content.like_date, EWRcarta_watch.page_id AS page_is_watched
				FROM EWRcarta_pages
				LEFT JOIN EWRcarta_watch ON (EWRcarta_pages.page_id = EWRcarta_watch.page_id AND EWRcarta_watch.user_id = ?)
				LEFT JOIN xf_liked_content
					ON (xf_liked_content.content_type = 'wiki'
						AND xf_liked_content.content_id = EWRcarta_pages.page_id
						AND xf_liked_content.like_user_id = " .$this->_getDb()->quote(XenForo_Visitor::getUserId()) . ")
			WHERE page_slug = ?
		", array(XenForo_Visitor::getUserId(), $pageSlug)))
		{
			return false;
		}

		if ($page['likes'] = $page['page_likes'])
		{
			$page['likeUsers'] = unserialize($page['page_like_users']);
		}

		return $page;
	}

	public function getPagesBySlugs($pageSlugs)
	{
		if (!$pages = $this->fetchAllKeyed("
			SELECT *
				FROM EWRcarta_pages
			WHERE page_slug IN (" . $this->_getDb()->quote($pageSlugs) . ")
		", 'page_slug'))
		{
			return array();
		}

        return $pages;
	}

	public function getPageByID($pageID)
	{
		$page = $this->_getDb()->fetchRow("
			SELECT *
				FROM EWRcarta_pages
			WHERE page_id = ?
		", $pageID);

		return $page;
	}

	public function getPagesByIDs($pageIDs)
	{
		if (!$pages = $this->fetchAllKeyed("
			SELECT *
				FROM EWRcarta_pages
			WHERE page_id IN (" . $this->_getDb()->quote($pageIDs) . ")
		", 'page_id'))
		{
			return array();
		}

        return $pages;
	}

	public function getPageIDsInRange($pageID, $limit)
	{
		return $this->_getDb()->fetchCol($this->_getDb()->limit('
			SELECT page_id
				FROM EWRcarta_pages
			WHERE page_id > ?
			ORDER BY page_id
		', $limit), $pageID);
	}

	public function getPageCount()
	{
        $count = $this->_getDb()->fetchRow("
			SELECT COUNT(*) AS total
				FROM EWRcarta_pages
		");

		return $count['total'];
	}
	
	public function getPageByThread($threadID)
	{
		if (!$page = $this->_getDb()->fetchRow("SELECT * FROM EWRcarta_pages WHERE thread_id = ?", $threadID))
		{
			return false;
		}

        return $page;
	}

	public function updatePage($input, $bypass = false)
	{
		$dw = XenForo_DataWriter::create('EWRcarta_DataWriter_Pages');

		if (!empty($input['page_id']) && $page = $this->getPageById($input['page_id']))
		{
			$dw->setExistingData($input);
		}

		if ($input['page_type'] == 'bbcode')
		{
			$input['page_content'] = XenForo_Helper_String::autoLinkBbCode($input['page_content']);
		}

		$dw->bulkSet(array(
			'page_name'    => $input['page_name'],
			'page_slug' => $input['page_slug'],
			'page_type' => $input['page_type'],
			'page_content' => $input['page_content'],
			'page_parent' => $input['page_parent'],
		));
		
		if (isset($input['page_index']))
		{
			$dw->bulkSet(array(
				'page_index' => $input['page_index'],
				'page_protect' => $input['page_protect'],
				'page_sidebar' => $input['page_sidebar'],
				'page_sublist' => $input['page_sublist'],
			));
		}
		
		if (isset($input['administrators']))
		{
			if ($input['administrators'] !== '')
			{
				$usernames = explode(',', $input['administrators']);
				$users = $this->getModelFromCache('XenForo_Model_User')->getUsersByNames($usernames);
				$userIDs = array();
				
				foreach ($users AS $user)
				{
					$userIDs[] = $user['user_id'];
				}
				
				$input['page_admins'] = implode(',', $userIDs);
			}
			else
			{
				$input['page_admins'] = '';
			}
			
			if ($input['usernames'] !== '')
			{
				$usernames = explode(',', $input['usernames']);
				$users = $this->getModelFromCache('XenForo_Model_User')->getUsersByNames($usernames);
				$userIDs = array();
				
				foreach ($users AS $user)
				{
					$userIDs[] = $user['user_id'];
				}
				
				$input['page_users'] = implode(',', $userIDs);
			}
			else
			{
				$input['page_users'] = '';
			}
			
			$dw->bulkSet(array(
				'page_admins' => $input['page_admins'],
				'page_groups' => $input['page_groups'],
				'page_users' => $input['page_users'],
			));
		}
		
		$dw->setExtraData(XenForo_DataWriter_DiscussionMessage::DATA_ATTACHMENT_HASH, $input['attachment_hash']);
		$dw->save();
		
		$input['page_id'] = $dw->get('page_id');
		$input['page_slug'] = $dw->get('page_slug');
		$input['page_date'] = $dw->get('page_date');
		$input['thread_id'] = $dw->get('thread_id');
		
		if ($input['thread_id'] && ($dw->isChanged('page_title') || $dw->isChanged('page_slug')))
		{
			$this->getModelFromCache('EWRcarta_Model_Threads')->updateThread($input);
		}

		if ($dw->isChanged('page_content'))
		{
			$visitor = XenForo_Visitor::getInstance();
			$history = $this->getModelFromCache('EWRcarta_Model_History')->getHistoryByPage($input);
			
			if ($bypass || !($history['user_id'] == $visitor['user_id']
				&& $input['page_date'] < $history['history_date'] + 1800))
			{
				$this->getModelFromCache('EWRcarta_Model_History')->updateHistory($input);

				$this->getModelFromCache('XenForo_Model_NewsFeed')->publish(
					$visitor['user_id'],
					($visitor['user_id'] ? $visitor['username'] : $_SERVER['REMOTE_ADDR']),
					'wiki',
					$input['page_id'],
					'update'
				);
				
				$users = $this->getModelFromCache('EWRcarta_Model_PageWatch')->getUsersWatchingPage($input['page_id']);
				
				foreach ($users AS $user)
				{
					if ($user['user_id'] == $visitor['user_id']) { continue; }
					
					if ($user['email_subscribe'] && $user['email'] && $user['user_state'] == 'valid')
					{
						$mail = XenForo_Mail::create('watched_wiki_update', array(
							'reply' => $visitor,
							'page' => $page,
							'receiver' => $user
						), $user['language_id']);
						$mail->enableAllLanguagePreCache();
						$mail->queue($user['email'], $user['username']);
					}
					
					if (XenForo_Model_Alert::userReceivesAlert($user, 'wiki', 'update'))
					{
						XenForo_Model_Alert::alert(
							$user['user_id'],
							$visitor['user_id'],
							($visitor['user_id'] ? $visitor['username'] : $_SERVER['REMOTE_ADDR']),
							'wiki',
							$input['page_id'],
							'update'
						);
					}
				}
			}
		}

		return $input;
	}

	public function updateViews($page)
	{
		if (!XenForo_Helper_Cookie::getCookie('EWRcarta_'.$page['page_id']))
		{
			$this->_getDb()->query("
				UPDATE EWRcarta_pages
				SET page_views = page_views+1
				WHERE page_id = ?
			", $page['page_id']);

			$page['page_views']++;
		}

		XenForo_Helper_Cookie::setCookie('EWRcarta_'.$page['page_id'], '1', 86400);
		
		$this->standardizeViewingUserReference($viewingUser);
		$userID = $viewingUser['user_id'];
		
		if ($userID)
		{
			$this->_getDb()->query('
				INSERT INTO EWRcarta_read
					(user_id, page_id, page_read_date)
				VALUES
					(?, ?, ?)
				ON DUPLICATE KEY UPDATE page_read_date = VALUES(page_read_date)
			', array($userID, $page['page_id'], XenForo_Application::$time));
		}

		return $page;
	}

	public function deletePage($input)
	{
		$contentIds = array($input['page_id']);

		$this->getModelFromCache('XenForo_Model_Alert')->deleteAlerts('wiki', $input['page_id']);
		$this->getModelFromCache('XenForo_Model_Attachment')->deleteAttachmentsFromContentIds('wiki', $contentIds);
		$this->getModelFromCache('XenForo_Model_Like')->deleteContentLikes('wiki', $contentIds);
		$this->getModelFromCache('XenForo_Model_NewsFeed')->delete('wiki', $input['page_id']);
		$this->getModelFromCache('EWRcarta_Model_Threads')->closeThread($input['thread_id']);

		$ipEntries = $this->_getDb()->fetchAll("
			SELECT history_id
				FROM EWRcarta_history
			WHERE page_id = ?
		", $input['page_id']);

		$history = array();
		foreach ($ipEntries AS $entry)
		{
			$history[] = $entry['history_id'];
		}
		$history = implode(",", $history);

		$this->_getDb()->query("
			DELETE FROM xf_ip
			WHERE content_type = 'wiki'
				AND content_id IN ( $history )
		");

		$this->_getDb()->query("
			DELETE FROM EWRcarta_cache
			WHERE page_id = ?
		", $input['page_id']);

		$this->_getDb()->query("
			DELETE FROM EWRcarta_history
			WHERE page_id = ?
		", $input['page_id']);

		$this->_getDb()->query("
			UPDATE EWRcarta_pages
			SET page_parent = ?
			WHERE page_parent = ?
		", array($input['page_parent'], $input['page_id']));

		$dw = XenForo_DataWriter::create('EWRcarta_DataWriter_Pages');
		$dw->setExistingData($input);
		$dw->delete();

		return true;
	}
}