<?php

class WpBasedLogger extends WP_UnitTestCase {

    private $logger;
    private $table_name;
    private $anti_table_name;
    private $default_config;

    function setup() {
        
        $this->table_name = 'default_log';
        $this->anti_table_name = 'wrong_default_log';
        
        $columns = array( 'message', 'level', 'exception_message', 'date_time' );
        $this->default_config = array(
            'appender' => array(
                'type' => 'wordpress',
                'params' => array( 'target' => $this->table_name, 'columns' => $columns )
            ),
            'layout' =>array(
                'type' => 'column',
                'params' => array( 'columns' => $columns )
            )
        );

    }

    function teardown() {
        
        global $wpdb;
        $wpdb->query( "DROP TABLE IF EXISTS ".$this->table_name.";" );
        $wpdb->query( "DROP TABLE IF EXISTS ".$this->anti_table_name.";" );
    }

    function test_should_create_default_log_table() {
                
        $columns = array( 'message', 'level', 'exception_message', 'date_time' );

        $config = array(
            'appender' => array(
                'type' => 'wordpress',
                'params' => array( 'target' => $this->table_name, 'columns' => $columns )
            ),
            'layout' =>array(
                'type' => 'column',
                'params' => array( 'columns' => $columns )
            )
        );
        
        $this->logger = Focs_Logger::get_instance( $config );
        $this->logger->info( 'test wp based logger' );

        $this->assertTrue( $this->target_exists(), 'Target table does not exist' );
    }

    function test_should_throw_exception_when_layout_not_supported() {
        
        $columns = array( 'message', 'level', 'exception_message', 'date_time' );

        $config = array(
            'appender' => array(
                'type' => 'wordpress',
                'params' => array( 'target' => $this->table_name, 'columns' => $columns )
            ),
            'layout' => array(
                'type' => 'pattern',
                'params' =>array( 'pattern' => '%message' )
           )
        );
        
        $this->logger = Focs_Logger::get_instance( $config );
        $have_exception = false;
        try{
            
            $this->logger->info( 'test wp appender' );
        }
        catch( \Exception $e ) {
            
            if( false !== strpos( $e->getMessage(),  'does not support' ) ) {
                
                $have_exception = true;
            }
        }

        $this->assertTrue( $have_exception, 'Exception not raised and should have been' );
    }

    function test_should_support_table_def() {
        
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $table_def = "CREATE TABLE $this->table_name (".
                "ID int NOT NULL AUTO_INCREMENT,".
                "message text NOT NULL,".
                "exception_message text NOT NULL,".
                "date_time datetime NOT NULL,".
                "level text NOT NULL,".
            " UNIQUE KEY ".strtoupper( $this->table_name )."_ID (ID)
            ) $charset_collate;";

        $config = array(
            'appender' => array(
                'type' => 'wordpress',
                'params' =>array( 
                    'target' => $this->table_name,
                    'table_def' => $table_def,
                    'columns' => array(                        
                        'message',
                        'level',
                        'exception_message',
                        'date_time'
                     )
                 )
            ),
            'layout' =>array(
                'type' => 'column',
                'params' => array( 
                    'columns' => array(                        
                        'message',
                        'level',
                        'exception_message',
                        'date_time'
                     )                     
                 )
            )
        );
        
        $this->logger = Focs_Logger::get_instance( $config );
        $this->logger->info( 'test wp based logger' );

