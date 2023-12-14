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
defined('_JEXEC') or die('Restricted access');
class Mod_j2store_detailcartInstallerScript {
    function preflight( $type, $parent ) {
        $app = \Joomla\CMS\Factory::getApplication();
        if (version_compare(JVERSION, '3.99.99', 'lt')) {
            $app->enqueueMessage("You are using an old version of Joomla. This module requires Joomla 4.0.0 or later.");
            return false;
        }
        if(!\Joomla\CMS\Component\ComponentHelper::isEnabled('com_j2store')) {
            $app->enqueueMessage( 'J2Store not found. Please install J2Store before installing this module');
            return false;
        }

        $version_file = JPATH_ADMINISTRATOR.'/components/com_j2store/version.php';
        if (\Joomla\CMS\Filesystem\File::exists ( $version_file )) {
            require_once($version_file);
            // abort if the current J2Store release is older
            if (version_compare(J2STORE_VERSION, '4.0.4', 'lt')) {
                $app->enqueueMessage( 'You are using an old version of J2Store. Please upgrade to the latest version 4.0.4');
                return false;
            }
        } else {
            $app->enqueueMessage( 'J2Store not found or the version file is not found. Make sure that you have installed J2Store before installing this module' );
            return false;
        }

    }
}
