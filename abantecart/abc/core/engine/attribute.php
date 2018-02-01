<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2017 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/
namespace abc\core\engine;
use abc\core\helper\AHelperUtils;
use abc\lib\AFile;

if (!class_exists('abc\ABC')) {
	header('Location: static_pages/?forbidden='.basename(__FILE__));
}

/**
 * Class to handle access to global attributes
 *
 * @property \abc\lib\ALanguageManager $language
 * @property \abc\lib\ADB $db
 * @property \abc\core\cache\ACache $cache
 * @property \abc\lib\AConfig $config
 * @property \abc\lib\ARequest $request
 * @property \abc\lib\ASession $session
 * @property \abc\core\engine\ALoader $load
 */
class AAttribute{
	protected $registry;
	private $attributes = array ();
	private $attribute_types = array ();
	/**
	 * @var array of core attribute types controllers
	 */
	private $core_attribute_types_controllers = array (
			'responses/catalog/attribute/getProductOptionSubform',
			'responses/catalog/attribute/getDownloadAttributeSubform'
	);

	/**
	 * @param string $attribute_type
	 * @param int $language_id
	 */
	public function __construct($attribute_type = '', $language_id = 0){
		$this->registry = Registry::getInstance();
		$this->errors = array ();
		$this->_load_attribute_types();
		//Preload the data with attributes for given $attribute type
		if ($attribute_type) {
			$this->_load_attributes($this->getAttributeTypeID($attribute_type), $language_id);
		}
	}

	/**
	 * @param  $key - key to load data from registry
	 * @return mixed  - data from registry
	 */
	public function __get($key){
		return $this->registry->get($key);
	}

	/**
	 * @param  string $key - key to save data in registry
	 * @param  mixed $value - key to save data in registry
	 * @void
	 */
	public function __set($key, $value){
		$this->registry->set($key, $value);
	}

