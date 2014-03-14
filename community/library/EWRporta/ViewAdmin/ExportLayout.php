<?php

class EWRporta_ViewAdmin_ExportLayout extends XenForo_ViewAdmin_Base
{
	public function renderXml()
	{
		$this->setDownloadFileName($this->_params['layout']['layout_id'] . '.xml');
		return $this->_params['xml']->saveXml();
	}
}