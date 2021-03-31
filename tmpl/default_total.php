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
?>
<?php if($totals = $order->get_formatted_order_totals()): ?>
<tr>
	<td colspan="<?php echo $row_header;?>">
		<?php echo JText::_('J2STORE_CART_TOTALS'); ?>
	</td>
</tr>
<?php foreach($totals as $total): ?>
	<tr valign="top">
		<th scope="row" colspan="<?php echo $row_header-1;?>">
			<?php echo $total['label']; ?>
			<?php if(isset($total['link'])):?>
				<?php echo $total['link']; ?>
			<?php endif;?>
		</th>
		<td><?php echo $total['value']; ?></td>
		</tr>
<?php endforeach; ?>
<?php endif; ?>
