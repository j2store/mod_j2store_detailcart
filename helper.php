<?php
/*
 ------------------------------------------------------------------------
# mod_j2store_detailcart  - J2Store Detail cart
# ------------------------------------------------------------------------
# author    ThemeParrot - ThemeParrot http://www.ThemeParrot.com
# copyright Copyright (C) 2014 ThemeParrot.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://ThemeParrot.com
# Based on Latest Articles module of Joomla
-------------------------------------------------------------------------
*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
require_once JPATH_ADMINISTRATOR .'/components/com_j2store/helpers/j2store.php';

jimport( 'joomla.application.module.helper' );
class ModJ2StoreDetailCartHelper{
	public static $_order;
	public static $_taxes;
	public static $_shipping;
	public static $_country_id;
	public static $_zone_id;
	public static $_postcode;
	public static $_onDisplayCartItem;
	public static $_before_display_cart;
	public static $_after_display_cart;
	public static function getCartItems($params){
		$no_cache = $params->get('cache',0);
		if($no_cache==0){
			$cache = JFactory::getCache();
			$cache->clean('com_j2store');
			$cache->clean('mod_j2store_detailcart');
		}

		F0FModel::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/models/');
		$items = F0FModel::getTmpInstance('Carts','J2StoreModel')->getItems();
		//plugin trigger
		self::$_before_display_cart = '';
		$before_results = J2Store::plugin()->event('BeforeDisplayCart', array( $items) );
		foreach ($before_results  as $result) {
			self::$_before_display_cart .= $result;
		}
		//trigger plugin events
		$i=0;
		$onDisplayCartItem = array();
		foreach( $items as $item)
		{
			ob_start();
			J2Store::plugin()->event('DisplayCartItem', array( $i, $item ) );
			$cartItemContents = ob_get_contents();
			ob_end_clean();
			if (!empty($cartItemContents))
			{
				$onDisplayCartItem[$i] = $cartItemContents;
			}
			$i++;
		}

		self::$_onDisplayCartItem =  $onDisplayCartItem;
		$order = F0FModel::getTmpInstance('Orders', 'J2StoreModel')->populateOrder($items)->getOrder();
		$order->validate_order_stock();
		self::$_order = $order;
		$items = $order->getItems();
		foreach($items as $item) {
			if(isset($item->orderitemattributes) && count($item->orderitemattributes)) {
				foreach($item->orderitemattributes as &$attribute) {
					if($attribute->orderitemattribute_type == 'file') {
						unset($table);
						$table = F0FTable::getInstance('Upload', 'J2StoreTable');
						if($table->load(array('mangled_name'=>$attribute->orderitemattribute_value))) {
							$attribute->orderitemattribute_value = $table->original_name;
						}
					}
				}
			}
		}
		self::$_taxes = $order->getOrderTaxrates();
		self::$_after_display_cart = '';
		return $items;
	}


	/**
	 * Method to set Shipping data
	 * when estimate shipping calculation done
	 *
	 */
	public static function getShippingData(){
		$app = JFactory::getApplication();
		$session = JFactory::getSession();
		$store_profile = J2Store::storeProfile();
		$country_id = $app->input->getInt('country_id');
		if (isset($country_id)) {
			$session->set('billing_country_id', $country_id, 'j2store');
			$session->set('shipping_country_id', $country_id, 'j2store');
		} elseif ($session->has('shipping_country_id', 'j2store')) {
			$country_id = $session->get('shipping_country_id', '', 'j2store');
		} else {
			$country_id = $store_profile->get('country_id');
		}
		self::$_country_id = $country_id;

		$zone_id = $app->input->getInt('zone_id');
		if (isset($zone_id)) {
			$session->set('billing_zone_id', $zone_id, 'j2store');
			$session->set('shipping_zone_id', $zone_id, 'j2store');
		} elseif($session->has('shipping_zone_id', 'j2store')) {
			$zone_id = $session->get('shipping_zone_id', '', 'j2store');
		} else {
			$zone_id = $store_profile->get('zone_id');
		}
		self::$_zone_id = $zone_id;
		//check incase Shipping Postcode not set in the session
		if($session->has('shipping_postcode','j2store')){
			$postcode = $session->get('shipping_postcode','', 'j2store');
		}else{
			//get the postcode id from the store
			$postcode =$store_profile->get('postcode');
		}
		self::$_postcode = $postcode;
	}

	/**
	 * Ajax method to update detail cart module
	 * after item added to cart
	 */
	public static function getUpdatedDetailcartAjax(){

		J2Store::utilities()->nocache();
		//initialise system objects
		$app = JFactory::getApplication();
		$document	= JFactory::getDocument();
		$db = JFactory::getDbo();
		$language = JFactory::getLanguage()->getTag();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__modules')->where('module='.$db->q('mod_j2store_detailcart'))->where('published=1')
		->where('language='.$db->q($language));
		$db->setQuery($query);
		$modules = $db->loadObjectList();
		if(count($modules) < 1) {
			$query = $db->getQuery(true);
			$query->select('*')->from('#__modules')->where('module='.$db->q('mod_j2store_detailcart'))->where('published=1')
			->where('language="*" OR language="en-GB"');
			$db->setQuery($query);
			$modules = $db->loadObjectList();
		}
		$json = array();
		if (count($modules) < 1)
		{
			$json['response'] = ' ';
		} else {
			foreach($modules as $module) {
				$app->setUserState( 'mod_j2store_detailcart.isAjax', '1' );
				$json['response'][$module->id] = JModuleHelper::renderModule($module);
			}
			echo json_encode($json);
			$app->close();
		}
		$app->close();
	}


	/**
	 * Ajax method to remove the cartitem
	 * @input int cartitem_id
	 * @return response
	 */
	public static function removeCartItemAjax() {
		J2Store::utilities()->clear_cache();
		J2Store::utilities()->nocache();
		$app = JFactory::getApplication();
		$json = array();
		$json['status'] = false;
		$cartitem_id = $app->input->get ('cartitem_id',0);
		$model = F0FModel::getTmpInstance('Carts' ,'J2StoreModel');
		$app = JFactory::getApplication();
		$app->set('cartitem_id',$cartitem_id);
		$model->setInput(array('cartitem_id'=>$cartitem_id));
		if($model->deleteItem()) {
			$json['msg'] = JText::_('J2STORE_CART_UPDATED_SUCCESSFULLY');
			$json['status'] =true;
		}else {
			$json['msg']= $model->getError();
		}
		echo json_encode($json);
		$app->close();
	}
}
