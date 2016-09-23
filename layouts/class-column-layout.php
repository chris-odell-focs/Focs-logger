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
     * A layout which returns as log entry as an array which matches a target table
     *
     * @version 1.0.0
     */
    class Column_Layout extends Focs_Layout implements IFocs_Layout {

        /************************************************************************
         * public methods
         ************************************************************************/
        /**
         * Format a logger entry
         *
         * @param mixed $message The mesage being logged
         * @param mixed $level The logger level. trace, debug, info, warn or fatal
         * @param mixed $exception Any exception that was raised
         * @param mixed $date_time The date_time of the log entry
         *
         * @return string The formatted log entry as a single line based on the passed in pattern parameter
         *
         * @since 1.0.0
         */
        public function format( $message, $level, $exception, $date_time ) {

            $col_array[] = $message;
            $col_array[] = $level;
            $col_array[] = ( !empty( $exception ) ) ? $exception->getMessage() : '';;
            $col_array[] = ( !empty( $date_time ) ) ? $date_time : date( 'Y-m-d G:i:s' );;

            return $col_array;
        }

        /**
         * Check to see if the layout required params are present.
         *
         * At the moment only the pattern parameter is required
         * @return bool
         */
        public function has_required_params() {

            $has_params = isset( $this->params[ 'columns' ] );
            if( $has_params ) { $has_params = ( '' !== $this->params[ 'columns' ] ); }

            return $has_params;
        }

        /************************************************************************
         * private methods
         ************************************************************************/

        /************************************************************************
         * private variables
         ************************************************************************/

        /**
         * Minimum parameters required by this appender
         *
         * @var string[] $required_params
         *
         * @since 1.0.0
         */
        private $required_params = array(
            'columns'
        );
    }
}