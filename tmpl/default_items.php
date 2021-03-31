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
$line_item = $params->get('show_cartitem_name',1) ||$params->get('show_cartitem_image',1) || $params->get('show_cartitem_qty',1) ||  $params->get('show_cartitem_sku',1) ;
$image_width =(int) $params->get('cartitem_image_width',100);
//$app = JFactory::getApplication();
//$menu_id = $app->getMenu()->getActive()->id;
?>
<style type="text/css">
#detailcart-table-<?php echo $module->id ;?>  .dccart-thumb-image img {
	width: <?php echo $image_width;?>px;
}
</style>
<table id="detailcart-table-<?php echo $module->id;?>" class="table table-bordered">
	<thead>
		<tr>
			<!-- Cart Line item table header -->
			<?php if($line_item):?>
			<?php $row_header+=1; ?>
			<th>
				<?php echo JText::_('J2STORE_CART_LINE_ITEM'); ?>
			</th>
			<?php endif;?>

			<!-- Tax Column table header -->
			<?php if($params->get('show_cartitem_quantity',1)):?>
			<?php $row_header+=1; ?>
			<th>
				<?php echo JText::_('J2STORE_CART_LINE_ITEM_QUANTITY'); ?>
			</th>
			<?php endif;?>

			<!-- Tax Column table header -->
			<?php if(isset($taxes) && count($taxes) &&  $params->get('show_cartitem_tax',1)):?>
			<?php $row_header+=1; ?>
			<th>
				<?php echo JText::_('J2STORE_CART_LINE_ITEM_TAX'); ?>
			</th>
			<?php endif;?>

			<!-- Cartitem total column table header  -->
			<?php if($params->get('show_cartitem_total',1)):?>
			<?php $row_header+=1; ?>
			<th>
				<?php echo JText::_('J2STORE_CART_LINE_ITEM_TOTAL'); ?>
			</th>
			<?php endif;?>

			<!-- Cartitem remove link column table header  -->
			<?php if($params->get('show_cartitem_remove_option',1)):?>
			<?php $row_header+=1; ?>
			<!-- Empty th will be displayed -->
			<th></th>
			<?php endif;?>
		</tr>
	</thead>
	<tbody>
		<!-- Check cartitems is not empty -->
		<?php if(!empty($cartitems)):?>
		<!-- Loop the items -->
		<?php $i = 0; ?>
		<?php foreach($cartitems as $citem): ?>
		<?php
			// get the cartitem params
			$registry = new JRegistry;
			$registry->loadString($citem->orderitem_params);
			$citem->params = $registry;
			$thumb_image = $citem->params->get('thumb_image', '');
		?>
		<tr class="cartitem-tr"
			id="cartitem-row-<?php echo $citem->cartitem_id;?>">
			<td>
				<?php if($line_item):?>
				<!-- Check Show  / Hide Cartitem  Image -->
				<?php if($params->get('show_cartitem_image', 1) && !empty($thumb_image)): ?>
				<span class="dccart-thumb-image">
					<img alt="<?php echo $citem->orderitem_name; ?>" src="<?php echo $thumb_image; ?>">
				</span>
				<?php endif; ?>

				<!-- Show  / Hide Cartitem  Name -->
				<?php if($params->get('show_cartitem_name',1)):?>
					<span class="cart-product-name">
						<?php echo $citem->orderitem_name; ?>
					</span>
				<?php endif; ?>
				<br />

				<!-- Show  / Hide Cartitem  Attibutes -->
				<?php if($params->get('show_cartitem_attribs',1)):?>
					<?php if(isset($citem->orderitemattributes) && $citem->orderitemattributes): ?>
						<span class="cart-item-options">
						<?php foreach ($citem->orderitemattributes as $attribute): ?>
							<small> - <?php echo JText::_($attribute->orderitemattribute_name); ?>
								: <?php echo $attribute->orderitemattribute_value; ?>
						</small> <br />
						<?php endforeach;?>
						</span>
					<?php endif; ?>
				<?php endif; ?>

					<!-- Show  / Hide Cartitem Price -->
					<?php if($params->get('show_cartitem_price', 1)): ?>
					<span class="cart-product-unit-price"> <span
						class="cart-item-title"><?php echo JText::_('J2STORE_CART_LINE_ITEM_UNIT_PRICE'); ?>
					</span> <span class="cart-item-value"> <?php echo $currency->format($order->get_formatted_lineitem_price($citem, $j2store_params->get('checkout_price_display_options', 1))); ?>

					</span>
					</span>
					<?php endif; ?>

					<!-- Show  / Hide Cartitem SKU -->
					<?php if($params->get('show_cartitem_sku', 1)): ?>
					<br />
					<span class="cart-product-sku"> <span
						class="cart-item-title"><?php echo JText::_('J2STORE_CART_LINE_ITEM_SKU'); ?>
					</span> <span class="cart-item-value"><?php echo $citem->orderitem_sku; ?>
					</span>
					</span>
					<?php endif; ?>
					<?php if(isset($onDisplayCartItem[$i])):?>
							<br/>
							<?php echo $onDisplayCartItem[$i];?>						
						<?php endif;?>
						<?php $i++;?>
						<?php echo J2Store::plugin()->eventWithHtml('AfterDisplayLineItemTitle', array($citem, $order, $params));?>
			</td>
			<?php endif;?>

			<?php if($params->get('show_cartitem_quantity',0)):?>
			<td><?php echo $citem->orderitem_quantity;?>
			</td>
			<?php endif;?>

			<!-- Check taxes exists and show the tax  when show_cartitem_tax is enabled  -->
			<?php if(isset($taxes) && count($taxes) && $params->get('show_cartitem_tax', 0)): ?>
			<td><?php echo $currency->format($citem->orderitem_tax); 	?></td>
			<?php endif; ?>

			<!-- Show / Hide Cart item Total -->
			<?php if($params->get('show_cartitem_total', 0)): ?>
			<td><?php echo $currency->format($order->get_formatted_lineitem_total($citem, $j2store_params->get('checkout_price_display_options', 1))); ?>
			</td>
			<?php endif; ?>
			<!-- Show / Hide Cart item Remove Link -->
			<?php if($params->get('show_cartitem_remove_option',1)):?>
			<td><a class="j2store-remove remove-icon"
				onclick="j2storeDCremovecartItem(this);"
				data_cartitem_id="<?php echo $citem->cartitem_id;?>"
				href="javascript:void(0);">X</a>
			</td>
			<?php endif;?>
		</tr>
		<?php endforeach;?>
		<!-- Display plugin results -->
		<?php  echo $after_display_cart; ?>
		</tbody>
		<!-- CARTITEM  TOTAL  SUMMARY -->
		<?php if($params->get('show_carttotal',1)):?>
			<?php  require JModuleHelper::getLayoutPath('mod_j2store_detailcart',  $params->get('layout', 'default').'_total');?>
			<?php endif;?>
		<?php endif?>
</table>
<script type="text/javascript">
function j2storeDCremovecartItem(thisElement){
	(function($){
		var cartitem_id = $(thisElement).attr('data_cartitem_id');
		if(cartitem_id =='') return false;
		//var request = ;
		$.ajax({
			type : 'get',
			cache : false,
			contentType : 'application/json; charset=utf-8',
			dataType : 'json',
			data : {
				'option' : 'com_ajax',
				'module' : 'j2store_detailcart',
				'method' : 'removeCartItem',
				'cartitem_id' : cartitem_id,
				'format' : 'json',
				'Itemid' : '<?php echo $menu_id;?>'
			},
			success:function(json){
				if(json['status'] == true){
					location.reload();
				}else{
					$('.detailcart-message-container').html('<p class=\'alert alert-success j2store-mdc-error\'>'+ json['msg'] +'</a>').delay(3200).fadeOut(300);
				}
			}
		});
	})(j2store.jQuery);
}
</script>
