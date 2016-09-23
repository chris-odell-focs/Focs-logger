<?php
namespace Focslo {

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

    /**
     * The interface implemented by a logger
     *
     * @version 1.0.0
     */
    interface IFocs_Logger {
        
        function trace( $message, $exception = null, $date_time = null );
        function debug( $message, $exception = null, $date_time = null );
        function info( $message, $exception = null, $date_time = null );
        function warn( $message, $exception = null, $date_time = null );
        function fatal( $message, $exception = null, $date_time = null );
    }
}