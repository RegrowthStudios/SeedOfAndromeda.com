<?php

/**
 * Controller for handling actions on threads.
 *
 * @package XenForo_Thread
 */
class XenPlaza_XPThreadEditingReason_ControllerPublic_Thread extends XFCP_XenPlaza_XPThreadEditingReason_ControllerPublic_Thread
{
	
	public function actionIndex()
	{
		//$this->getModelFromCache('XenForo_Model_User')->getUserById();		
		$result = parent::actionIndex();
		if(isset($result->params['posts'])){
			foreach($result->params['posts'] AS &$post){
				if($post['XP_editor']){
					$post['XP_Editor'] = $this->getModelFromCache('XenForo_Model_User')->getUserById($post['XP_editor']);
				}
			}
		}
		//print_r($result);die;
		return $result;
	}
	public function actionShowPosts()
	{
		$result = parent::actionShowPosts();
		if(isset($result->params['posts'])){
			foreach($result->params['posts'] AS &$post){
				if($post['XP_editor']){
					$post['XP_Editor'] = $this->getModelFromCache('XenForo_Model_User')->getUserById($post['XP_editor']);
				}
			}
		}
		
		return $result;
	}
}