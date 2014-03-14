<?php

class EWRporta_BbCode_Formatter extends XFCP_EWRporta_BbCode_Formatter
{
    protected $_tags;

    public function getTags()
    {
        $this->_tags = parent::getTags();

        $this->_tags['prbreak'] = array(
			'hasOption' => false,
			'callback' => array($this, 'renderBreak'),
        );

        $this->_tags['prebreak'] = array(
			'hasOption' => false,
			'callback' => array($this, 'renderBreak'),
        );

        return $this->_tags;
    }

	public function renderBreak(array $tag, array $rendererStates)
	{
		return '';
	}
}