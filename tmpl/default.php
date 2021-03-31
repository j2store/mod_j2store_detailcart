<?php
/*
 	------------------------------------------------------------------------
	# mod_j2store_detailcartv3 - J2Store Detail cart
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
$row_header = 0;
$force_shipping = $params->get('force_shipping',0);
$app = JFactory::getApplication();
$ajax = $app->getUserState('mod_j2store_detailcart.isAjax');

//show only if a shipping method is chosen
if(isset($shipping_method) && count($shipping_method)) {
		$show_checkout = true;
} else {
	$show_checkout = false;
}
?>
<?php if(!$ajax): ?>
	<div id="dcart-block-<?php echo $module->id;?>" class="mod_j2store_detailcart_<?php echo $module->id;?> detailJ2StoreCartBlock<?php if($params->get('moduleclass_sfx')) echo ' '.$params->get('moduleclass_sfx'); ?>">
		<div id="detailJ2StoreCart">
<?php endif;?>
			 <?php if(!empty($cartitems)):?>
			 <div class="span12"><?php echo $before_display_cart;?></div>
			 
	<div class="row-fluid">
		<div class="span12 col-md-12 col-sm-12 col-xs-12 col-lg-12">
			 	<div class="table-responsive">
			 		<div class="detailcart-message-container">

			 		</div>
			 		<!-- Cart items -->
					<?php  require JModuleHelper::getLayoutPath('mod_j2store_detailcart', $params->get('layout', 'default').'_items');?>
				</div>
			<?php if($params->get('show_estimateshipping',0)):?>
				<?php  require JModuleHelper::getLayoutPath('mod_j2store_detailcart', $params->get('layout', 'default') .'_calculator');?>
			<?php endif;?>

			<?php  require JModuleHelper::getLayoutPath('mod_j2store_detailcart', $params->get('layout', 'default') .'_shipping');?>

	 		<?php if($params->get('force_shipping' ,0)):?>
			 		<div id="warning-container-<?php echo $module->id?>" class="mod-j2storedetailcart-status">
					<!-- Make sure the show checkout is enable &&  check country id is set in session  &&  check zone_id is set in session  &&  check postcode is set in session  -->
						<?php if($country_id != '' && $zone_id !='' && $postcode !='' &&  empty($shipping_methods)):?>
							<p class="j2store-mdc-error text text-warning">
								<?php echo JText::_($params->get('custom_forceshipping_message','MOD_J2STORE_NO_SHIPPING_METHOD_MATCHES'));?>
							</p>
						<?php endif;?>
					</div>
			<?php endif;?>


			<div class="buttons-right">
				<?php if($params->get('show_checkout',1)):?>
				<span class="cart-checkout-button">
					<!-- When force shipping is Enabled and Shipping values are empty then disable the Proceed checkout -->
					<?php if($force_shipping ==1 && !empty($shipping_values)):?>
						<a class="btn btn-success" href="<?php echo $checkout_url; ?>" ><?php echo JText::_('J2STORE_PROCEED_TO_CHECKOUT'); ?> </a>
					<?php elseif($force_shipping == 0):?>
						<a class="btn btn-success" href="<?php echo $checkout_url; ?>" ><?php echo JText::_('J2STORE_PROCEED_TO_CHECKOUT'); ?> </a>
					<?php endif;?>
				</span>
				<?php endif;?>
				<?php if($params->get('show_cart_link',0)):?>
					<span class="cart-cart-button">
						<a class="btn btn-success" href="<?php echo $cart_link; ?>" > <?php echo JText::_('J2STORE_PROCEED_TO_CARTS'); ?> </a>
					</span>
				<?php endif;?>
				<span><?php echo J2Store::plugin()->eventWithHtml('AfterDisplayCheckoutButton', array($order)); ?></span>
			</div>
			</div>
		</div>
			<?php else:?>
				<p class="j2store-detailcart-noitems-info">
					<?php echo JText::_('J2STORE_DETAILCART_ITEM_EMPTY');?>
				</p>
			<?php endif;?>
		
	<?php if(!$ajax):?>
	</div>
</div>

<!-- Module from ajax response -->
<?php else:?>
	<?php $app->setUserState('mod_j2store_detailcart.isAjax', 0); ?>
<?php endif; ?>