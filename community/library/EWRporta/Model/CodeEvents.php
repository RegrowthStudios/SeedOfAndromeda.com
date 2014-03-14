<?php

class EWRporta_Model_CodeEvents extends XenForo_Model
{
	public function getListenersByBlock($blockId)
	{
		$title = 'EWRblock_'.$blockId;

		return $this->_getDb()->fetchAll('
			SELECT *
				FROM xf_code_event_listener
			WHERE description LIKE ?
		', $title.'%');
	}

	public function deleteListenersByBlock($blockId)
	{
		$title = 'EWRblock_'.$blockId;

		$db = $this->_getDb();
		$db->delete('xf_code_event_listener', 'description LIKE ' . $db->quote($title.'%'));

		return;
	}

	public function importListenersXml(SimpleXMLElement $xml, $blockId)
	{
		$db = $this->_getDb();
		XenForo_Db::beginTransaction($db);

		$this->deleteListenersByBlock($blockId);

		$listeners = XenForo_Helper_DevelopmentXml::fixPhpBug50670($xml->listener);
		foreach ($listeners AS $event)
		{
			$eventId = (string)$event['event_id'];

			$dw = XenForo_DataWriter::create('XenForo_DataWriter_CodeEventListener');
			$dw->setOption(XenForo_DataWriter_CodeEventListener::OPTION_REBUILD_CACHE, false);
			$dw->bulkSet(array(
				'event_id' => (string)$event['event_id'],
				'execute_order' => (string)$event['execute_order'],
				'callback_class' => (string)$event['callback_class'],
				'callback_method' => (string)$event['callback_method'],
				'active' => (string)$event['active'],
				'description' => (string)$event['description']
			));
			$dw->save();
		}

		$this->getModelFromCache('XenForo_Model_CodeEvent')->rebuildEventListenerCache();

		XenForo_Db::commit($db);

		return;
	}

	public function appendListenerXml(DOMElement $rootNode, $blockId)
	{
		$document = $rootNode->ownerDocument;

		$listeners = $this->getListenersByBlock($blockId);
		foreach ($listeners AS $listener)
		{
			$listenerNode = $document->createElement('listener');
			$listenerNode->setAttribute('description', $listener['description']);
			$listenerNode->setAttribute('event_id', $listener['event_id']);
			$listenerNode->setAttribute('execute_order', $listener['execute_order']);
			$listenerNode->setAttribute('callback_class', $listener['callback_class']);
			$listenerNode->setAttribute('callback_method', $listener['callback_method']);
			$listenerNode->setAttribute('active', $listener['active']);

			$rootNode->appendChild($listenerNode);
		}
	}
}