        $this->assertTrue( $this->target_exists(), 'Target table does not exist' );
    }

    function test_should_throw_exception_when_appender_has_no_columns_defined() {

        $columns = array( 'ID', 'message', 'exception_message', 'date_time', 'level' );

        $config = array(
            'appender' => array(
                'type' => 'wordpress',
                'params' => array( 'target' => $this->table_name )
            ),
            'layout' =>array(
                'type' => 'column',
                'params' => array( 'columns' => $columns )
            )
        );
        
        $this->logger = Focs_Logger::get_instance( $config );
        $have_exception = false;
        try{
            
            $this->logger->info( 'test wp appender' );
        }
        catch( \Exception $e ) {
            
            if( false !== strpos( $e->getMessage(),  'does not have the required parameters' ) ) {
                
                $have_exception = true;
            }
        }

        $this->assertTrue( $have_exception, 'Exception not raised and should have been' );

    }

    function test_should_not_log_when_under_threshold() {
       
        $this->logger = Focs_Logger::get_instance( $this->default_config );

        $this->logger->info( 'test file based logger - info log' );
        $this->logger->trace( 'test file based logger - trace log' );

        $info_row = $this->get_row( "SELECT * FROM $this->table_name" );
        $trace_row = $this->get_row( "SELECT * FROM $this->table_name", OBJECT, 1 );
        
        $this->assertTrue( false !== stripos( $info_row->message, 'info log' ), 'info was not found in logfile' );
        $this->assertTrue( false === stripos( $trace_row->message, 'trace log' ), 'trace was found in logfile but should not have been' );

    }

    function test_should_log_when_under_threshold_but_level_is_warn_or_fatal() {
        
        $this->logger = Focs_Logger::get_instance( $this->default_config );

        $this->logger->info( 'test file based logger - info log' );
        $this->logger->warn( 'test file based logger - warn log' );

        $info_row = $this->get_row( "SELECT * FROM $this->table_name WHERE level='INFO'" );
        $warn_row = $this->get_row( "SELECT * FROM $this->table_name WHERE level='WARN'");

        $this->assertTrue( false !== stripos( $info_row->message, 'info log' ), 'info was not found in logfile' );
        $this->assertTrue( false !== stripos( $warn_row->message, 'warn log' ), 'warn was not found in logfile' );

    }

    function test_should_have_trace_keyword() {
        
        $this->default_config[ 'logging_level' ] = FOCSLO_TRACE;
        $this->logger = Focs_Logger::get_instance( $this->default_config );

        $this->logger->trace( 'test file based logger - trace log' );
        $the_row = $this->get_row( "SELECT * FROM $this->table_name WHERE level='TRACE'" );

        $this->assertTrue( false !== stripos( $the_row->message, 'trace log' ), 'trace was not found in logfile' );        

    }

    function test_should_have_debug_keyword() {
        
        $this->default_config[ 'logging_level' ] = FOCSLO_DEBUG;
        $this->logger = Focs_Logger::get_instance( $this->default_config );

        $this->logger->debug( 'test file based logger - debug log' );
        $the_row = $this->get_row( "SELECT * FROM $this->table_name WHERE level='DEBUG'" );

        $this->assertTrue( false !== stripos( $the_row->message, 'debug log' ), 'debug was not found in logfile' );        

    }

    function test_should_have_info_keyword() {
        
        $this->default_config[ 'logging_level' ] = FOCSLO_INFO;
        $this->logger = Focs_Logger::get_instance( $this->default_config );

        $this->logger->info( 'test file based logger - info log' );
        $the_row = $this->get_row( "SELECT * FROM $this->table_name WHERE level='INFO'" );

        $this->assertTrue( false !== stripos( $the_row->message, 'info log' ), 'info was not found in logfile' );        

    }

    function test_should_have_warn_keyword() {
        
        $this->default_config[ 'logging_level' ] = FOCSLO_WARN;
        $this->logger = Focs_Logger::get_instance( $this->default_config );

        $this->logger->warn( 'test file based logger - warn log' );
        $the_row = $this->get_row( "SELECT * FROM $this->table_name WHERE level='WARN'" );

        $this->assertTrue( false !== stripos( $the_row->message, 'warn log' ), 'warn was not found in logfile' );        

    }

    function test_should_have_fatal_keyword() {
        
        $this->default_config[ 'logging_level' ] = FOCSLO_FATAL;
        $this->logger = Focs_Logger::get_instance( $this->default_config );

        $this->logger->fatal( 'test file based logger - fatal log' );
        $the_row = $this->get_row( "SELECT * FROM $this->table_name WHERE level='FATAL'" );

        $this->assertTrue( false !== stripos( $the_row->message, 'fatal log' ), 'fatal was not found in logfile' );        

    }

    //function test_should_support_custom_columns() {
    
    /*****************************************************
     * future implementation
     *****************************************************/


    //    global $wpdb;
    //    $charset_collate = $wpdb->get_charset_collate();

    //    $table_def = "CREATE TABLE $this->table_name (".
    //            "ID int NOT NULL AUTO_INCREMENT,".
    //            "message text NOT NULL,".
    //            "exception_message text NOT NULL,".
    //            "date_time datetime NOT NULL,".
    //            "level text NOT NULL,".
    //            "blurg text NOT NULL,".
    //        " UNIQUE KEY ".strtoupper( $this->table_name )."_ID (ID)
    //        ) $charset_collate;";

    //    $config = array(
    //        'appender' => array(
    //            'type' => 'wordpress',
    //            'params' =>array( 
    //                'target' => $this->table_name,
    //                'table_def' => $table_def
    //             )
    //        ),
    //        'layout' =>array(
    //            'type' => 'column',
    //            'params' => array( 
    //                'columns' => array( 
    //                    'ID', 
    //                    'message', 
    //                    'exception_message', 
    //                    'date_time', 
    //                    'level',
    //                    'test_col'
    //                 ),
    //                 'custom_column_callbacks' => array(
    //                    array( $this, 'custom_column' )
    //                 )
    //             )
    //        )
    //    );
    
    //    $this->logger = Focs_Logger::get_instance( $config );
    //    $this->logger->info( 'test wp based logger' );

    //    $this->assertTrue( $this->target_exists(), 'Target table does not exist' );

    //    $count = $wpdb->get_var( "SELECT COUNT(*) FROM $this->table_name WHERE test_col='xxxxx'" );

    //    $this->assertTrue( $count == 1, 'Test column value not found' );

    //}

    /******************************************************************************
     * private functions
     ******************************************************************************/

    private function target_exists() {
        
        global $wpdb;

        return $wpdb->get_var( "SHOW TABLES LIKE '".$this->table_name."'" ) == $this->table_name;
    }

    private function get_row( $sql ) {
    
        global $wpdb;

        return $wpdb->get_row( $sql );
    }
  
}