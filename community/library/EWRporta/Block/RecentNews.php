<?php

class EWRporta_Block_RecentNews extends XenForo_Model
{
	public function getModule(&$options, $page, $category = false)
	{
		$page = ($page - 1) * $options['limit'];
		$fromWhere = '';
		$fromOrder = '';

		if ($category)
		{
			$fromWhere = "INNER JOIN EWRporta_catlinks ON (EWRporta_catlinks.thread_id = xf_thread.thread_id)
				INNER JOIN EWRporta_categories ON (EWRporta_categories.category_id = EWRporta_catlinks.category_id AND EWRporta_categories.category_slug = '".$category."')";
		}
		
		if ($options['sticky'])
		{
			$fromOrder = "sticky DESC, ";
		}

		$news = $this->_getDb()->fetchAll("
			SELECT xf_thread.*, xf_user.*, xf_post.message, xf_post.attach_count, xf_node.title AS node_title, 
				IF(xf_user.username IS NULL, xf_thread.username, xf_user.username) AS username,
				IF(EWRporta_promotes.promote_date IS NULL, xf_thread.post_date, EWRporta_promotes.promote_date) AS promote_date,
				EWRporta_promotes.promote_icon, EWRporta_promotes.promote_data
			FROM xf_thread
				LEFT JOIN xf_user ON (xf_user.user_id = xf_thread.user_id)
				INNER JOIN xf_post ON (xf_post.post_id = xf_thread.first_post_id)
				INNER JOIN xf_node ON (xf_node.node_id = xf_thread.node_id)
				LEFT JOIN EWRporta_promotes ON (EWRporta_promotes.thread_id = xf_thread.thread_id)
			$fromWhere
			WHERE ( xf_thread.node_id IN (".$this->_getDb()->quote($options['forum']).") OR EWRporta_promotes.promote_date < ? )
				AND xf_thread.discussion_state = 'visible'
				AND IF(EWRporta_promotes.promote_date IS NULL, xf_thread.post_date, EWRporta_promotes.promote_date) < ?
			ORDER BY $fromOrder promote_date DESC
			LIMIT ?, ?
		", array(XenForo_Application::$time, XenForo_Application::$time, $page, $options['limit']));

		foreach ($news AS &$post)
		{
			if (!$options['social'])
			{
				$post['categories'] = $this->getModelFromCache('EWRporta_Model_Categories')->getCategoryLinks($post);
			}
		
			$strtime = new DateTime(date('r', $post['promote_date']));
			$strtime->setTimezone(new DateTimeZone(XenForo_Application::get('options')->guestTimeZone));
			list($post['month'], $post['day']) = explode('.', $strtime->format('n.d'));
			$post['month'] = new XenForo_Phrase('month_'.$post['month'].'_short');

			if ($post['attach_count'])
			{
				$post['attachments'] = $this->getModelFromCache('XenForo_Model_Attachment')->getAttachmentsByContentId('post', $post['first_post_id']);
				$post['attachments'] = $this->getModelFromCache('XenForo_Model_Attachment')->prepareAttachments($post['attachments']);
			}

			if ($post['promote_icon'] != 'disabled')
			{
				switch ($post['promote_icon'])
				{
					case 'avatar':		$post['showIcon'] = true;														break;
					case 'attach':		if ($post['attach'] = $this->getAttach($post)) { $post['showIcon'] = true; }	break;
					case 'image':		if ($post['image'] = $this->getImage($post)) { $post['showIcon'] = true; }		break;
					case 'medio':		if ($post['medio'] = $this->getMedio($post)) { $post['showIcon'] = true; }		break;
				}

				if (empty($post['showIcon']))
				{
					$post = $this->getDefault($post);
				}
			}

			$post['message'] = str_ireplace('prbreak]', 'prebreak]', $post['message']);
			$post['message'] = preg_replace('#\n{3,}#', "\n\n", trim($post['message']));

			if ($trimLoc = stripos($post['message'], '[prebreak]'))
			{
				$prbreak = '';

				if (($breakLoc = stripos($post['message'], '[/prebreak]', $trimLoc+10)) && ($length = $breakLoc - $trimLoc-10))
				{
					$link = XenForo_Link::buildPublicLink('full:threads', $post);
					$prbreak = " [url='".$link."']".substr($post['message'], $trimLoc+10, $length).'[/url] [size=2][...][/size]';
				}

				$post['message'] = substr($post['message'], 0, $trimLoc).$prbreak;
			}
			else
			{
				$post['message'] = XenForo_Helper_String::wholeWordTrim($post['message'], $options['truncate']);
			}
		}

		$options['parseBB'] = true;
		$options['count'] = $this->getCount($options, $category);

        return $news;
	}

	public function getCount($options, $category = false)
	{
		$fromWhere = '';

		if ($category)
		{
			$fromWhere = "INNER JOIN EWRporta_catlinks ON (EWRporta_catlinks.thread_id = xf_thread.thread_id)
				INNER JOIN EWRporta_categories ON (EWRporta_categories.category_id = EWRporta_catlinks.category_id AND EWRporta_categories.category_slug = '".$category."')";
		}

        $count = $this->_getDb()->fetchRow("
			SELECT COUNT(*) AS total
				FROM xf_thread
				LEFT JOIN EWRporta_promotes ON (EWRporta_promotes.thread_id = xf_thread.thread_id)
			$fromWhere
			WHERE (xf_thread.node_id IN (".$this->_getDb()->quote($options['forum']).") OR EWRporta_promotes.promote_date < ?)
				AND xf_thread.discussion_state = 'visible'
				AND IF(EWRporta_promotes.promote_date IS NULL, xf_thread.post_date, EWRporta_promotes.promote_date) < ?
		", array(XenForo_Application::$time, XenForo_Application::$time));

		return $count['total'];
	}

	public function getAttach(&$post)
	{
		if (!empty($post['attachments'][$post['promote_data']]))
		{
			if ($post['attachments'][$post['promote_data']]['thumbnailUrl'])
			{
				$post['message'] = str_ireplace('[attach]'.$post['promote_data'].'[/attach]', '', $post['message']);
				$post['message'] = str_ireplace('[attach=full]'.$post['promote_data'].'[/attach]', '', $post['message']);
				return $post['attachments'][$post['promote_data']];
			}
		}

		return false;
	}

	public function getImage(&$post)
	{
		$post['message'] = str_ireplace('[img]'.$post['promote_data'].'[/img]', '', $post['message']);
		return $post['promote_data'];
	}

	public function getMedio(&$post)
	{
		if (XenForo_Application::autoload('EWRmedio_Model_Media'))
		{
			if ($medio = $this->getModelFromCache('EWRmedio_Model_Media')->getMediaByID($post['promote_data']))
			{
				$post['message'] = str_ireplace('[medio]'.$post['promote_data'].'[/medio]', '', $post['message']);
				$post['message'] = str_ireplace('[medio=full]'.$post['promote_data'].'[/medio]', '', $post['message']);
				return $medio;
			}
		}

		return false;
	}

	public function getDefault($post)
	{
		if (preg_match('#\[medio\](\d+)\[/medio\]#i', $post['message'], $matches))
		{
			$post['promote_data'] = $matches[1];
			$post['medio'] = $this->getMedio($post);
			return $post;
		}
		
		if (!empty($post['attachments']))
		{
			foreach ($post['attachments'] AS $attach)
			{
				if ($attach['thumbnailUrl'])
				{
					$post['promote_data'] = $attach['attachment_id'];
					$post['attach'] = $this->getAttach($post);
					return $post;
				}
			}
		}

		if (preg_match('#\[img\](.+?)\[/img\]#i', $post['message'], $matches))
		{
			$post['promote_data'] = $matches[1];
			$post['image'] = $this->getImage($post);
			return $post;
		}

		return $post;
	}
}