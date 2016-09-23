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
     * Write the log data to a file
     *
     * @version 1.0.0
     */
    class File_Appender extends Focs_Appender implements IFocs_Appender {

        /**************************************************************************
         * public functions
         **************************************************************************/

        /**
         * Check to see if the requested layout is supported by this
         * appender.
         *
         * @param mixed $layout The layout to check
         *
         * @return bool True if the layout is supported otherwise false
         *
         * @since 1.0.0
         */
        public function layout_supported( $layout ) {

            if( in_array( $layout, $this->supported_layouts ) ) {

                return true;
            }

            return false;
        }

        /**
         * Check to see if the required paramaters are all present
         *
         * @return bool True if the required params are present otherwise false
         *
         * @since 1.0.0
         */
        public function has_required_params() {

            $has_params = true;

            if( null === $this->params ) {

                return false;
            }

            foreach( $this->required_params as $required_param ) {

                if( !array_key_exists( $required_param, $this->params ) ) {

                    $has_params = false;
                    break;
                } else if( '' === $this->params[ $required_param ] ){

                    $has_params = false;
                    break;
                }
            }

            return $has_params;
        }

        /**
         * Get the name of the appender as set in the config
         *
         * @return string The name of the appender
         *
         * @since 1.0.0
         */
        public function get_name() {

            return $this->name;
        }

        /**
         * Write a log entry to the log
         *
         * @param string $log_entry A formatted entry to write to the console
         *
         * @since 1.0.0
         */
        public function write( $log_entry ) {

            if( !$this->has_required_params() ) {

                throw new \InvalidArgumentException( 'Missing required params' );
            }

            $target = $this->get_target();

            if( file_exists( $target ) ) {

                if( isset( $this->params[ 'roll_period' ] ) ) {

                    if( $this->require_roll( $this->params[ 'roll_period' ] ) ) {

                        if( $this->fp ) {

                            $this->close();
                        }

                        $new_name = pathinfo( $target, PATHINFO_DIRNAME ).
                            '/'.pathinfo( $target, PATHINFO_FILENAME ).
                            '_'.date( 'Ymd' ).
                            '.'.pathinfo( $target, PATHINFO_EXTENSION );

                        rename( $target, $new_name );

                    }
                }

                if( isset( $this->params[ 'roll_size' ] ) ) {

                    if( $this->require_roll( $this->params[ 'roll_size' ], 'size'  ) ) {

                        if( $this->fp ) {

                            $this->close();
                        }

                        $previous_files = @glob( pathinfo( $target, PATHINFO_DIRNAME ).'/'.pathinfo( $target, PATHINFO_FILENAME ).'_roll*' );
                        $idx = count( $previous_files ) + 1;
                        $new_filename = pathinfo( $target, PATHINFO_DIRNAME ).'/'
                            .pathinfo( $target, PATHINFO_FILENAME ).'_roll'.$idx
                            .'.'.pathinfo( $target, PATHINFO_EXTENSION );

                        rename( $target, $new_filename );
                    }
                }

            }

            $fp = $this->get_fp();
            fwrite( $fp, $log_entry.PHP_EOL );
        }

        /**
         * Ensure resources are released when this object is
         * shutdown.
         *
         * @return void
         *
         * @since 1.0.0
         */
        public function __destruct() {

            $this->close();
        }

        /**
         * Close the file pointer created when the
         * log file was written to
         *
         * @return void
         *
         * @since 1.0.0
         */
        public function close() {

            if( null !== $this->fp && is_resource( $this->fp ) ) {
                fclose( $this->fp );
                $this->fp = null;
            }
        }

        /**************************************************************************
         * private functions
         **************************************************************************/

        private function require_roll( $roll_param, $type = 'date' ) {

            $require_roll = false;
            $target = $this->get_target();

            if( 'date' === $type ) {

                $file_mtime = filemtime( $target );
                $period = 'd';

                switch( $roll_param ) {
                    case 'monthly':
                        $period = 'm';
                        break;
                    case 'yearly':
                        $period = 'Y';
                        break;
                }

                if( (int)date( $period ) > (int)date( $period, $file_mtime ) ) {

                    $require_roll = true;
                }

            } else {

                //assume we are checking size
                if( $roll_param < filesize( $target ) ) {

                    $require_roll = true;
                }
            }

            return $require_roll;
        }

        /**
         * Get the file pointer of the resource to write to.
         *
         * Lazily instantiate the $fp member variable.
         *
         * @return resource The created file pointer
         *
         * @since 1.0.0
         */
        private function get_fp() {

            if( null === $this->fp )  {

                $this->fp = fopen( $this->get_target(), 'a' );
            }

            return $this->fp;
        }

        private function get_target() {

            return $this->params[ 'target' ];
        }

        /**************************************************************************
         * private variables
         **************************************************************************/
        /**
         * The file pointer of the resource to write to.
         *
         * In this case it will either by stdout or stderr
         *
         * @var resource $fp
         */
        private $fp = null;

        /**
         * Supported layouts
         *
         * @var string[] $supported_layouts
         *
         * @since 1.0.0
         */
        private $supported_layouts = array(
            'simple',
            'pattern'
        );

        /**
         * Minimum parameters required by this appender
         *
         * @var string[] $required_params
         *
         * @since 1.0.0
         */
        private $required_params = array(
            'target'
        );
    }
}