<?php

class EWRcarta_ViewPublic_PageNull extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		$this->_response->setHttpResponseCode(404);
	}
}