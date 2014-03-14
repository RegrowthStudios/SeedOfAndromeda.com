<?php

class XenResource_ViewPublic_Resource_QuickPreview extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		$previewLength = XenForo_Application::get('options')->discussionPreviewLength;

		if ($previewLength && !empty($this->_params['update']))
		{
			$formatter = XenForo_BbCode_Formatter_Base::create('XenForo_BbCode_Formatter_Text');
			$parser = XenForo_BbCode_Parser::create($formatter);

			$this->_params['update']['messageParsed'] = $parser->render($this->_params['update']['message']);
		}
	}
}