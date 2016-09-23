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
     * A layout which implements a pattern for each line
     *
     * @version 1.0.0
     */
    class Pattern_Layout extends Focs_Layout implements IFocs_Layout {

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

            $this->message = $message;
            $this->level = $level;
            $this->exception_message = ( !empty( $exception ) ) ? $exception->getMessage() : '';
            $this->date_time = ( !empty( $date_time ) ) ? $date_time : date( 'Y-m-d G:i:s' );

            if( isset( $this->params[ 'custom_pattern_callbacks' ] ) &&
                is_array( $this->params[ 'custom_pattern_callbacks' ] ) ) {

                $this->pattern_callbacks = $this->params[ 'custom_pattern_callbacks' ];
            }

            $this->load_default_callbacks();
            $pattern = $this->params[ 'pattern' ];
            foreach( $this->pattern_callbacks as $pattern_callback ) {

                $pattern = call_user_func_array( $pattern_callback, array( $pattern ) );
            }

            return $pattern;
        }

        /**
         * Check to see if the layout required params are present.
         *
         * At the moment only the pattern parameter is required
         * @return bool
         */
        public function has_required_params() {

            $has_params = isset( $this->params[ 'pattern' ] );
            if( $has_params ) { $has_params = ( '' !== $this->params[ 'pattern' ] ); }

            return $has_params;
        }

        /************************************************************************
         * private methods
         ************************************************************************/

        /************************************************************************
         * private variables
         ************************************************************************/
        /**
         * Load the default/built in pattern callbacks
         *
         * @return void
         *
         * @since 1.0.0
         */
        private function load_default_callbacks() {

            $this->pattern_callbacks = wp_parse_args( $this->pattern_callbacks, array(
                array( $this, 'date_pattern' ),
                array( $this, 'level_pattern' ),
                array( $this, 'exception_pattern' ),
                array( $this, 'newline_pattern' ),
                array( $this, 'message_pattern' ),
            ) );
        }

        /**
         * Replace a date place holder in the pattern with the date time
         *
         * Can use %date or %d. Uses the supplied date format or defaults to Y-m-d G:i:s
         *
         * @param mixed $pattern The pattern to use
         *
         * @return string The pattern with the date place holder replaced with the date
         *
         * @since 1.0.0
         */
        private function date_pattern( $pattern ) {

            $date_format = isset( $this->params[ 'date_format' ] ) ? $this->params[ 'date_format' ] : 'Y-m-d G:i:s';

            $pattern = str_replace( '%date', date( $date_format, strtotime( $this->date_time ) ), $pattern );
            $pattern = str_replace( '%d', date( $date_format, strtotime( $this->date_time ) ), $pattern );

            return $pattern;
        }

        /**
         * Replace a level place holder with the passed in logging level
         *
         * Can use %level or %l
         *
         * @param string $pattern The pattern to test for the level placeholder
         *
         * @return The pattern with the level placeholder replaced
         *
         * @since 1.0.0
         */
        private function level_pattern( $pattern ) {

            $pattern = str_replace( '%level', $this->level, $pattern );
            $pattern = str_replace( '%l', $this->level, $pattern );

            return $pattern;
        }

        /**
         * Replace an exception pattern with the message from the passed in exception object
         *
         * Can use %exception or %e
         *
         * @param mixed $pattern The pattern to test for the exception placeholder
         *
         * @return The pattern with the exception message placeholder replaced
         *
         * @since 1.0.0
         */
        private function exception_pattern( $pattern ) {

            $pattern = str_replace( '%exception', $this->exception_message, $pattern );
            $pattern = str_replace( '%e', $this->exception_message, $pattern );

            return $pattern;
        }

        /**
         * Replace a newline pattern with the value of the PHP_EOL constant
         *
         * Can use %newline or %n
         *
         * @param mixed $pattern The pattern to test for the newline placeholder
         *
         * @return The pattern with the newline placeholder replaced
         *
         * @since 1.0.0
         */
        private function newline_pattern( $pattern ) {

            $pattern = str_replace( '%newline', PHP_EOL, $pattern );
            $pattern = str_replace( '%n', PHP_EOL, $pattern );

            return $pattern;
        }

        /**
         * Replace a message pattern with the value of the passed in message parameter
         *
         * Can use %message or %m
         *
         * @param mixed $pattern The pattern to test for the message placeholder
         *
         * @return The pattern with the message placeholder replaced
         *
         * @since 1.0.0
         */
        private function message_pattern( $pattern ) {

            $pattern = str_replace( '%message', $this->message, $pattern );
            $pattern = str_replace( '%m', $this->message, $pattern );

            return $pattern;
        }

        /**
         * Callbacks to apply to the pattern in the parameters.
         *
         * This will include the built in patterns and any custom
         * patterns.
         *
         * @var mixed[]
         *
         * @since 1.0.0
         */
        private $pattern_callbacks = null;

        /**
         * Minimum parameters required by this appender
         *
         * @var string[] $required_params
         *
         * @since 1.0.0
         */
        private $required_params = array(
            'pattern'
        );

        /**
         * Member level variable of passed in message parameter
         *
         * @var string
         *
         * @since 1.0.0
         */
        private $message;

        /**
         * Member level variable of passed in level parameter
         *
         * @var string
         *
         * @since 1.0.0
         */
        private $level;

        /**
         * The exception message of the passed in exception parameter
         *
         * @var \Exception
         *
         * @since 1.0.0
         */
        private $exception_message;

        /**
         * Member level variable of passed in $date_time parameter
         *
         * @var string
         *
         * @since 1.0.0
         */
        private $date_time;
    }
}