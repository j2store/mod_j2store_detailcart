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
defined('_JEXEC') or die('Restricted access');?>
<?php if(isset($shipping_methods) && count($shipping_methods)): ?>
<form action="<?php echo JRoute::_('index.php'); ?>"  name="j2store-detailcart-shipping-form" id="j2store-detailcart-shipping-form-<?php echo $module->id;?>"
				enctype="multipart/form-data" >

	<div id="j2store-detailcart-shipping" class="j2store-cart-shipping">
	<h3><?php echo JText::_('J2STORE_CHECKOUT_SELECT_A_SHIPPING_METHOD');?></h3>
	<?php foreach($shipping_methods as $method): ?>
	<?php
		$checked = '';
		if(isset($shipping_values['shipping_name']) && $shipping_values['shipping_name']==$method['name']) {
			$checked = 'checked';
		}
	?>
	<input type="radio" id="shipping_<?php echo $method['element']; ?>" rel="<?php echo addslashes($method['name'])?>" name="shipping_method" <?php echo $checked; ?> onClick="j2storeDCUpdateShipping('<?php echo addslashes($method['name']); ?>','<?php echo $method['price']; ?>',<?php echo $method['tax']; ?>,<?php echo $method['extra']; ?>, '<?php echo $method['code']; ?>', true );" />
	<label for="shipping_<?php echo $method['element']; ?>" onClick="j2storeDCUpdateShipping('<?php echo addslashes($method['name']); ?>','<?php echo $method['price']; ?>',<?php echo $method['tax']; ?>,<?php echo $method['extra']; ?>, '<?php echo $method['code']; ?>', true );">
		<?php echo stripslashes(JText::_($method['name'])); ?> ( <?php echo $currency->format( $method['total']); ?> )
	</label>

	<?php endforeach; ?>

</div>
<?php endif;?>
<?php $setval = false;?>
<input type="hidden" name="shipping_price" id="shipping_price" value="<?php echo $setval ? $this->shipping_methods['0']['price'] : "";?>" />
<input type="hidden" name="shipping_tax" id="shipping_tax" value="<?php echo $setval ? $this->shipping_methods['0']['tax'] : "";?>" />
<input type="hidden" name="shipping_name" id="shipping_name" value="<?php echo $setval ? $this->shipping_methods['0']['name'] : "";?>" />
<input type="hidden" name="shipping_code" id="shipping_code" value="<?php echo $setval ? $this->shipping_methods['0']['code'] : "";?>" />
<input type="hidden" name="shipping_extra" id="shipping_extra" value="<?php echo $setval ? $this->shipping_methods['0']['extra'] : "";?>" />
</form>
<script type="text/javascript">

	function j2storeDCUpdateShipping(name, price, tax, extra, code, combined) {
		(function($) {
			var form = $('#j2store-detailcart-shipping-form-<?php echo $module->id;?>');
			form.find("input[type='hidden'][name='shipping_name']").val(name);
			form.find("input[type='hidden'][name='shipping_code']").val(code);
			form.find("input[type='hidden'][name='shipping_price']").val(price);
			form.find("input[type='hidden'][name='shipping_tax']").val(tax);
			form.find("input[type='hidden'][name='shipping_extra']").val(extra);
			//override the task
			form.find("input[type='hidden'][name='task']").val('shippingUpdate');

			$.ajax({
				url: 'index.php?option=com_j2store&view=carts&task=shippingUpdate',
				type: 'get',
				data: $('#j2store-detailcart-shipping-form-<?php echo $module->id;?>  input[type=\'hidden\'], #j2store-detailcart-shipping-form-<?php echo $module->id;?> input[type=\'radio\']:checked'),
				dataType: 'json',
				cache: false,
				beforeSend: function() {
					$('#detailcart-shipping-estimate-form-<?php echo $module->id;?>').after('<span class="wait">&nbsp;<img src="<?php echo JUri::root(true); ?>/media/j2store/images/loader.gif" alt="" /></span>');
				},
				complete: function() {
					$('.wait').remove();
				},
				success: function(json) {
					if (json['redirect']) {
						//reload the page
						location.reload();
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});

		})(j2store.jQuery);
	}

	<?php
		// if the auto select feature is enabled then apply the first shipping when shipping is empty
		if ($j2store_params->get('auto_apply_shipping_rate',1) == 1 ) {
			if ( empty($shipping_values) && !empty($shipping_methods) && isset($shipping_methods[0]['name']) ) {
				$method = $shipping_methods[0];	
				?>
				j2storeDCUpdateShipping('<?php echo addslashes($method['name']); ?>','<?php echo $method['price']; ?>',<?php echo $method['tax']; ?>,<?php echo $method['extra']; ?>, '<?php echo $method['code']; ?>', true );
	<?php }
		}	?>	
		
	</script>