<?php

class EWRcarta_ViewPublic_PageView extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		$options = XenForo_Application::get('options');
		$parserModel = XenForo_Model::create('EWRcarta_Model_Parser');

		if ($this->_params['page']['page_type'] == 'phpfile')
		{
			$this->_params['page'] = $parserModel->parsePagePHP($this->_params['page']);
		}
		else
		{
			$cacheModel = XenForo_Model::create('EWRcarta_Model_Cache');
			$cache = $cacheModel->getCache($this->_params['page']);

			if ($this->_params['page']['page_date'] >= $cache['cache_date'] || strtotime($options->EWRcarta_cache, $cache['cache_date']) < XenForo_Application::$time)
			{
				$bbCodeParser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));
				$bbCodeOptions = array(
					'viewAttachments' => true,
					'stopLineBreakConversion' => ($this->_params['page']['page_type'] == 'html' ? true : false),
					'attachments' => $this->_params['page']['attachments'],
				);
				$this->_params['page']['HTML'] = new XenForo_BbCode_TextWrapper($this->_params['page']['page_content'], $bbCodeParser, $bbCodeOptions);
				$this->_params['page']['HTML'] = (string) $this->_params['page']['HTML'];

				if ($this->_params['page']['page_type'] == 'html')
				{
					$this->_params['page']['HTML'] = htmlspecialchars_decode($this->_params['page']['HTML']);
				}

				$this->_params['page'] = $parserModel->parseContents($this->_params['page']);
				$this->_params['page'] = $parserModel->parseTemplates($this->_params['page']);
				$this->_params['page'] = $parserModel->parseAutolinks($this->_params['page']);

				$dw = XenForo_DataWriter::create('EWRcarta_DataWriter_Cache', XenForo_DataWriter::ERROR_SILENT);
				if ($cache)
				{
					$dw->setExistingData($cache);
				}
				$dw->bulkSet(array(
					'page_id' => $this->_params['page']['page_id'],
					'cache_content' => $this->_params['page']['HTML'],
				));
				$dw->save();
			}
			else
			{
				$this->_params['page']['HTML'] = $cache['cache_content'];
				$this->_params['page']['cache'] = $cache['cache_date'];
			}
		}
	}
}