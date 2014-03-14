<?php

class EWRporta_Block_RecentThreads extends XenForo_Model
{
	public function getBypass($params)
	{
		$visitor = XenForo_Visitor::getInstance();

		$conditions = array(
			'last_post_date' => array('>', XenForo_Application::$time - 86400 * $params['option']['cutoff']),
			'deleted' => false,
			'moderated' => false
		);
		if ($params['option']['forum'][0] != '0')
		{
			$conditions['forum_ids'] = $params['option']['forum'];
		}

		$fetchOptions = array(
			'join' => XenForo_Model_Thread::FETCH_FORUM | XenForo_Model_Thread::FETCH_USER,
			'permissionCombinationId' => $visitor['permission_combination_id'],
			'readUserId' => $visitor['user_id'],
			'watchUserId' => $visitor['user_id'],
			'postCountUserId' => $visitor['user_id'],
			'order' => 'last_post_date',
			'orderDirection' => 'desc',
			'limit' => $params['option']['limit'],
		);

		$threads = $this->getThreads($conditions, $fetchOptions);

		foreach ($threads AS $threadID => &$thread)
		{
			if ($this->getModelFromCache('XenForo_Model_Thread')->canViewThreadAndContainer($thread, $thread))
			{
				$thread = $this->getModelFromCache('XenForo_Model_Thread')->prepareThread($thread, $thread);
				$thread['canInlineMod'] = false;
				$thread['showForumLink'] = true;
			}
			else
			{
				unset($threads[$threadID]);
			}
		}

		return $threads;
	}

	public function getThreads(array $conditions, array $fetchOptions = array())
	{
		$whereConditions = $this->getModelFromCache('XenForo_Model_Thread')->prepareThreadConditions($conditions, $fetchOptions);

		$sqlClauses = $this->getModelFromCache('XenForo_Model_Thread')->prepareThreadFetchOptions($fetchOptions);
		$limitOptions = $this->getModelFromCache('XenForo_Model_Thread')->prepareLimitFetchOptions($fetchOptions);

		if (!empty($conditions['forum_ids']))
		{
			$whereConditions .= ' AND thread.node_id IN ('.$this->_getDb()->quote($conditions['forum_ids']).')';
		}

		return $this->fetchAllKeyed($this->limitQueryResults('
				SELECT thread.*
					' . $sqlClauses['selectFields'] . '
				FROM xf_thread AS thread
				' . $sqlClauses['joinTables'] . '
				WHERE ' . $whereConditions . '
				' . $sqlClauses['orderClause'] . '
			', $limitOptions['limit'], $limitOptions['offset']
		), 'thread_id');
	}
}