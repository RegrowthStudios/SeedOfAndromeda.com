<?php

class EWRcarta_Model_Post extends XFCP_EWRcarta_Model_Post
{
	public function getQuoteTextForPost(array $post, $maxQuoteDepth = 0)
	{
		$response = parent::getQuoteTextForPost($post, $maxQuoteDepth);
		$response = str_ireplace('[wiki=full]', '[wiki]', $response);
		return $response;
	}
}