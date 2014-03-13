<?php

class Andy_ForumModerators_ControllerPublic_Forum extends XFCP_Andy_ForumModerators_ControllerPublic_Forum
{
	public function actionForum()
	{
		//########################################
		// Shows moderator names with link to each
		// moderator.
		//########################################
		
		// get parent		
		$parent = parent::actionForum();
		
		// declare variables
		$mod = array();
		$superMod = array();
		$moderators	= array();
		$parentNodeId = '';
		$whereclause1 = '';
		$whereclause2 = '';	
		$nodeHierarchy = array();		
		
		// get forumId and forumName
		$forumId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);
		
		// get forumName (URL Portion)
		$forumName = $this->_input->filterSingle('node_name', XenForo_Input::STRING);
		
		// get options from Admin CP -> Options -> Forum Moderators -> Show Forum Moderators
		$showForumModerators = XenForo_Application::get('options')->showForumModerators;
		
		// get options from Admin CP -> Options -> Forum Moderators -> Show Super Moderators
		$showSuperModerators = XenForo_Application::get('options')->showSuperModerators;
		
		// get options from Admin CP -> Options -> Forum Moderators -> Exclude
		$forumModeratorsExclude = XenForo_Application::get('options')->forumModeratorsExclude;
		
		// create whereclause1
		if ($forumModeratorsExclude	!= '')
		{
			// put into an array
			$excludeArray = explode(',', $forumModeratorsExclude);			

			// create whereclause1
			$whereclause1 = 'AND (xf_user.user_id <> ' . implode(' AND xf_user.user_id <> ', $excludeArray);
			$whereclause1 = $whereclause1 . ')';
		}

		// get database
		$db = XenForo_Application::get('db');

