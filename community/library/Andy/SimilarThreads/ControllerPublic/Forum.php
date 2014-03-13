<?php

class Andy_SimilarThreads_ControllerPublic_Forum extends XFCP_Andy_SimilarThreads_ControllerPublic_Forum
{
    public function actionCreateThread()
    {
		//########################################
		// Create loadJavaScript variable and
		// add to params.
		//########################################
		
        // get parent     
        $parent = parent::actionCreateThread();
		
		// declare variable
		$loadJavaScript = '';
		
        // get options from Admin CP -> Options -> Similar Threads -> Show Create Thread
        $showCreateThread = XenForo_Application::get('options')->showCreateThread;
		
		// run if showCreateThread
		if ($showCreateThread)
		{
			// get nodeId       
			$forumId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);
			$forumName = $this->_input->filterSingle('node_name', XenForo_Input::STRING);
			$ftpHelper = $this->getHelper('ForumThreadPost');
			$forum = $ftpHelper->assertForumValidAndViewable($forumId ? $forumId : $forumName);
			$nodeId = $forum['node_id'];       
	   
			// get options from Admin CP -> Options -> Similar Threads -> Exclude Forums  
			$excludeForums = XenForo_Application::get('options')->similarThreadsExcludeForums;
	   
			$excludeForumsArray = explode(',', $excludeForums);  
			  
			// check for excluded forums
			if (!in_array($nodeId, $excludeForumsArray))
			{
				// showCreateThread is enabled and forum is not excluded
				$loadJavaScript = true;
			   
				// prepare viewParams
				if ($parent instanceOf XenForo_ControllerResponse_View)
				{
					// prepare viewParams
					$viewParams = array(
						'loadJavaScript' => $loadJavaScript
					);
				   
					// add viewParams to parent params
					$parent->params += $viewParams;
				}
			}
		}
      
        // return parent
        return $parent;
	}
	
    public function actionSimilarThreads()
    {
		//########################################
		// Show similar threads when member is
		// creating a new thread.
		//########################################
			
		// declare variables
		$currentNodeId = '';
		$currentThreadId = '';
		$similarThreads = array();
		$searchWords = array();
		$searchWord1 = '';
		$searchWord2 = '';			
		$safeSearchWord1 = '';
		$safeSearchWord2 = '';
		
		// get newTitle
		$newTitle = $this->_request->getParam('title');
		
        // get options from Admin CP -> Options -> Similar Threads -> Remove Punctuations
        $removePunctuations = XenForo_Application::get('options')->removePunctuations;
		
		// put into array
		$removePunctuationsArray = explode(' ', $removePunctuations);				
		
		// remove punctuations			
		$newTitle = str_replace($removePunctuationsArray, '', $newTitle);	

		// put into array
		$newTitle = explode(' ', $newTitle);
		
		// get common words in model    
		$commonWords = $this->getModelFromCache('Andy_SimilarThreads_Model')->getCommonWords();
		
		// remove any common words from array
		foreach ($newTitle as $var)
		{
			if (!in_array(strtolower($var), $commonWords))
			{
				// get options from Admin CP -> Options -> Similar Threads -> Miniumum Common Word Length    
				$minimumCommonWordLength = XenForo_Application::get('options')->minimumCommonWordLength;					
				
				if (strlen($var) >= $minimumCommonWordLength)
				{
					$searchWords[] = $var;
				}
			}
		}
		
		$count = count($searchWords);
		
		// only continue if we have a search word
		if ($count > 0)
		{				
			// get first search word
			$searchWord1 = $searchWords[0];
			
			// make safe for query
			$safeSearchWord1 = addslashes($searchWords[0]);
			
			if ($count > 1)
			{	
				// get second search word
				$searchWord2 = $searchWords[1];	
						
				// make safe for query
				$safeSearchWord2 = addslashes($searchWords[1]);	
			}			
		}
		
		// run query only if we have a search
		if ($safeSearchWord1 != '')
		{
			// run query in model    
			$similarThreads = $this->getModelFromCache('Andy_SimilarThreads_Model')->getThreads($safeSearchWord1,$safeSearchWord2,$currentNodeId,$currentThreadId);    
		} 
		
		// prepare viewParams for template
		$viewParams = array(
			'similarThreads' => $similarThreads,
			'searchWord1' => $searchWord1,
			'searchWord2' => $searchWord2
		);
		
		// send to template
		return $this->responseView('Andy_SimilarThreads_ViewPublic_SimilarThreads', 'andy_similarthreads_create_thread', $viewParams);
	}
}

?>