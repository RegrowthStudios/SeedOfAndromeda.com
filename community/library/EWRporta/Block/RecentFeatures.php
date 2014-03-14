<?php

class EWRporta_Block_RecentFeatures extends XenForo_Model
{
	public function getModule(&$options)
	{
		$options['forum'] = implode(',', $this->getModelFromCache('EWRporta_Model_Promotes')->getPromoteForums());

		$news = $this->_getDb()->fetchAll("
			SELECT xf_thread.*, xf_post.message, xf_attachment.*, xf_attachment_data.*,
				IF(EWRporta_promotes.promote_date IS NULL, xf_thread.post_date, EWRporta_promotes.promote_date) AS promote_date
			FROM xf_thread
				INNER JOIN xf_post ON (xf_post.post_id = xf_thread.first_post_id)
				INNER JOIN xf_attachment ON (xf_attachment.content_id = xf_thread.first_post_id AND xf_attachment.content_type = 'post')
				INNER JOIN xf_attachment_data ON (xf_attachment_data.data_id = xf_attachment.data_id AND xf_attachment_data.filename = ? AND xf_attachment_data.thumbnail_width > 0)
				LEFT JOIN EWRporta_promotes ON (EWRporta_promotes.thread_id = xf_thread.thread_id)
			WHERE (xf_thread.node_id IN (".$options['forum'].") OR EWRporta_promotes.promote_date < ?)
				AND xf_thread.discussion_state = 'visible'
				AND IF(EWRporta_promotes.promote_date IS NULL, xf_thread.post_date, EWRporta_promotes.promote_date) < ?
			ORDER BY promote_date DESC
			LIMIT ?
		", array($options['filename'], XenForo_Application::$time, XenForo_Application::$time, $options['limit']));
		
		$options['parseText'] = true;
		
		$options['bullets'] = $options['bullets'] ? 'true' : 'false';
		$options['controls'] = $options['controls'] ? 'true' : 'false';
		$options['autorotate'] = $options['autorotate'] ? 'true' : 'false';

        return $news;
	}
}