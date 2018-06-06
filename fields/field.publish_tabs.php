<?php

	Class fieldPublish_Tabs extends Field{


	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/

		public function __construct(){
			parent::__construct();

			$this->_name = __('Publish Tab');
			$this->_required = false;
			$this->set('hide', 'no');
		}

	/*-------------------------------------------------------------------------
		Setup:
	-------------------------------------------------------------------------*/

		public function createTable(){
			return Symphony::Database()
				->create('tbl_entries_data_' . $this->get('id'))
				->ifNotExists()
				->charset('utf8')
				->collate('utf8_unicode_ci')
				->fields([
					'id' => [
						'type' => 'int(11)',
						'auto' => true,
					],
					'entry_id' => 'int(11)',
					'value' => [
						'type' => 'double',
						'null' => true,
					],
				])
				->keys([
					'id' => 'primary',
					'entry_id' => 'key',
					'value' => 'key',
				])
				->execute()
				->success();
		}

	/*-------------------------------------------------------------------------
		Settings:
	-------------------------------------------------------------------------*/

		public function commit(){
			if(!parent::commit()) return false;

			$id = $this->get('id');

			if($id === false) return false;

			$fields = array();
			$fields['field_id'] = $id;

			return FieldManager::saveSettings($id, $fields);
		}

	/*-------------------------------------------------------------------------
		Publish:
	-------------------------------------------------------------------------*/

		public function displayPublishPanel(XMLElement &$wrapper, $data = null, $flagWithError = null, $fieldnamePrefix = null, $fieldnamePostfix = null, $entry_id = null){
			$wrapper->setValue($this->get('label'));
		}

		public function processRawFieldData($data, &$status, &$message = null, $simulate = false, $entry_id = null) {
			$status = self::__OK__;

			return array(
				'value' => ''
			);
		}

	/*-------------------------------------------------------------------------
		Output:
	-------------------------------------------------------------------------*/

		public function fetchIncludableElements() {
			return null;
		}

		public function appendFormattedElement(XMLElement &$wrapper, $data, $encode = false, $mode = null, $entry_id = null) {

		}

		public function prepareReadableValue($data, $entry_id = null, $truncate = false, $defaultValue = null) {
			return $this->prepareTableValue($data, null, $entry_id);
		}

		public function prepareTableValue($data, XMLElement $link = null, $entry_id = null) {
			// build this entry fully
			$entries = EntryManager::fetch($entry_id);

			if ($entries === false) return parent::prepareTableValue(null, $link, $entry_id);

			$entry = reset(EntryManager::fetch($entry_id));

			// get the first field inside this tab
			$field_id = Symphony::Database()
				->select(['id'])
				->from('tbl_fields')
				->where(['parent_section' => $this->get('parent_section')])
				->where(['sortorder' => ($this->get('sortorder') + 1)])
				->orderBy('sortorder')
				->limit(1)
				->execute()
				->variable('id');

			if ($field_id === null) return parent::prepareTableValue(null, $link, $entry_id);

			$field = FieldManager::fetch($field_id);

			// get the first field's value as a substitude for the tab's return value
			return $field->prepareTableValue($entry->getData($field_id), $link, $entry_id);
		}

	}
