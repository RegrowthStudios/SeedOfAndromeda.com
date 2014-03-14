<?php

class EWRcarta_SearchHandler_Wiki extends XenForo_Search_DataHandler_Abstract
{
	protected $_pageModel;

	protected function _insertIntoIndex(XenForo_Search_Indexer $indexer, array $data, array $parentData = null)
	{
		$metaData = array();

		$indexer->insertIntoIndex(
			'wiki', $data['page_id'], $data['page_name'], $data['page_content'], $data['page_date'], 0, 0, $metaData
		);
	}

	protected function _updateIndex(XenForo_Search_Indexer $indexer, array $data, array $fieldUpdates)
	{
		$indexer->updateIndex('wiki', $data['page_id'], $fieldUpdates);
	}

	protected function _deleteFromIndex(XenForo_Search_Indexer $indexer, array $dataList)
	{
		$pageIDs = array();
		foreach ($dataList as $data)
		{
			$pageIDs[] = $data['page_id'];
		}

		$indexer->deleteFromIndex('wiki', $pageIDs);
	}

	public function rebuildIndex(XenForo_Search_Indexer $indexer, $lastId, $batchSize)
	{
		$pageIDs = $this->_getPageModel()->getPageIDsInRange($lastId, $batchSize);
		if (!$pageIDs)
		{
			return false;
		}

		$this->quickIndex($indexer, $pageIDs);

		return max($pageIDs);
	}

	public function quickIndex(XenForo_Search_Indexer $indexer, array $contentIds)
	{
		$pages = $this->_getPageModel()->getPagesByIDs($contentIds);
		$pageIDs = array();

		foreach ($pages as $pageID => $page)
		{
			$pageIDs[] = $pageID;
			$this->insertIntoIndex($indexer, $page);
		}

		return $pageIDs;
	}

	public function getDataForResults(array $ids, array $viewingUser, array $resultsGrouped)
	{
		return $this->_getPageModel()->getPagesByIDs($ids);
	}

	public function canViewResult(array $result, array $viewingUser)
	{
		return true;
	}

	public function prepareResult(array $result, array $viewingUser)
	{
		return $result;
	}

	public function getResultDate(array $result)
	{
		return $result['page_date'];
	}

	public function renderResult(XenForo_View $view, array $result, array $search)
	{
		return $view->createTemplateObject('EWRcarta_Search_Result', array(
			'wiki' => $result,
			'search' => $search,
		));
	}

	public function getSearchContentTypes()
	{
		return array('wiki');
	}

	public function getSearchFormControllerResponse(XenForo_ControllerPublic_Abstract $controller, XenForo_Input $input, array $viewParams)
	{
		return $controller->responseView('EWRcarta_ViewPublic_Search', 'EWRcarta_Search_Form', $viewParams);
	}

	protected function _getPageModel()
	{
		if (!$this->_pageModel)
		{
			$this->_pageModel = XenForo_Model::create('EWRcarta_Model_Pages');
		}

		return $this->_pageModel;
	}
}