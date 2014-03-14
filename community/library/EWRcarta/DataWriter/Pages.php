<?php

class EWRcarta_DataWriter_Pages extends XenForo_DataWriter
{
	const DATA_ATTACHMENT_HASH = 'attachmentHash';
	protected $_existingDataErrorPhrase = 'requested_page_not_found';

	protected function _getFields()
	{
		return array(
			'EWRcarta_pages' => array(
				'page_id'			=> array('type' => self::TYPE_UINT, 'autoIncrement' => true),
				'page_slug'			=> array('type' => self::TYPE_STRING, 'required' => true),
				'page_name'			=> array('type' => self::TYPE_STRING, 'required' => true),
				'page_date'			=> array('type' => self::TYPE_UINT, 'required' => true),
				'page_type'			=> array('type' => self::TYPE_STRING, 'required' => true, 'default' => 'bbcode',
					'allowedValues' => array('bbcode', 'html', 'phpfile')
				),
				'page_content'		=> array('type' => self::TYPE_STRING, 'required' => true),
				'page_parent'		=> array('type' => self::TYPE_UINT, 'required' => true, 'default' => 0),
				'page_index'		=> array('type' => self::TYPE_UINT, 'required' => true, 'default' => 0),
				'page_protect'		=> array('type' => self::TYPE_UINT, 'required' => true, 'default' => 0),
				'page_sidebar'		=> array('type' => self::TYPE_UINT, 'required' => true, 'default' => 1),
				'page_sublist'		=> array('type' => self::TYPE_UINT, 'required' => true, 'default' => 1),
				'page_likes'		=> array('type' => self::TYPE_UINT, 'required' => false),
				'page_like_users'	=> array('type' => self::TYPE_SERIALIZED, 'default' => 'a:0:{}'),
				'page_views'		=> array('type' => self::TYPE_UINT, 'required' => false),
				'page_groups'		=> array('type' => self::TYPE_STRING, 'required' => false, 'maxLength' => 255, 'default' => ''),
				'page_users'		=> array('type' => self::TYPE_STRING, 'required' => false, 'maxLength' => 255, 'default' => ''),
				'page_admins'		=> array('type' => self::TYPE_STRING, 'required' => false, 'maxLength' => 255, 'default' => ''),
				'thread_id'			=> array('type' => self::TYPE_UINT, 'required' => false, 'default' => '0'),
			)
		);
	}

	protected function _getExistingData($data)
	{
		if (!$pageID = $this->_getExistingPrimaryKey($data, 'page_id'))
		{
			return false;
		}

		return array('EWRcarta_pages' => $this->getModelFromCache('EWRcarta_Model_Pages')->getPageByID($pageID));
	}

	protected function _getUpdateCondition($tableName)
	{
		return 'page_id = ' . $this->_db->quote($this->getExisting('page_id'));
	}

	protected function _preSave()
	{
		$pageslug = $this->get('page_slug');
		$pageslug = strtolower(trim($pageslug));
		$pageslug = preg_replace('#[^-a-z0-9\s]#', '-', $pageslug);
		$pageslug = preg_replace('#^[-\s]+|[-\s]+$#', '', $pageslug);
		$pageslug = preg_replace('#[-\s]+#', '-', $pageslug);
		$this->set('page_slug', $pageslug);

		if ($this->get('page_type') != 'bbcode')
		{
			$this->set('page_protect', 1);
		}

		if ($this->isChanged('page_content'))
		{
			$this->set('page_date', XenForo_Application::$time);
		}
	}

	protected function _postSave()
	{
		$this->_indexForSearch();

		$attachmentHash = $this->getExtraData(self::DATA_ATTACHMENT_HASH);
		if ($attachmentHash)
		{
			$rows = $this->_db->update('xf_attachment', array(
				'content_type' => 'wiki',
				'content_id' => $this->get('page_id'),
				'temp_hash' => '',
				'unassociated' => 0
			), 'temp_hash = ' . $this->_db->quote($attachmentHash));
		}
	}

	protected function _postDelete()
	{
		$dataHandler = $this->getModelFromCache('XenForo_Model_Search')->getSearchDataHandler('wiki');
		$indexer = new XenForo_Search_Indexer();

		$dataHandler->deleteFromIndex($indexer, $this->getMergedData());
	}

	protected function _indexForSearch()
	{
		$dataHandler = $this->getModelFromCache('XenForo_Model_Search')->getSearchDataHandler('wiki');
		$indexer = new XenForo_Search_Indexer();

		if (!$this->getExisting('page_id'))
		{
			$dataHandler->insertIntoIndex($indexer, $this->getMergedData());
		}
		else if ($this->isChanged('page_name') || $this->isChanged('page_content'))
		{
			$dataHandler->updateIndex($indexer, $this->getMergedData(), array(
				'title' => $this->get('page_name'),
				'message' => $this->get('page_content')
			));
		}
	}
}