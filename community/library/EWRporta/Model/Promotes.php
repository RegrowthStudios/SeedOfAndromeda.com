<?php

class EWRporta_Model_Promotes extends XenForo_Model
{
	public function getPromoteByThreadId($threadId)
	{
		if (!$promote = $this->_getDb()->fetchRow("
			SELECT *
				FROM EWRporta_promotes
			WHERE thread_id = ?
		", $threadId))
		{
			return false;
		}

		return $promote;
	}

	public function getPromoteForums()
	{
		if (!$forums = $this->_getDb()->fetchRow("
			SELECT *
				FROM EWRporta_options
			WHERE option_id = ?
		", 'recentnews_forum'))
		{
			return false;
		}

		$forums = unserialize($forums['option_value']);

		return $forums;
	}

	public function getPromoteIcons($input)
	{
		$post = $this->getModelFromCache('XenForo_Model_Post')->getPostById($input['first_post_id']);

		$icons = array(
			'attachments' => array(),
			'imageEmbeds' => array(),
			'medioEmbeds' => array(),
		);

		if ($post['attachments'] = $this->getModelFromCache('XenForo_Model_Attachment')->getAttachmentsByContentId('post', $input['first_post_id']))
		{
			$post['attachments'] = $this->getModelFromCache('XenForo_Model_Attachment')->prepareAttachments($post['attachments']);

			foreach ($post['attachments'] AS $attach)
			{
				if (!empty($attach['thumbnailUrl']))
				{
					$icons['attachments'][] = $attach;
				}
			}
		}

		if (preg_match_all('#\[img\](.+?)\[/img\]#i', $post['message'], $matches))
		{
			foreach ($matches[1] AS $match)
			{
				$url = str_ireplace('http://', '', $match);
				$url = explode('/', $url);
				$icons['imageEmbeds'][] = array(
					'server' => reset($url),
					'filename' => end($url),
					'imageurl' => $match,
				);
			}
		}

		if (XenForo_Application::autoload('EWRmedio_Model_Media'))
		{
			if (preg_match_all('#\[medio\](\d+)\[/medio\]#i', $post['message'], $matches))
			{
				$icons['medioEmbeds'] = $this->getModelFromCache('EWRmedio_Model_Media')->getMediasByIDs($matches[1]);
			}
		}

		return $icons;
	}

	public function updatePromotion($input)
	{
		$dw = XenForo_DataWriter::create('EWRporta_DataWriter_Promotes');

		if ($promote = $this->getPromoteByThreadId($input['thread_id']))
		{
			$dw->setExistingData($promote);
		}

		if ($input['delete'])
		{
			$dw->delete();
			$this->_getDb()->query("DELETE FROM EWRporta_catlinks WHERE thread_id = ?", $input['thread_id']);
			
			$this->getModelFromCache('EWRporta_Model_Caches')->emptyBlockCache(array('block_id'=>'RecentFeatures'));
			$this->getModelFromCache('EWRporta_Model_Caches')->emptyBlockCache(array('block_id'=>'RecentNews'));
		}
		else
		{
			if ($input['ampm'] == 'pm')
			{
				$input['hour'] = $input['hour']+12;
			}

			$input['time'] = $input['hour'] . ":" . str_pad($input['mins'], 2, "0", STR_PAD_LEFT);

			$datetime = $input['date']." ".$input['time']." ".$input['ampm']." ".$input['zone'];

			$dw->bulkSet(array(
				'thread_id' => $input['thread_id'],
				'promote_date' => strtotime($datetime),
				'promote_icon' => $input['promote_icon'],
			));

			switch ($input['promote_icon'])
			{
				case 'attach':	$dw->set('promote_data', $input['attach_data']);	break;
				case 'image':	$dw->set('promote_data', $input['image_data']);		break;
				case 'medio':	$dw->set('promote_data', $input['medio_data']);		break;
				default:		$dw->set('promote_data', 0);
			}

			$dw->save();
		}

		return true;
	}
}