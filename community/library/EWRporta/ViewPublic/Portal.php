<?php

class EWRporta_ViewPublic_Portal extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
	}
	
	public function renderRss()
	{
		$options = XenForo_Application::get('options');
		$title = ($options->boardTitle ? $options->boardTitle : XenForo_Link::buildPublicLink('canonical:portal'));
		$description = ($options->boardDescription ? $options->boardDescription : $title);

		$buggyXmlNamespace = (defined('LIBXML_DOTTED_VERSION') && LIBXML_DOTTED_VERSION == '2.6.24');

		$feed = new Zend_Feed_Writer_Feed();
		$feed->setEncoding('utf-8');
		$feed->setTitle($title);
		$feed->setDescription($description);
		$feed->setLink(XenForo_Link::buildPublicLink('canonical:portal'));
		if (!$buggyXmlNamespace)
		{
			$feed->setFeedLink(XenForo_Link::buildPublicLink('canonical:portal/feed.rss'), 'rss');
		}
		$feed->setDateModified(XenForo_Application::$time);
		$feed->setLastBuildDate(XenForo_Application::$time);
		$feed->setGenerator($title);

		if (!empty($this->_params['threads']))
		{
			foreach ($this->_params['threads'] AS $thread)
			{
				// TODO: add contents of first post in future

				$entry = $feed->createEntry();
				$entry->setTitle($thread['title']);
				$entry->setLink(XenForo_Link::buildPublicLink('canonical:threads', $thread));
				$entry->setDateCreated(new Zend_Date($thread['post_date'], Zend_Date::TIMESTAMP));
				$entry->setDateModified(new Zend_Date($thread['last_post_date'], Zend_Date::TIMESTAMP));
				if (!$buggyXmlNamespace)
				{
					$entry->addAuthor(array(
						'name' => $thread['username'],
						'uri' => XenForo_Link::buildPublicLink('canonical:members', $thread)
					));
					if ($thread['reply_count'])
					{
						$entry->setCommentCount($thread['reply_count']);
					}
				}

				$feed->addEntry($entry);
			}
		}

		return $feed->export('rss');
	}
}