<?php

class EWRporta_Model_Post extends XFCP_EWRporta_Model_Post
{
	public function getPostsInThread($threadId, array $fetchOptions = array())
	{
		$options = XenForo_Application::get('options');

		if ($options->EWRporta_globalize['article'])
		{
			$forum = $this->getModelFromCache('XenForo_Model_Forum')->getForumByThreadId($threadId);
		
			if (!empty($forum['node_id']) && in_array($forum['node_id'], $this->getModelFromCache('EWRporta_Model_Promotes')->getPromoteForums())
				|| $this->getModelFromCache('EWRporta_Model_Promotes')->getPromoteByThreadId($threadId))
			{
				$fetchOptions['join'] += self::FETCH_THREAD;

				$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
				$stateLimit = $this->prepareStateLimitFromConditions($fetchOptions, 'post');
				$joinOptions = $this->preparePostJoinOptions($fetchOptions);

				return $this->fetchAllKeyed('
					SELECT post.*
						' . $joinOptions['selectFields'] . '
					FROM xf_post AS post
						' . $joinOptions['joinTables'] . '
					WHERE post.thread_id = ?
						AND (((' . $stateLimit . ')
						' . $this->addPositionLimit('post', $limitOptions['limit'], $limitOptions['offset']) . ')
						OR post.post_id = thread.first_post_id)
					ORDER BY post.position ASC, post.post_date ASC
				', 'post_id', $threadId);
			}
		}

		return parent::getPostsInThread($threadId, $fetchOptions);
	}

	public function getQuoteTextForPost(array $post, $maxQuoteDepth = 0)
	{
		$response = parent::getQuoteTextForPost($post, $maxQuoteDepth);
		$response = preg_replace('#\[pre?break\].*?\[/pre?break\]#si', '', $response);
		return $response;
	}
}