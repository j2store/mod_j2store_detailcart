<?php
/*
 ------------------------------------------------------------------------
# mod_j2store_detailcart - J2Store Detail cart
# ------------------------------------------------------------------------
# author    ThemeParrot - ThemeParrot http://www.ThemeParrot.com
# copyright Copyright (C) 2014 ThemeParrot.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://ThemeParrot.com
# Based on Latest Articles module of Joomla
-------------------------------------------------------------------------
*/
// no direct access
defined('_JEXEC') or die('Restricted access');
// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root(true).'/modules/mod_j2store_detailcart/css/detailcart.css');
$document->addScript(JUri::root(true).'/media/j2store/js/j2store.js');
$session= JFactory::getSession();
$j2store_params = J2Store::config();
$currency = J2Store::currency();

$cartitems =ModJ2StoreDetailCartHelper::getCartItems($params);
ModJ2StoreDetailCartHelper::getShippingData();


$layout = $params->get('layout', 'default');
$order = ModJ2StoreDetailCartHelper::$_order;
$taxes = ModJ2StoreDetailCartHelper::$_taxes;
$country_id = ModJ2StoreDetailCartHelper::$_country_id;
$zone_id = ModJ2StoreDetailCartHelper::$_zone_id;
$postcode = ModJ2StoreDetailCartHelper::$_postcode;
$onDisplayCartItem = ModJ2StoreDetailCartHelper::$_onDisplayCartItem;
$before_display_cart = ModJ2StoreDetailCartHelper::$_before_display_cart;
$after_display_cart = ModJ2StoreDetailCartHelper::$_after_display_cart;
$cart_model = F0FModel::getTmpInstance('Carts','J2StoreModel');
$checkout_url = $cart_model->getCheckoutUrl();
$cart_link = $cart_model->getCartUrl();

//do we have shipping methods
$shipping_methods = $session->get('shipping_methods', array(), 'j2store');
$shipping_values = $session->get('shipping_values', array(), 'j2store');
$app = JFactory::getApplication();
$menu_id = $app->input->getInt('Itemid');
if($app->getMenu()){
	$active = $app->getMenu()->getActive();
	if(isset($active)){
		$menu_id = $app->getMenu()->getActive()->id;
	}
}
$menu_id = isset($menu_id) ? $menu_id : 0;
$script = "
	if(typeof(j2store) == 'undefined') {
		var j2store = {};
	}
	if(typeof(j2store.jQuery) == 'undefined') {
		j2store.jQuery = jQuery.noConflict();
	}
 (function($) {
	$(document).bind('after_adding_to_cart', function(element,data, type){
		var request = {
					'option' : 'com_ajax',
					'module' : 'j2store_detailcart',
					'method' : 'getUpdatedDetailcart',
					'format' : 'json',
					'Itemid' : {$menu_id}
					};

		$.ajax({
					type : 'get',
					data : request,
					cache : false,
					contentType : 'application/json; charset=utf-8',
					dataType : 'json',
						success : function(json) {
							if (json != null && json['response']) {
								$.each(json['response'], function(key, value) {
									 if ($('.mod_j2store_detailcart_' + key).length) {
										$('.mod_j2store_detailcart_'+key).each(function() {
											$(this).html(value);
										});
									}
								});
							location.reload();
							}
						}
					});
				});
	})(j2store.jQuery);
";
$document->addScriptDeclaration($script);
require JModuleHelper::getLayoutPath('mod_j2store_detailcart', $layout);

?>
