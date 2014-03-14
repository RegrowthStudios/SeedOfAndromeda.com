<?php

class EWRporta_ViewAdmin_ExportBlock extends XenForo_ViewAdmin_Base
{
	public function renderXml()
	{
		$this->setDownloadFileName($this->_params['block']['block_id'] . '.xml');
		return $this->_params['xml']->saveXml();
	}
}