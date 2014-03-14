<?php

class EWRcarta_Model_Threads extends XenForo_Model
{
	public function buildThread($page, $nodeID)
	{
		if (!in_array($nodeID, XenForo_Application::get('options')->EWRcarta_wikiforum)) { return false; }
		if (!$forum = $this->getModelFromCache('XenForo_Model_Forum')->getForumById($nodeID)) { return false; }
		
		$visitor = XenForo_Visitor::getInstance();

		$writer = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
		$writer->set('user_id', $visitor['user_id']);
		$writer->set('username', $visitor['username']);
		$writer->set('title', $page['page_name']);
		$writer->set('node_id', $forum['node_id']);
			$postWriter = $writer->getFirstMessageDw();
			$postWriter->set('message', '[wiki=full]'.$page['page_slug'].'[/wiki]');
		$writer->save();

		$thread = $writer->getMergedData();
		$this->getModelFromCache('XenForo_Model_Thread')->markThreadRead($thread, $forum, XenForo_Application::$time);

		$dw = XenForo_DataWriter::create('EWRcarta_DataWriter_Pages');
		$dw->setExistingData($page);
		$dw->set('thread_id', $thread['thread_id']);
		$dw->save();

		return true;
	}
	
	public function updateThread($page)
	{
		if (!$thread = $this->getModelFromCache('XenForo_Model_Thread')->getThreadById($page['thread_id']))
		{
			$dw = XenForo_DataWriter::create('EWRcarta_DataWriter_Pages');
			$dw->setExistingData($page);
			$dw->set('thread_id', '0');
			$dw->save();
		
			return false;
		}
	
		$writer = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
		$writer->setExistingData($page['thread_id']);
		$writer->set('title', $page['page_name']);
			$postWriter = $writer->getFirstMessageDw();
			$postWriter->set('message', '[wiki=full]'.$page['page_slug'].'[/wiki]');
		$writer->save();

		return true;
	}

	public function closeThread($threadID)
	{
		if (!$thread = $this->getModelFromCache('XenForo_Model_Thread')->getThreadById($threadID))
		{
			return false;
		}

		$visitor = XenForo_Visitor::getInstance();

		$writer = XenForo_DataWriter::create('XenForo_DataWriter_DiscussionMessage_Post');
		$writer->set('user_id', $visitor['user_id']);
		$writer->set('username', $visitor['username']);
		$writer->set('message', new XenForo_Phrase('wiki_thread_deleted'));
		$writer->set('thread_id', $thread['thread_id']);
		$writer->save();

		$threadWriter = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
		$threadWriter->setExistingData($thread['thread_id']);
		$threadWriter->set('title', $threadWriter->get('title').' [DELETED]');
		$threadWriter->set('discussion_open', 0);
		$threadWriter->save();

		return true;
	}
}