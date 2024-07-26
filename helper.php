<?php
/**
------------------------------------------------------------------------
 * mod_j2store_detailcart - J2Store Detail cart
 * ------------------------------------------------------------------------
 * author    Gopi  http://www.ThemeParrot.com
 * copyright  (C) 2023 ThemeParrot.com. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://ThemeParrot.com
 * Based on Latest Articles module of Joomla
-------------------------------------------------------------------------
 */

// no direct access
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

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
	public static $_ondisplay_cartitem;
	public static $_before_display_cart;
	public static $_after_display_cart;

	/**
	 *  Retrieves and processes cart items based on provided parameters.
	 *
	 *  This function fetches the current cart items, optionally clears the cache,
	 *  triggers plugin events for displaying the cart and cart items, and processes
	 *  order and item attributes. The function returns the processed cart items.
	 *
	 * @param object $params  An object containing parameters for the function.
	 *                        - 'cache': If set to 0, cache is cleared before fetching items.
	 *
	 * @return array
	 *
	 * @since version
	 */
	public static function getCartItems($params){
        $fof_helper = J2Store::fof();
		$no_cache = $params->get('cache',0);
		if(!$no_cache){
			$cache = Factory::getContainer()->get(CacheControllerFactoryInterface::class)->createCacheController();
			$cache->clean('com_j2store');
			$cache->clean('mod_j2store_detailcart');
		}
		J2Store::platform()->addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/models/');
		$items = $fof_helper->getModel('Carts','J2StoreModel')->getItems();
		//plugin trigger
		self::$_before_display_cart = '';
		$before_results = J2Store::plugin()->event('BeforeDisplayCart', [ $items] );
		foreach ($before_results  as $result) {
			self::$_before_display_cart .= $result;
		}
		//trigger plugin events
		$i=0;
		$ondisplay_cartitem = [];
		foreach( $items as $item)
		{
			ob_start();
			J2Store::plugin()->event('DisplayCartItem', [ $i, $item ] );
			$cart_item_contents = ob_get_contents();
			ob_end_clean();
			if (!empty($cart_item_contents))
			{
				$ondisplay_cartitem[$i] = $cart_item_contents;
			}
			$i++;
		}

		self::$_ondisplay_cartitem =  $ondisplay_cartitem;
		$order = $fof_helper->getModel('Orders', 'J2StoreModel')->populateOrder($items)->getOrder();
		$order->validate_order_stock();
		self::$_order = $order;
		$items = $order->getItems();
		foreach($items as $item) {
			if(isset($item->orderitemattributes) && count($item->orderitemattributes)) {
				foreach($item->orderitemattributes as &$attribute) {
					if($attribute->orderitemattribute_type == 'file') {
						unset($table);
						$table = $fof_helper->loadTable('Upload', 'J2StoreTable');
						if($table->load(['mangled_name'=>$attribute->orderitemattribute_value])) {
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
	 * Retrieves and processes the shipping data including country and zone information.
	 *
	 *  This function fetches the country ID and zone ID from the user input or session,
	 *  updates the session accordingly, and sets the class properties with the retrieved values.
	 *  Additionally, it checks and sets the shipping postcode if it is not already set in the session.
	 *
	 * @since version
	 */
	public static function getShippingData(){
		$app = J2Store::platform()->application();
		$session = $app->getSession();
		$store_profile = J2Store::storeProfile();
		$country_id = $app->input->getInt('country_id');
		if ($country_id) {
			$session->set('billing_country_id', $country_id, 'j2store');
			$session->set('shipping_country_id', $country_id, 'j2store');
		}else {
			$country_id = $session->has('shipping_country_id', 'j2store')
				? $session->get('shipping_country_id', '', 'j2store')
				: $store_profile->get('country_id');
		}
		self::$_country_id = $country_id;

		$zone_id = $app->input->getInt('zone_id');
		if ($zone_id)
		{
			$session->set('billing_zone_id', $zone_id, 'j2store');
		} else {
			$zone_id = $session->has('shipping_zone_id', 'j2store')
				? $session->get('shipping_zone_id', '', 'j2store')
				: $store_profile->get('zone_id' );
		}

		self::$_zone_id = $zone_id;
		//check incase Shipping Postcode not set in the session
		self::$_postcode = $session->has('shipping_postcode','j2store') ? $session->get('shipping_postcode','', 'j2store') : $store_profile->get('postcode');
	}

	/**
	 * Retrieves and updates the detailed cart information via AJAX.
	 *
	 * This function disables caching, fetches the `mod_j2store_detailcart` modules that are published
	 * and match the current language, and renders them. If no modules match the current language,
	 * it attempts to find modules with a language set to "*" (all languages) or "en-GB".
	 * The rendered module content is returned as a JSON response.
	 *
	 * @since version
	 */
	public static function getUpdatedDetailcartAjax(){

		J2Store::utilities()->nocache();
		//initialise system objects
		$app = J2Store::platform()->application();
		$db     = Factory::getContainer()->get( DatabaseInterface::class );
		$query = $db->getQuery(true);
		$query->select('*')->from('#__modules')->where('module='.$db->q('mod_j2store_detailcart'))->where('published=1')
		->where('language='.$db->q($app->getLanguage()->getTag()));
		$db->setQuery($query);
		$modules = $db->loadObjectList();
		if(count($modules) < 1) {
			$query = $db->getQuery(true);
			$query->select('*')->from('#__modules')->where('module='.$db->q('mod_j2store_detailcart'))->where('published=1')
			->where('language="*" OR language="en-GB"');
			$db->setQuery($query);
			$modules = $db->loadObjectList();
		}
		$json = [];
		if (count($modules) < 1)
		{
			$json['response'] = '';
		} else {
			foreach($modules as $module) {
				$app->setUserState( 'mod_j2store_detailcart.isAjax', '1' );
				$json['response'][$module->id] = ModuleHelper::renderModule($module);
			}
		}
		echo json_encode($json);
		$app->close();
	}

	/**
	 * Removes an item from the cart via AJAX and returns the result as a JSON response.
	 *
	 * This function clears the cache, disables caching, and retrieves the cart item ID from the
	 * request. It then attempts to delete the specified cart item using the `Carts` model and
	 * returns the result in JSON format, indicating success or failure.
	 *
	 * @since version
	 */
	public static function removeCartItemAjax() {
		J2Store::utilities()->clear_cache();
		J2Store::utilities()->nocache();
		$app = J2Store::platform()->application();
		$cart_item_id = $app->input->get ('cartitem_id',0);
		$model = J2Store::fof()->getModel('Carts' ,'J2StoreModel');
		$app->set('cartitem_id',$cart_item_id);
		$model->setInput(['cartitem_id'=>$cart_item_id]);
		$json = $model->deleteItem() ? ['msg' => Text::_('J2STORE_CART_UPDATED_SUCCESSFULLY'), 'status' => true] : ['msg' => $model->getError(), 'status' => false];
		echo json_encode($json);
		$app->close();
	}
}
