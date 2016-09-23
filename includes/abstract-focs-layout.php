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

    abstract class Focs_Layout {

        /**
         * Use factory pattern to create an appender instance
         *
         * @param mixed $type The typer of appender, console, file, wordpress
         *
         * @return object Subclass of Focs_Appender
         */
        public static function get_layout( $type ) {

            $class_file = self::$layout_map[ $type ];
            require_once( FOCSLO_LAYOUTS.'/'.$class_file.'.php' );

            $class_file = str_replace( '-', '_', str_replace( 'class-', '', $class_file ) );
            $class_name = '\\Focslo\\'.$class_file;

            $layout = new $class_name();

            return $layout;
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
         * The map to map appender type to appender name.
         *
         * @var string[]
         *
         * @since 1.0.0
         */
        private static $layout_map = array(
            'simple' => 'class-simple-layout',
            'pattern' => 'class-pattern-layout',
            'column' => 'class-column-layout'
        );

    }
}