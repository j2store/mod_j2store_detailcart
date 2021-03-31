<?php
/*------------------------------------------------------------------------
# com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
require_once  JPATH_ADMINISTRATOR .'/components/com_j2store/helpers/j2html.php';

?>
<div id="j2store-cart-modules">
	<label>
		<input type="radio" name="next" value="shipping-<?php echo $module->id;?>" id="shipping_estimate" />
		<?php echo JText::_('J2STORE_CART_TAX_SHIPPING_CALCULATOR_HEADING'); ?>
	</label>
	<div id="shipping-<?php echo $module->id;?>" class="content" style="display:none;">
		<form action="<?php echo JRoute::_('index.php');?>" method="post" id="detailcart-shipping-estimate-form-<?php echo $module->id;?>" onkeypress="return estimateKeyPress(event);" onsubmit="return false;">
			<table>
				<?php if($params->get('show_country',1)):?>
					<tr>
						<td>
							<?php if($params->get('country_required',1)):?>
								<span class="required">*</span>
							<?php endif;?>
							<?php echo JText::_('J2STORE_SELECT_A_COUNTRY'); ?></td>
						<td>

							<?php
							echo J2Html::select()->clearState()
								->type('genericlist')
								->name('country_id')
								->idTag('estimate_country_id')
								->value($country_id)
								->attribs(array('class'=>'dcart-shipping-input'))
								->setPlaceHolders(array(''=>JText::_('J2STORE_SELECT_OPTION')))
								->hasOne('Countries')
								->setRelations(
									array (
										'fields' => array (
											'key'=>'j2store_country_id',
											'name'=>'country_name'
										)
									)
								)->getHtml();

							?>
							<input type="hidden" id="country_required" name="country_required" value="<?php echo $params->get('country_required',1);?>"/>
						</td>
					</tr>
				<?php else:?>
					<input type="hidden" id="country_required" name="country_required" value="0"/>
				<?php endif;?>


				<?php if($params->get('show_zone',1)):// || empty($zone_id)?>
					<tr>
						<td>
							<?php if($params->get('zone_required',1)):?>
								<span class="required">*</span>
							<?php endif;?>
							<?php echo JText::_('J2STORE_STATE_PROVINCE'); ?></td>
						<td><select data_country_id="<?php echo $country_id;?>" id="estimate_zone_id" name="zone_id">
							</select></td>
						<input type="hidden" id="zone_required" name="zone_required" value="<?php echo $params->get('zone_required',1);?>"/>
					</tr>
				<?php else:?>
					<input type="hidden" id="zone_required" name="zone_required" value="0" />
				<?php endif;?>
				<?php if($params->get('show_zip',1)):?>
					<tr>
						<td>
							<?php if($params->get('postal_required', 1)): ?>
								<span class="required">*</span>
							<?php endif;?>
							<?php echo JText::_('J2STORE_POSTCODE'); ?>
						</td>
						<td><input type="text" id="estimate_postcode" name="postcode" value="<?php echo $postcode; ?>" onkeypress="return estimateKeyPress(event);"/></td>
					</tr>
				<?php else:?>
					<input type="hidden" id="postal_required" name="postal_required" value="0"/>
				<?php endif;?>
			</table>
			<input type="button" value="<?php echo JText::_('J2STORE_CART_CALCULATE_TAX_SHIPPING'); ?>" id="dc-button-quote-<?php echo $module->id;?>" class="btn btn-primary" />
			<input type="hidden" name="option" value="com_j2store" />
			<input type="hidden" name="view" value="carts" />
			<input type="hidden" name="task" value="estimate" />
		</form>
	</div>

</div>

<script type="text/javascript"><!--
	j2store.jQuery('input[name=\'next\']').bind('click', function() {
		j2store.jQuery('#j2store-detailcart-modules > div').hide();
		j2store.jQuery('#' + this.value).slideToggle('slow');
	});
	//--></script>

<?php
if(!isset($zone_id)) {
	$zone_id = '';
}

?>
<script type="text/javascript">
	function estimateKeyPress(e)
	{
		(function($) {
			if(e.keyCode == 13){
				$("#dc-button-quote-<?php echo $module->id;?>").click();
			}
		})(j2store.jQuery);
	}
	(function($){
		$(document).on('submit',function(e){
			if(e.keyCode == 13){
				$("#dc-button-quote-<?php echo $module->id;?>").click();
			}else{
				e.preventDefault();
			}
		});
	})(j2store.jQuery);
	(function($) {
		$(document).on('click', '#dc-button-quote-<?php echo $module->id;?>', function() {
			var values = $('#detailcart-shipping-estimate-form-<?php echo $module->id;?>').serializeArray();
			$.ajax({
				url:'index.php?option=com_j2store&view=carts&task=estimate',
				type: 'get',
				data: values,
				dataType: 'json',
				beforeSend: function() {
					$('#dc-button-quote-<?php echo $module->id;?>').after('<span class="wait">&nbsp;<img src="media/j2store/images/loader.gif" alt="" /></span>');
				},
				complete: function() {
					$('.wait').remove();
				},
				success: function(json) {

					console.log(json['error']);


					$('.warning, .j2error').remove();
					if (json['error']) {
						$.each( json['error'], function( key, value ) {
							if (value) {
								$('#detailcart-shipping-estimate-form-<?php echo $module->id;?>  #estimate_'+key).after('<br class="j2error" /><span class="j2error">' + value + '</span>');
							}

						});
					}
					if (json['redirect']) {
						location.reload();
					}
				}
			});

		});

	})(j2store.jQuery);


	var country_id = '<?php echo $country_id;?>';
	function getZonelist(country_id){
		(function($) {
			//country_id = $(element).attr('value');
			$.ajax({
				url:'index.php?option=com_j2store&view=carts&task=getCountry&country_id=' + country_id,
				type: 'get',
				dataType: 'json',
				beforeSend: function() {
					$('#estimate_country_id-<?php echo $module->id?>').after('<span class="wait">&nbsp;<img src="<?php echo JUri::root(true); ?>/media/j2store/images/loader.gif" alt="" /></span>');
				},
				complete: function() {
					$('.wait').remove();
				},
				success: function(json) {

					html = '<option value=""><?php echo JText::_('J2STORE_SELECT_OPTION'); ?></option>';

					if (json['zone'] != '') {
						for (i = 0; i < json['zone'].length; i++) {
							html += '<option value="' + json['zone'][i]['j2store_zone_id'] + '"';

							if (json['zone'][i]['j2store_zone_id'] == '<?php echo $zone_id; ?>') {
								html += ' selected="selected"';
							}

							html += '>' + json['zone'][i]['zone_name'] + '</option>';
						}
					} else {
						html += '<option value="0" selected="selected"><?php echo JText::_('J2STORE_CHECKOUT_ZONE_NONE'); ?></option>';
					}

					$('select[name=\'zone_id\']').html(html);
				},
				error: function(xhr, ajaxOptions, thrownError) {
					//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		})(j2store.jQuery);
	}

	(function($) {
		getZonelist(country_id);
		$('select[name=\'country_id\']').bind('change', function() {
			country_id = $(this).attr('value');
			getZonelist(country_id);
		});
		$('input[name=\'country_id\']').bind('', function() {
			country_id = $(this).attr('value');
			getZonelist(country_id);
		});

		if($('#estimate_country_id-<?php echo $module->id?>').length > 0 ){
			$('select[name=\'country_id\']').trigger('change');
			$('input[name=\'country_id\']').change();
		}
	})(j2store.jQuery);
</script>
