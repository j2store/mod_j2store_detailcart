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
class mod_j2store_detailcartInstallerScript {
function preflight( $type, $parent ) {
		if(!JComponentHelper::isEnabled('com_j2store')) {
			Jerror::raiseWarning(null, 'J2Store not found. Please install J2Store before installing this plugin');
			return false;
		}
		jimport('joomla.filesystem.file');
		$version_file = JPATH_ADMINISTRATOR.'/components/com_j2store/version.php';
		if (JFile::exists ( $version_file )) {
			require_once($version_file);
			if (version_compare ( J2STORE_VERSION, '3.1.7', 'lt' ) ) {
				Jerror::raiseWarning ( null, 'You need at least J2Store version 3.1.7 for this Module to work' );
				return false;
			}
		} else {
			Jerror::raiseWarning ( null, 'J2Store not found or the version file is not found. Make sure that you have installed J2Store before installing this plugin' );
			return false;
		}
	}
}
