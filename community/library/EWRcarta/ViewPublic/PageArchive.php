<?php

class EWRcarta_ViewPublic_PageArchive extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		$parserModel = XenForo_Model::create('EWRcarta_Model_Parser');

		if ($this->_params['history']['history_type'] == 'phpfile')
		{
			$this->_params['history']['page_content'] = $this->_params['history']['history_content'];
			$this->_params['history'] = $parserModel->parsePagePHP($this->_params['history']);
		}
		else
		{
			$bbCodeParser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));
			$bbCodeOptions = array(
				'stopLineBreakConversion' => ($this->_params['history']['history_type'] == 'html' ? true : false)
			);
			$this->_params['history']['HTML'] = new XenForo_BbCode_TextWrapper($this->_params['history']['history_content'], $bbCodeParser, $bbCodeOptions);
			$this->_params['history']['HTML'] = (string) $this->_params['history']['HTML'];

			if ($this->_params['history']['history_type'] == 'html')
			{
				$this->_params['history']['HTML'] = htmlspecialchars_decode($this->_params['history']['HTML']);
			}

			if ($this->_params['history']['history_type'] == 'bbcode')
			{
				$bbCodeParser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));
				$this->_params['history']['HTML'] = new XenForo_BbCode_TextWrapper($this->_params['history']['history_content'], $bbCodeParser);
				$this->_params['history']['HTML'] = (string) $this->_params['history']['HTML'];
			}
			else
			{
				$this->_params['history']['HTML'] = $this->_params['history']['history_content'];
			}

			$this->_params['history'] = $parserModel->parseContents($this->_params['history']);
			$this->_params['history'] = $parserModel->parseTemplates($this->_params['history']);
			$this->_params['history'] = $parserModel->parseAutolinks($this->_params['history']);
		}
	}
}