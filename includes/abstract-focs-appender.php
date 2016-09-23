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

    abstract class Focs_Appender {

        /**
         * Use factory pattern to create an appender instance
         *
         * @param mixed $type The typer of appender, console, file, wordpress
         *
         * @return object Subclass of Focs_Appender
         */
        public static function get_appender( $type ) {

            $class_file = self::$appender_map[ $type ];
            require_once( FOCSLO_APPENDERS.'/'.$class_file.'.php' );

            $class_file = str_replace( '-', '_', str_replace( 'class-', '', $class_file ) );
            $class_name = '\\Focslo\\'.$class_file;

            $appender = new $class_name();

            return $appender;
        }

        /**
         * Set the unique name of the appender
         *
         * @param mixed $name
         *
         * @since 1.0.0
         */
        public function set_name( $name ) {

            $this->name = $name;
        }

        /**
         * Set any parameters required by the appender
         *
         * @param mixed $params
         */
        public function set_params( $params ) {

            $this->params = $params;
        }

        /**
         * Set any parameters required by the appender
         *
         * @param mixed $params
         */
        public function set_level( $level ) {

            $this->level = $level;
        }

        /**
         * Check to see if the logger should log the message.
         *
         * If the logging level is debug and we are logging at trace then the
         * logger should not log the trace value.
         *
         * @param mixed $level
         * @return bool
         */
        public function over_log_threshold( $level ) {

            $current_level_idx = $this->level_to_index( $this->level );
            $level_idx = $this->level_to_index( $level );

            //always log if the logging level is higher than 2
            if( 2 < $level_idx ) {

                return true;
            }

            return $level_idx >= $current_level_idx;
        }

        /**
         * Any parameters an appender requires
         *
         * @var mixed[] $params
         *
         * @since 1.0.0
         */
        protected $params;

        /**
         * The unique name of this appender
         *
         * @var string $name
         *
         * @since 1.0.0
         */
        protected $name;

        /**
         * The current logger level.
         *
         * Other than warn or fatal if the level logged at
         * is lower than the config level then do not log.
         *
         * Default to trace
         *
         * @var string $level
         */
        protected $level = FOCSLO_TRACE;

        /**
         * Convert a level to an index.
         *
         * Helper function to decide whether to log or not
         *
         * @param mixed $level The string version of the level to convert into a numberical representation
         *
         * @return int The numerical representation of the level
         *
         * @since 1.0.0
         */
        private function level_to_index( $level ) {

            $idx = 0;

            switch( $level ) {
                case FOCSLO_DEBUG:
                    $idx = 1;
                    break;
                case FOCSLO_INFO:
                    $idx = 2;
                    break;
                case FOCSLO_WARN:
                    $idx = 3;
                    break;
                case FOCSLO_FATAL:
                    $idx = 4;
                    break;
            }

            return $idx;
        }

        /**
         * The map to map appender type to appender name.
         *
         * @var string[]
         *
         * @since 1.0.0
         */
        private static $appender_map = array(
            'file' => 'class-file-appender',
            'wordpress' => 'class-wordpress-appender'
        );
    }

}