		// forum moderators
		if ($showForumModerators)
		{
			// if using (URL Portion) get forumId
			if ($forumId == 0)
			{
				$forumId = $db->fetchOne("
				SELECT node_id
				FROM xf_node
				WHERE node_name = ?
				", $forumName);	
			}
			
			// continue only if we have a forumId number
			if ($forumId > 0)
			{
				//########################################
				// create whereclause1
				
				// get breadcrumb data
				$breadcrumbData = $db->fetchOne("
					SELECT breadcrumb_data
					FROM xf_node
					WHERE node_id = ?
				", $forumId);				

				// unserialize blob data
				$results = unserialize($breadcrumbData);
				
				// get nodeHierarchy
				foreach ($results as $k => $v)
				{
					$nodeHierarchy[] = $v['node_id'];	
				}
				
				if (!empty($nodeHierarchy))
				{
					// create whereclause2
					$whereclause2 = 'OR (xf_moderator_content.content_id = ' . implode(' OR xf_moderator_content.content_id = ', $nodeHierarchy);
					$whereclause2 = $whereclause2 . ')';					
				}
				
				//########################################
				// run moderators query
				
				$mod = $db->fetchAll("
				SELECT xf_user.*
				FROM xf_moderator_content
				INNER JOIN xf_user ON xf_user.user_id = xf_moderator_content.user_id
				WHERE xf_moderator_content.content_id = " . $forumId . "
				$whereclause1
				$whereclause2
				AND xf_moderator_content.content_type = 'node'
				ORDER BY xf_user.username ASC
				");
			}
		}

		// super moderators
		if ($showSuperModerators)
		{
			$superMod = $db->fetchAll("
			SELECT xf_user.*
			FROM xf_moderator
			INNER JOIN xf_user ON xf_user.user_id = xf_moderator.user_id
			WHERE xf_moderator.is_super_moderator = '1'
			$whereclause1
			ORDER BY username ASC
			");	
		}
	
		// merge arrays
		$moderators = array_merge($mod, $superMod);
		
		// sort multi-dimensional array by value
		function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
			$sort_col = array();
			foreach ($arr as $key=> $row) {
				$sort_col[$key] = $row[$col];
			}
		
			array_multisort($sort_col, $dir, $arr);
		}
		
		// sort by last_activity
		array_sort_by_column($moderators, 'username');			
		
		// count moderators
		$modCount = count($moderators);		

		// prepare viewParams
		if ($parent instanceOf XenForo_ControllerResponse_View)
		{
			$viewParams = array(
				'modCount' => $modCount,
				'moderators' => $moderators
			);
			
			// add viewParams to parent params
			$parent->params += $viewParams;
		}	
		
		// return parent
		return $parent;
	}
	
	public function actionModerators()
	{
		//########################################
		// Shows moderator link which brings up
		// an overlay.
		//########################################	
			
		// declare variables
		$mod = array();
		$superMod = array();
		$moderators	= array();
		$parentNodeId = '';
		$whereclause1 = '';
		$whereclause2 = '';	
		$nodeHierarchy = array();						
		
		// get forumId and forumName
		$forumId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);
		
		// get forumName (URL Portion)
		$forumName = $this->_input->filterSingle('node_name', XenForo_Input::STRING);
		
		// get options from Admin CP -> Options -> Forum Moderators -> Show Forum Moderators
		$showForumModerators = XenForo_Application::get('options')->showForumModerators;
		
		// get options from Admin CP -> Options -> Forum Moderators -> Show Super Moderators
		$showSuperModerators = XenForo_Application::get('options')->showSuperModerators;
		
		// get options from Admin CP -> Options -> Forum Moderators -> Exclude
		$forumModeratorsExclude = XenForo_Application::get('options')->forumModeratorsExclude;
		
		// create whereclause1
		if ($forumModeratorsExclude	!= '')
		{
			// put into an array
			$excludeArray = explode(',', $forumModeratorsExclude);			

			// create whereclause1
			$whereclause1 = 'AND (xf_user.user_id <> ' . implode(' AND xf_user.user_id <> ', $excludeArray);
			$whereclause1 = $whereclause1 . ')';
		}								

		// get database
		$db = XenForo_Application::get('db');
		
		// get forumTitle
		if ($forumId > 0)
		{
			$forumTitle = $db->fetchOne("
			SELECT title
			FROM xf_node
			WHERE node_id = ?
			", $forumId);	
		}
		
		// get forumTitle (URL Portion)
		if ($forumId == '')
		{
			$forumTitle = $db->fetchOne("
			SELECT title
			FROM xf_node
			WHERE title = ?
			", $forumName);	
		}		

		// forum moderators
		if ($showForumModerators)
		{
			// if using (URL Portion) get forumId
			if ($forumId == 0)
			{
				$forumId = $db->fetchOne("
				SELECT node_id
				FROM xf_node
				WHERE node_name = ?
				", $forumName);	
			}
			
			// continue only if we have a forumId number
			if ($forumId > 0)
			{
				//########################################
				// create whereclause
				
				// get breadcrumb data
				$breadcrumbData = $db->fetchOne("
					SELECT breadcrumb_data
					FROM xf_node
					WHERE node_id = ?
				", $forumId);				

				// unserialize blob data
				$results = unserialize($breadcrumbData);
				
				// get nodeHierarchy
				foreach ($results as $k => $v)
				{
					$nodeHierarchy[] = $v['node_id'];	
				}
				
				if (!empty($nodeHierarchy))
				{
					// create whereclause2
					$whereclause2 = 'OR (xf_moderator_content.content_id = ' . implode(' OR xf_moderator_content.content_id = ', $nodeHierarchy);
					$whereclause2 = $whereclause2 . ')';					
				}
				
				// run query
				$mod = $db->fetchAll("
				SELECT xf_user.*
				FROM xf_moderator_content
				INNER JOIN xf_user ON xf_user.user_id = xf_moderator_content.user_id
				WHERE xf_moderator_content.content_id = " . $forumId . "
				$whereclause1
				$whereclause2
				AND xf_moderator_content.content_type = 'node'
				ORDER BY xf_user.username ASC
				");
			}
		}

		// super moderators
		if ($showSuperModerators)
		{
			$superMod = $db->fetchAll("
			SELECT xf_user.*
			FROM xf_moderator
			INNER JOIN xf_user ON xf_user.user_id = xf_moderator.user_id
			WHERE xf_moderator.is_super_moderator = '1'
			$whereclause1
			ORDER BY xf_user.username ASC
			");	
		}
	
		// merge arrays
		$moderators = array_merge($mod, $superMod);
		
		// count moderators
		$modCount = count($moderators);	
		
		//########################################
		// $moderators will have last_activity
		// but this data is only updated every
		// hour. We need to check if the session 
		// table has more current information.
		//########################################
		
		for ($i=0; $i<$modCount; $i++)
		{
			// get session view_date if there is one
			$viewDate = $db->fetchOne("
			SELECT view_date
			FROM xf_session_activity
			WHERE user_id = ?
			", $moderators[$i]['user_id']);
			
			if ($viewDate != '')
			{
				$moderators[$i]['last_activity'] = $viewDate;
			}
		}		

		// sort multi-dimensional array by value
		function array_sort_by_column(&$arr, $col, $dir = SORT_DESC) {
			$sort_col = array();
			foreach ($arr as $key=> $row) {
				$sort_col[$key] = $row[$col];
			}
		
			array_multisort($sort_col, $dir, $arr);
		}
		
		// sort by last_activity
		array_sort_by_column($moderators, 'last_activity');		
		
		// prepare viewParams
		$viewParams = array(
			'modCount' => $modCount,
			'moderators' => $moderators,
			'forumTitle' => $forumTitle
		);
		
		// send to template
		return $this->responseView('Andy_ForumModerators_ViewPublic_Forum','andy_forummoderators_overlay',$viewParams);
	}
}

?>