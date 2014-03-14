<?php

class EWRcarta_ViewPublic_PageEdit extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		$parserModel = XenForo_Model::create('EWRcarta_Model_Parser');

		if (!empty($this->_params['input']['page_type']))
		{
			if ($this->_params['input']['page_type'] == 'phpfile')
			{
				$this->_params['input'] = $parserModel->parsePagePHP($this->_params['input']);
			}
			else
			{
				$bbCodeParser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));
				$bbCodeOptions = array(
					'stopLineBreakConversion' => ($this->_params['input']['page_type'] == 'html' ? true : false)
				);
				$this->_params['input']['HTML'] = new XenForo_BbCode_TextWrapper($this->_params['input']['page_content'], $bbCodeParser, $bbCodeOptions);
				$this->_params['input']['HTML'] = (string) $this->_params['input']['HTML'];

				if ($this->_params['input']['page_type'] == 'html')
				{
					$this->_params['input']['HTML'] = htmlspecialchars_decode($this->_params['input']['HTML']);
				}

				$this->_params['input'] = $parserModel->parseContents($this->_params['input']);
				$this->_params['input'] = $parserModel->parseTemplates($this->_params['input']);
				$this->_params['input'] = $parserModel->parseAutolinks($this->_params['input']);
			}
		}

		$disable = $this->_params['page']['page_type'] == 'html' ? true : false;
		$disable = XenForo_Application::get('options')->EWRcarta_wysiwyg ? $disable : true;

		$this->_params['editorTemplate'] = XenForo_ViewPublic_Helper_Editor::getEditorTemplate(
			$this, 'page_content', $this->_params['page']['page_content'], array('disable' => $disable)
		);
	}
}