	/**
	 * @param int $language_id
	 * @return bool
	 */
	private function _load_attribute_types($language_id = 0){
		//Load attribute types from DB or cache.
		if (!$language_id) {
			$language_id = (int)$this->config->get('storefront_language_id');
		}
		$store_id = (int)$this->config->get('config_store_id');
		$cache_key = 'attribute.types.store_' . $store_id . '_lang_' . (int)$language_id;
		$attribute_types = $this->cache->pull($cache_key);
		if ($attribute_types !== false) {
			$this->attribute_types = $attribute_types;
			return false;
		}
		$query = $this->db->query("SELECT at.*, gatd.type_name
									FROM " . $this->db->table("global_attributes_types") . " at
									LEFT JOIN " . $this->db->table("global_attributes_type_descriptions") . " gatd
										ON (gatd.attribute_type_id = at.attribute_type_id AND gatd.language_id = " . (int)$language_id . ")
									WHERE at.status = 1 order by at.sort_order");
		if (!$query->num_rows) {
			return false;
		}

		$this->cache->push($cache_key, $query->rows);

		$this->attribute_types = $query->rows;
		return true;
	}

	/**
	 * load all the attributes for specified type
	 * @param $attribute_type_id
	 * @param int $language_id
	 * @return array|bool
	 */
	private function _load_attributes($attribute_type_id, $language_id = 0){
		//Load attributes from DB or cache. If load from DB, cache.
		// group attribute and sort by attribute_group_id (if any) and sort by attribute inside the group.
		$this->attributes = array ();
		if (!$language_id) {
			$language_id = $this->config->get('storefront_language_id');
		}
		$store_id = (int)$this->config->get('config_store_id');

		$cache_key = 'attributes.' . $attribute_type_id;
		$cache_key = preg_replace('/[^a-zA-Z0-9\.]/', '',
						$cache_key) . '.store_' . $store_id . '_lang_' . (int)$language_id;
		$attributes = $this->cache->pull($cache_key);
		if ($attributes !== false) {
			$this->attributes = $attributes;
			return false;
		}

		$query = $this->db->query(" SELECT ga.*, gad.name
									FROM " . $this->db->table("global_attributes") . " ga
									LEFT JOIN " . $this->db->table("global_attributes_descriptions") . " gad
										ON ( ga.attribute_id = gad.attribute_id AND gad.language_id = '" . (int)$language_id . "' )
									WHERE ga.attribute_type_id = '" . $this->db->escape($attribute_type_id) . "' AND ga.status = 1
									ORDER BY ga.sort_order");
		foreach ($query->rows as $row) {
			$this->attributes[$row['attribute_id']] = $row;
		}

		$this->cache->push($cache_key, $this->attributes);
		return true;
	}

	/**
	 * @return array
	 */
	public function getAttributes(){
		return $this->attributes;
	}

	/**
	 * Get details about given group for attributes
	 * @param $group_id
	 * @param int $language_id
	 * @return array
	 */
	public function getActiveAttributeGroup($group_id, $language_id = 0){

		if (!$language_id) {
			$language_id = $this->config->get('storefront_language_id');
		}

		$query = $this->db->query("SELECT gag.*, gagd.name
									FROM " . $this->db->table("global_attributes_groups") . " gag
									LEFT JOIN " . $this->db->table("global_attributes_groups_descriptions") . " gagd
										ON ( gag.attribute_group_id = gagd.attribute_group_id AND gagd.language_id = '" . (int)$language_id . "' )
									WHERE gag.attribute_group_id = '" . $this->db->escape($group_id) . "' AND gag.status = 1
									ORDER BY gag.sort_order");

		if ($query->num_rows) {
			return $query->row;
		} else {
			return array ();
		}
	}

	/**
	 * Get array of all available attribute types
	 * @return array
	 */
	public function getAttributeTypes(){
		return $this->attribute_types;
	}

	/**
	 * Get array of all core attribute types controllers (for recognizing of core attribute types)
	 * @return array
	 */
	public function getCoreAttributeTypesControllers(){
		return $this->core_attribute_types_controllers;
	}

	/**
	 * @param string $type
	 * @return null | int
	 * Get attribute type id based on attribute type_key
	 */
	public function getAttributeTypeID($type){
		foreach ($this->attribute_types as $attribute_type) {
			if ($attribute_type['type_key'] == $type) {
				return $attribute_type['attribute_type_id'];
			}
		}
		return null;
	}

	/**
	 * @param string $type
	 * @return array
	 * Get attribute type data based on attribute type_key
	 */
	public function getAttributeTypeInfo($type){
		foreach ($this->attribute_types as $attribute_type) {
			if ($attribute_type['type_key'] == $type) {
				return $attribute_type;
			}
		}
		return array ();
	}

	/**
	 * @param int $type_id
	 * @return array
	 * Get attribute type data based on attribute type id
	 */
	public function getAttributeTypeInfoById($type_id){
		foreach ($this->attribute_types as $attribute_type) {
			if ($attribute_type['attribute_type_id'] == $type_id) {
				return $attribute_type;
			}
		}
		return array ();
	}

	/**
	 * @param  $attribute_id
	 * Returns total count of children for the attribute. No children returns 0
	 */
	public function totalChildren($attribute_id){
		$sql = "SELECT count(*) as total_count
				FROM " . $this->db->table("global_attributes") . "
				WHERE attribute_parent_id = '" . (int)$attribute_id . "'";
		$attribute_data = $this->db->query($sql);
		return $attribute_data->rows[0]['total_count'];
	}

	/**
	 * load all the attributes for specified type
	 * @param $attribute_type
	 * @param int $language_id - Language id. default 0 (english)
	 * @param int $attribute_parent_id - Parent attribute ID if any. Default 0 (parent)
	 * @return array
	 */
	public function getAttributesByType($attribute_type, $language_id = 0, $attribute_parent_id = 0){
		if (empty($this->attributes)) {
			$this->_load_attributes($this->getAttributeTypeID($attribute_type), $language_id);
		}
		if ($attribute_parent_id == 0) {
			return $this->attributes;
		} else {
			$children = array ();
			foreach ($this->attributes as $attribute) {
				if ($attribute['attribute_parent_id'] == $attribute_parent_id) {
					$children[] = $attribute;
				}
			}
			return $children;
		}
	}

	/**
	 * get attribute connected to option
	 *
	 * @param $option_id
	 * @return null
	 */
	public function getAttributeByProductOptionId($option_id){
		$sql = "SELECT attribute_id
				FROM " . $this->db->table("product_options") . "
				WHERE product_option_id = '" . (int)$option_id . "' AND attribute_id != 0";
		$attribute_id = $this->db->query($sql);
		if ($attribute_id->num_rows) {
			return $this->getAttribute($attribute_id->row['attribute_id']);
		} else {
			return null;
		}
	}

	/**
	 * @param $attribute_id - load attribute with id=$attribute_id
	 * @return array
	 */
	public function getAttribute($attribute_id){
		if (empty($this->attributes)) {
			return array ();
		}

		foreach ($this->attributes as $attribute) {
			if ($attribute['attribute_id'] == $attribute_id) {
				if (AHelperUtils::has_value($attribute['settings'])) {
					$attribute['settings'] = unserialize($attribute['settings']);
				}
				return $attribute;
			}
		}
		return array ();
	}

	/**
	 * @param $attribute_id - load all the attribute values and descriptions for specified attribute id
	 * @param int $language_id - Language id. default 0 (english)
	 * @return array
	 */
	public function getAttributeValues($attribute_id, $language_id = 0){
		if (!(int)$language_id) {
			$language_id = $this->language->getLanguageID();
		}
		$store_id = (int)$this->config->get('config_store_id');
		//get attribute values
		$cache_key = 'attribute.values.' . $attribute_id;
		$cache_key = preg_replace('/[^a-zA-Z0-9\.]/', '', $cache_key) . '.store_' . $store_id . '_lang_' . $language_id;
		$attribute_vals = $this->cache->pull($cache_key);
		if ($attribute_vals !== false) {
			return $attribute_vals;
		}

		$query = $this->db->query("
			SELECT gav.sort_order, gav.attribute_value_id, gavd.*
			FROM " . $this->db->table("global_attributes_values") . " gav
			LEFT JOIN " . $this->db->table("global_attributes_value_descriptions") . " gavd
				ON ( gav.attribute_value_id = gavd.attribute_value_id AND gavd.language_id = '" . (int)$language_id . "' )
			WHERE gav.attribute_id = '" . $this->db->escape($attribute_id) . "'
			order by gav.sort_order"
		);
		$attribute_vals = $query->rows;
		$this->cache->push($cache_key, $attribute_vals);
		return $attribute_vals;
	}

	/**
	 * method for validation of data based on global attributes requirements
	 * @param array $data - usually it's a $_POST
	 * @return array - array with error text for each of invalid field data
	 */
	public function validateAttributeData($data = array ()){
		$errors = array ();
		// load language for file upload text errors
		$this->language->load('catalog/attribute');
		foreach ($this->attributes as $attribute_info) {

			// for multivalue required fields
			if (in_array($attribute_info['element_type'], HtmlElementFactory::getMultivalueElements())
					&& !sizeof($data[$attribute_info['attribute_id']])
					&& $attribute_info['required'] == '1'
			) {
				$errors[$attribute_info['attribute_id']] = $this->language->get('entry_required') . ' ' . $attribute_info['name'];
			}
			// for required string values
			if ($attribute_info['required'] == '1' && !in_array($attribute_info['element_type'], array ('K', 'U'))) {
				if (!is_array($data[$attribute_info['attribute_id']])) {
					$data[$attribute_info['attribute_id']] = trim($data[$attribute_info['attribute_id']]);
					if ($data[$attribute_info['attribute_id']] == '') {    //if empty string!
						$errors[$attribute_info['attribute_id']] = $this->language->get('entry_required') . ' ' . $attribute_info['name'];
					}
				} else {
					if (!$data[$attribute_info['attribute_id']]) {    // if empty array
						$errors[$attribute_info['attribute_id']] = $this->language->get('entry_required') . ' ' . $attribute_info['name'];
					}
				}
			}
			// check by regexp
			if (AHelperUtils::has_value($attribute_info['regexp_pattern'])) {
				if (!is_array($data[$attribute_info['attribute_id']])) { //for string value
					if (!preg_match($attribute_info['regexp_pattern'], $data[$attribute_info['attribute_id']])) {
						$errors[$attribute_info['attribute_id']] .= ' ' . $attribute_info['error_text'];
					}
				} else { // for array's values
					foreach ($data[$attribute_info['attribute_id']] as $dd) {
						if (!preg_match($attribute_info['regexp_pattern'], $dd)) {
							$errors[$attribute_info['attribute_id']] .= ' ' . $attribute_info['error_text'];
							break;
						}
					}
				}
			}

			//for captcha
			if ($attribute_info['element_type'] == 'K'
					&&
					(!isset($this->session->data['captcha']) || $this->session->data['captcha'] != $data[$attribute_info['attribute_id']])
			) {
				$errors[$attribute_info['attribute_id']] = $this->language->get('error_captcha');
			}
			// for file
			if ($attribute_info['element_type'] == 'U'
					&&
					($this->request->files[$attribute_info['attribute_id']]['tmp_name'] || $attribute_info['required'] == '1')
			) {
				$fm = new AFile();
				$file_path_info = $fm->getUploadFilePath(
						$data['settings']['directory'],
						$this->request->files[$attribute_info['attribute_id']]['name']
				);
				$file_data = array (
						'name'     => $file_path_info['name'],
						'path'     => $file_path_info['path'],
						'type'     => $this->request->files[$attribute_info['attribute_id']]['type'],
						'tmp_name' => $this->request->files[$attribute_info['attribute_id']]['tmp_name'],
						'error'    => $this->request->files[$attribute_info['attribute_id']]['error'],
						'size'     => $this->request->files[$attribute_info['attribute_id']]['size'],
				);

				$file_errors = $fm->validateFileOption($attribute_info['settings'], $file_data);

				if ($file_errors) {
					$errors[$attribute_info['attribute_id']] .= implode(' ', $file_errors);
				}
			}
		}
		return $errors;
	}

}