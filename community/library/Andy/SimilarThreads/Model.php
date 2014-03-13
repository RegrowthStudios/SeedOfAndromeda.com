<?php

class Andy_SimilarThreads_Model extends XenForo_Model
{
	public function getCommonWords()
	{
		//########################################
		// This was put into the model so that
		// other add-on could easily extend the
		// commonWords variable.
		//########################################		
		
		// get options from Admin CP -> Options -> Similar Threads -> Common Words  
		$commonWords = XenForo_Application::get('options')->commonWords;
		
		// convert to lowercase
		$commonWords = strtolower($commonWords);
		
		// put commonWords into an array
		$commonWords = explode(' ', $commonWords);		
		
		// return
		return $commonWords;
	}
	
	public function getThreads($safeSearchWord1,$safeSearchWord2,$currentNodeId,$currentThreadId)
	{ 
		// declare variables
		$whereclause1 = '';
		$whereclause2 = '';
		$whereclause3 = '';
		$results1 = array();
		$results2 = array();
		$results3 = array();
		$excludeResults1 = '';
		$excludeResults2 = '';
		$resultsCount1 = array();
		$resultsCount2 = array();
	
		//########################################
		// $whereclause1
		// permissions
		//########################################
					
		// get node list
		$viewableNodes = $this->getModelFromCache('XenForo_Model_Node')->getViewableNodeList();
		
		// get $nodeIds
		foreach ($viewableNodes as $node)
		{
			$nodeIds[] = $node['node_id'];
		}
		
		// create whereclause of viewable nodes
		$whereclause1 = 'AND (xf_thread.node_id = ' . implode(' OR xf_thread.node_id = ', $nodeIds);
		$whereclause1 = $whereclause1 . ')';
		
		//########################################
		// $whereclause2
		// exclude thread that is being viewed
		//########################################
		
		// if coming from Thread.php don't include the thread we are viewing
		if (isset($currentThreadId))
		{
			$whereclause2 = "AND xf_thread.thread_id <> '$currentThreadId'";
		}
		
		//########################################
		// $whereclause3
		// show results from same forum
		//########################################
		
		// get options from Admin CP -> Options -> Similar Threads -> Show Results From Same Forum    
        $sameForum = XenForo_Application::get('options')->sameForum;
		
		// check if coming from Thread.php 
		$visitor = XenForo_Visitor::getInstance();
        $userId = $visitor['user_id'];	
		 
		$params = $this->_getDb()->fetchOne("
		SELECT params
		FROM xf_session_activity
		WHERE user_id = '$userId'
		AND controller_action = 'CreateThread'
		");
		
		if ($params != '') 
		{
			$pos1 = strpos($params,'node_id=');
			
			if (is_numeric($pos1))
			{
				$currentNodeId = substr($params,8);
			}
		}
		
		// create $whereclause3				
		if ($sameForum == 1 AND $currentNodeId != '')
		{
			$whereclause3 = "AND xf_thread.node_id = '$currentNodeId'";
		}
		
		//########################################
		// search 1
		// $safeSearchWord1 AND $safeSearchWord2
		//########################################
		
		// get option from Admin CP -> Options -> Similar Threads -> Maximum Results	
		$maxResults = XenForo_Application::get('options')->maxResults; 		
		
		if ($safeSearchWord1 != '' AND $safeSearchWord2 != '')
		{
			// get threads
			$results1 = $this->_getDb()->fetchAll("
				SELECT xf_thread.thread_id, xf_thread.title, xf_thread.node_id, xf_node.title AS nodetitle, xf_thread.post_date
				FROM xf_thread
				INNER JOIN xf_node ON xf_node.node_id = xf_thread.node_id
				WHERE xf_thread.title LIKE '%$safeSearchWord1%'
				AND xf_thread.title LIKE '%$safeSearchWord2%'
				AND xf_thread.discussion_state = 'visible'
				AND xf_thread.discussion_type <> 'redirect'
				$whereclause1
				$whereclause2
				$whereclause3
				ORDER BY xf_thread.thread_id DESC
				LIMIT $maxResults
			");	
			
			// prepare $results for return
			$results = $results1;
		}
		
		//########################################
		// search 2
		// $safeSearchWord1
		//########################################

		if ($safeSearchWord1 != '')
		{
		
			foreach ($results1 AS $k => $v)
			{
				$resultsCount1[] = $v['thread_id'];
				
				// exclude previously found thread_id's
				$excludeResults1 = 'AND xf_thread.thread_id <> ' . implode(' AND xf_thread.thread_id <> ', $resultsCount1);
			}
			
			$count = count($resultsCount1);
			
			if ($count < $maxResults AND is_numeric($count))
			{
				$maxResults2 = $maxResults - $count;
				
				// get threads
				$results2 = $this->_getDb()->fetchAll("
					SELECT xf_thread.thread_id, xf_thread.title, xf_thread.node_id, xf_node.title AS nodetitle, xf_thread.post_date
					FROM xf_thread
					INNER JOIN xf_node ON xf_node.node_id = xf_thread.node_id
					WHERE xf_thread.title LIKE '%$safeSearchWord1%'
					AND xf_thread.discussion_state = 'visible'
					AND xf_thread.discussion_type <> 'redirect'
					$whereclause1
					$whereclause2
					$whereclause3
					$excludeResults1
					ORDER BY xf_thread.thread_id DESC
					LIMIT $maxResults2
				");	
				
				// prepare $results for return
				$results = array_merge($results1, $results2);
			}
		}
		
		//########################################
		// search 3
		// $safeSearchWord2
		//########################################
		
		if ($safeSearchWord2 != '')
		{			
			foreach ($results2 AS $k => $v)
			{
				$resultsCount2[] = $v['thread_id'];
				
				// exclude previously found thread_id's
				$excludeResults2 = 'AND xf_thread.thread_id <> ' . implode(' AND xf_thread.thread_id <> ', $resultsCount2);
			}
			
			$count = count($resultsCount1) + count($resultsCount2);
			
			if ($count < $maxResults AND is_numeric($count))
			{
				$maxResults3 = $maxResults - $count;
				
				// get threads
				$results3 = $this->_getDb()->fetchAll("
					SELECT xf_thread.thread_id, xf_thread.title, xf_thread.node_id, xf_node.title AS nodetitle, xf_thread.post_date
					FROM xf_thread
					INNER JOIN xf_node ON xf_node.node_id = xf_thread.node_id
					WHERE xf_thread.title LIKE '%$safeSearchWord2%'
					AND xf_thread.discussion_state = 'visible'
					AND xf_thread.discussion_type <> 'redirect'
					$whereclause1
					$whereclause2
					$whereclause3
					$excludeResults1
					$excludeResults2
					ORDER BY xf_thread.thread_id DESC
					LIMIT $maxResults3
				");	
				
				// prepare $results for return
				$results = array_merge($results1, $results2, $results3);
			} 
		}
	
		//########################################
		// return results
		//########################################	

		return $results;	
	}
}

?>