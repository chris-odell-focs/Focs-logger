<?php
     
/**
 * Copyright 2016  Foxdell Codesmiths (www.foxdellcodesmiths.com)
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
?>

<?php

/**
 * @package FOCSLO
 */

/*
Plugin Name: Foxdell Codesmiths Logger
Plugin URI: http://logger.foxdellcodesmiths.com/
Description: Create a logging framework which is tailored to WordPress 
Version: 1.0.0
Author: Foxdell Codesmiths
Author URI: http://www.foxdellcodesmiths.com
License: GPLv2 or later
Text Domain: focslo
*/


/*
 
This framework allows the creation of a logger with it's own independant configuration
to make it easier to use the logging framework in multiple plugins.

It borrows heavily from Log4PHP, but by making the config specific to each
instance it gets around the issue with Log4PHP of having global config for
different plugins.

*/

include_once plugin_dir_path( __FILE__ ).'includes/interface-ifocs-logger.php';
include_once plugin_dir_path( __FILE__ ).'includes/class-focs-logger.php';

if (!function_exists('write_log')) {
	    function write_log ( $log )  {
	        if ( true === WP_DEBUG ) {
	            if ( is_array( $log ) || is_object( $log ) ) {
	                error_log( print_r( $log, true ) );
	            } else {
	                error_log( $log );
	            }
	        }
	    }
}


/**
 * This class allows the creation of an instance of the logger class
 * of the correct version. Creating an insatnce this way allows for backward compatibility
 * and allows multiple version of the logger class to exist;
 * 
 * @since 1.0.0
 */
class Focs_Logger {
    
    /**
     * Get an instance of the logger class.
     * 
     * By setting the version parameter an older version of the logger
     * class and be instantiatied. Otherwise the latest version will be
     * created.
     * 
     * @param mixed $config Configuration of the logger 
     * @param mixed $version The version. By default this will be the latest version.
     * 
     * @since 1.0.0
     */
    public static function get_instance( $config ) {
        
        $class_name = '\\Focslo\Focs_Logger_v'.self::$version;
        $new_logger = new $class_name( $config );

        return $new_logger;
    }

    public static function set_version( $version ) {
    
        self::$version = $version;
    }

    private static $version = '100';

}
