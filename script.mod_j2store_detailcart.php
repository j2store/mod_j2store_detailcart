<?php
/**
------------------------------------------------------------------------
 * mod_j2store_detailcart - J2Store Detail cart
 * ------------------------------------------------------------------------
 * author    Gopi  http://www.ThemeParrot.com
 * copyright  (C) 2024 ThemeParrot.com. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://ThemeParrot.com
 * Based on Latest Articles module of Joomla
-------------------------------------------------------------------------
 */
// no direct access
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Filesystem\Path;

defined('_JEXEC') or die('Restricted access');
class Mod_j2store_detailcartInstallerScript {
	/**
	 *  The list of extra modules and plugins to install on component installation / update and remove on component
	 *  uninstallation.
	 *
	 * @param   string     $type    Which action is happening (install|uninstall|discover_install|update)
	 * @param   Installer  $parent  The class calling this method
	 *
	 * @return bool
	 */
    function preflight( $type, $parent ) {
        $app = J2Store::platform()->application();
        if (version_compare(JVERSION, '3.99.99', 'lt')) {
            $app->enqueueMessage('You are using an old version of Joomla. This module requires Joomla 4.0.0 or later.');
            return false;
        }
        if(!ComponentHelper::isEnabled('com_j2store')) {
            $app->enqueueMessage( 'J2Store not found. Please install J2Store before installing this module');
            return false;
        }

        $version_file = JPATH_ADMINISTRATOR.'/components/com_j2store/version.php';
        if (! is_file( Path::clean( $version_file ) )) {
	        $app->enqueueMessage( 'J2Store not found or the version file is not found. Make sure that you have installed J2Store before installing this module' );
	        return false;
        }
        require_once($version_file);
        // abort if the current J2Store release is older
        if (version_compare(J2STORE_VERSION, '4.0.4', 'lt')) {
	        $app->enqueueMessage( 'You are using an old version of J2Store. Please upgrade to the latest version 4.0.4');
	        return false;
        }
	    return true;
    }
}
