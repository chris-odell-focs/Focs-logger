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
     * The logger class which is instantiated to allow logging to
     * a provider
     *
     * @version 1.0.0
     */
    class Focs_Logger_v100 implements IFocs_Logger {

        /****************************************************************************
         * public functions
         ****************************************************************************/
        /**
         * Class initialisation
         *
         * @param mixed $config The configuration of the logger
         *
         * @since 1.0.0
         */
        public function __construct( $config = array() ) {

            $this->do_defines();
            $this->load_common_libs();

            if( !isset( $config[ 'appender' ] ) ) {

                $config[ 'appender' ] = array(
                    'type' => 'file',
                    'params' => array( 'target' => dirname( __FILE__ ).'/../default.log' )
                );
            }

            if( !isset( $config[ 'layout' ] ) ) {

                $config[ 'layout' ] = array(
                    'type' => 'simple',
                    'params' => array()
                );
            }

            if( !isset( $config[ 'appender' ][ 'params' ] ) ) {

                $config[ 'appender' ][ 'params' ] = array();
            }

            if( !isset( $config[ 'layout' ][ 'params' ] ) ) {

                $config[ 'layout' ][ 'params' ] = array();
            }

            if( !isset( $config[ 'logging_level' ] ) ) {

                $config[ 'logging_level' ] = FOCSLO_INFO;
            }

            $this->config = $config;
        }

        /**
         * Write to the log at trace level
         *
         * @param mixed $message The message to log
         * @param mixed $exception Optional. Any exceptions.
         * @param mixed $date_time Optional. The date and time, defaults to NOW if not supplied
         *
         * @return void
         *
         * @since 1.0.0
         */
        public function trace( $message, $exception = null, $date_time = null ) {

            $this->do_write( $message, $exception, $date_time, FOCSLO_TRACE );
        }

        /**
         * Write to the log at debug level
         *
         * @param mixed $message The message to log
         * @param mixed $exception Optional. Any exceptions.
         * @param mixed $date_time Optional. The date and time, defaults to NOW if not supplied
         *
         * @return void
         *
         * @since 1.0.0
         */
        public function debug( $message, $exception = null, $date_time = null ) {

            $this->do_write( $message, $exception, $date_time, FOCSLO_DEBUG );
        }

        /**
         * Write to the log at info level
         *
         * @param mixed $message The message to log
         * @param mixed $exception Optional. Any exceptions.
         * @param mixed $date_time Optional. The date and time, defaults to NOW if not supplied
         *
         * @return void
         *
         * @since 1.0.0
         */
        public function info( $message, $exception = null, $date_time = null ) {

            $this->do_write( $message, $exception, $date_time, FOCSLO_INFO );
        }

        /**
         * Write to the log at warn level
         *
         * @param mixed $message The message to log
         * @param mixed $exception Optional. Any exceptions.
         * @param mixed $date_time Optional. The date and time, defaults to NOW if not supplied
         *
         * @return void
         *
         * @since 1.0.0
         */
        public function warn( $message, $exception = null, $date_time = null ) {

            $this->do_write( $message, $exception, $date_time, FOCSLO_WARN );
        }

        /**
         * Write to the log at fatal level
         *
         * @param mixed $message The message to log
         * @param mixed $exception Optional. Any exceptions.
         * @param mixed $date_time Optional. The date and time, defaults to NOW if not supplied
         *
         * @return void
         *
         * @since 1.0.0
         */
        public function fatal( $message, $exception = null, $date_time = null ) {

            $this->do_write( $message, $exception, $date_time, FOCSLO_FATAL );
        }

        /****************************************************************************
         * private functions
         ****************************************************************************/
        /**
         * Central function for writing to the log file
         *
         * @param mixed $message The log message
         * @param mixed $exception An exception if any
         * @param mixed $date_time The date time. Defaults to 'now'
         * @param mixed $level The logging level
         *
         * @throws \InvalidArgumentException
         *
         * @return void
         *
         * @since 1.0.0
         */
        private function do_write( $message, $exception, $date_time, $level ){

            $appender = $this->get_appender();
            if( $appender->over_log_threshold( $level ) ) {

                $layout = $this->get_layout();
                $date_time = $this->date_time_is_null( $date_time );

                $log_entry = $layout->format( $message, $level, $exception, $date_time );
                $appender->write( $log_entry );
            }
        }

        /**
         * If a date time is null then return todays date
         *
         * @param string $date_time The datetime to test
         *
         * @return string String representation of the date and time
         *
         * @since 1.0.0
         */
        private function date_time_is_null( $date_time ) {

            if( null === $date_time ) {

                return date( 'Y-m-d G:i:s' );
            }

            return $date_time;
        }

        /**
         * Lazily instantiate a layout instance
         *
         * @throws \Exception
         *
         * @return \Focslo\Focs_Layout
         *
         * @since 1.0.0
         */
        private function get_layout() {

            if(  null === $this->current_layout ) {

                $this->current_layout = \Focslo\Focs_Layout::get_layout( $this->config[ 'layout' ][ 'type' ] );
                $this->current_layout->set_params( $this->config[ 'layout' ][ 'params' ] );

                if( !$this->current_layout->has_required_params() ) {

                    throw new \Exception( 'Layout '.$this->config[ 'layout' ][ 'type' ].' does not have the required parameters' );
                }
            }

            return $this->current_layout;
        }

        /**
         * Lazily instantiate an appender instance
         *
         * @throws \Exception
         *
         * @return Focs_Appender
         *
         * @since 1.0.0
         */
        private function get_appender() {

            if(  null === $this->current_appender ) {

                $this->current_appender = \Focslo\Focs_Appender::get_appender( $this->config[ 'appender' ][ 'type' ] );
                $this->current_appender->set_params( $this->config[ 'appender' ][ 'params' ] );
                $this->current_appender->set_level( $this->config[ 'logging_level' ] );

                if( !$this->current_appender->has_required_params() ) {

                    throw new \Exception( 'Appender '.$this->config[ 'appender' ][ 'type' ].' does not have the required parameters' );
                }

                if( !$this->current_appender->layout_supported( $this->config[ 'layout' ][ 'type' ] ) ) {

                    throw new \Exception( 'Appender '.$this->config[ 'appender' ][ 'type' ].' does not support '.$this->config[ 'layout' ][ 'type' ].' layout' );
                }

            }

            return $this->current_appender;
        }

        /**
         * Define any dynamic constants
         *
         * @return void
         *
         * @since 1.0.0
         */
        private function do_defines() {

            if( !defined( 'FOCSLO_PLUGIN_DIR' ) ) {
                define( 'FOCSLO_PLUGIN_DIR', dirname( dirname( __FILE__ ) ) );
            }

            if( !defined( 'FOCSLO_INCLUDES' ) ) {
                define( 'FOCSLO_INCLUDES', FOCSLO_PLUGIN_DIR.'/includes' );
            }

            if( !defined( 'FOCSLO_APPENDERS' ) ) {
                define( 'FOCSLO_APPENDERS', FOCSLO_PLUGIN_DIR.'/appenders' );
            }

            if( !defined( 'FOCSLO_LAYOUTS' ) ) {
                define( 'FOCSLO_LAYOUTS', FOCSLO_PLUGIN_DIR.'/layouts' );
            }

            if( !defined( 'FOCSLO_TRACE' ) ) {
                define( 'FOCSLO_TRACE', 'TRACE' );
            }

            if( !defined( 'FOCSLO_DEBUG' ) ) {
                define( 'FOCSLO_DEBUG', 'DEBUG' );
            }

            if( !defined( 'FOCSLO_INFO' ) ) {
                define( 'FOCSLO_INFO', 'INFO' );
            }

            if( !defined( 'FOCSLO_WARN' ) ) {
                define( 'FOCSLO_WARN', 'WARN' );
            }

            if( !defined( 'FOCSLO_FATAL' ) ) {
                define( 'FOCSLO_FATAL', 'FATAL' );
            }
        }

        private function load_common_libs() {

            $libs = array(
                FOCSLO_INCLUDES.'/abstract-focs-appender.php',
                FOCSLO_INCLUDES.'/abstract-focs-layout.php',
                FOCSLO_INCLUDES.'/interface-ifocs-appender.php',
                FOCSLO_INCLUDES.'/interface-ifocs-layout.php',
            );

            foreach( $libs as $lib ) { require_once( $lib ); }

        }

        /****************************************************************************
         * private variables
         ****************************************************************************/

        /**
         * The layout to use
         *
         * @var \Focslo\Focs_Layout
         */
        private $current_layout = null;

        /**
         * The current appender instance
         *
         * @var \Focslo\Focs_Appender
         */
        private $current_appender = null;

        /**
         * Config for the logger e.g the type of appender & layout
         *
         * @var string[]
         *
         * @since 1.0.0
         */
        private $config;

    }
}