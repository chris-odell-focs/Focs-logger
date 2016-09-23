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
     * Write the log data to a table in wordpress
     *
     * @version 1.0.0
     */
    class WordPress_Appender extends Focs_Appender implements IFocs_Appender {

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
         * @param mixed[] $log_entry Formatted as an array with keys matching column names
         *
         * @since 1.0.0
         */
        public function write( $log_entry ) {

            if( !$this->has_required_params() ) {

                throw new \InvalidArgumentException( 'Missing required params' );
            }

            $target = $this->get_target();
            if( $this->prerequisites( $log_entry ) ) {

                $this->write_row_to_target( $target, $log_entry );
            }
        }

        /**
         * Close the appender
         *
         * @return void
         *
         * @since 1.0.0
         */
        public function close() {

            //for the wordpress appender do nothing
        }

        /**************************************************************************
         * private functions
         **************************************************************************/
        /**
         * Convert an array of values into a key/value associative array
         *
         * @param string[] $column_names The column names which will be the keys
         * @param mixed $data_array The array of data values, must match column ordinal
         *
         * @throws \Exception
         *
         * @return mixed[] Asociative array with column names as the keys.
         *
         * @since 1.0.0
         */
        private function convert_to_column_value_pairs( $column_names, $data_array ) {

            if( count( $column_names ) !== count( $data_array ) ) {

                throw new \Exception( 'convert_to_column_value_pairs columns and data values do not match '.print_r( $column_names, true ) );
            }

            return array_combine ( $column_names, $data_array );
        }

        /**
         * Based on the args derive an array with the column format
         *
         * @param mixed $args The parameters in column => value pairs
         *
         * @return string[] An array with %s or %d for the column format
         *
         * @since 1.0.0
         */
        private function get_column_format( $args ) {

            $col_format = array();

            foreach( $args as $arg ) {

                $format = '%s';
                if( is_numeric( $arg ) ) {

                    $format = '%d';
                }

                $col_format[] = $format;
            }

            return $col_format;
        }

        /**
         * Write the log entry to the target table
         *
         * @param string $target The target table in the database
         * @param mixed[] $log_entry The array of values to write.
         *
         * @return void
         *
         * @since 1.0.0
         */
        private function write_row_to_target( $target, $log_entry ) {

            global $wpdb;

            $wpdb->insert(
                $target,
                $this->convert_to_column_value_pairs( $this->params[ 'columns' ], $log_entry ),
                $this->get_column_format( $log_entry )
            );
        }

        /**
         * Check the pre-requisites before a write
         *
         * 1. That the table exists
         * 2. That the log entry matches up with the number of columns in the target table
         *
         * @param mixed[] $log_entry Array of values to log
         *
         * @return bool True if there prequisites are in place
         *
         * @since 1.0.0
         */
        private function prerequisites( $log_entry ) {

            $have_prereqs = true;
            try{
                $this->ensure_target_exists();
            }
            catch( \Exception $e ) {

                $have_prereqs = false;
            }
            return $have_prereqs;
        }

        /**
         * Ensure that the target table exists.
         *
         * If the target table doesn't try and create it.
         * First see if there is a table_def param and use that if not
         * base the table on the number of columns provided.
         *
         * @return void.
         *
         * @since 1.0.0
         */
        private function ensure_target_exists() {

            global $wpdb;

            $target = $this->get_target();

            if( $wpdb->get_var( "SHOW TABLES LIKE '".$target."'" ) != $target ) {

                $table_def = isset( $this->params[ 'table_def' ] ) ? $this->params[ 'table_def' ] : '';
                if( '' === $table_def ) {

                    $charset_collate = $wpdb->get_charset_collate();

                    $columns = 'ID int NOT NULL AUTO_INCREMENT,'.
                        'message text NOT NULL,'.
                        'exception_message text NOT NULL,'.
                        'date_time datetime NOT NULL,'.
                        'level text NOT NULL,';

                    $table_def = "CREATE TABLE $target (".
                          $columns.
                          " UNIQUE KEY ".strtoupper( $this->get_target() )."_ID (ID)
                            ) $charset_collate;";

                }

                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                dbDelta( $table_def );
            }
        }

        /**
         * The target for the WordPress apender this will be the
         * table name
         *
         * @return mixed
         */
        private function get_target() {

            return $this->params[ 'target' ];
        }

        /**************************************************************************
         * private variables
         **************************************************************************/

        /**
         * Supported layouts
         *
         * @var string[] $supported_layouts
         *
         * @since 1.0.0
         */
        private $supported_layouts = array(
            'column'
        );

        /**
         * Minimum parameters required by this appender
         *
         * The Target is the tablename and the columns are the columns in the table
         *
         * @var string[] $required_params
         *
         * @since 1.0.0
         */
        private $required_params = array(
            'target',
            'columns'
        );
    }
}