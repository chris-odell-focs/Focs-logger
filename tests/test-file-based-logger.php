<?php

class TestFileBasedLogger extends WP_UnitTestCase {

    private $logger;
    private $log_file;
    private $log_name;
    private $rolled_filename;
    private $rolled_size_filename;

    function setup() {
        
        $this->log_name = 'default.log';
        $this->log_file = dirname( __FILE__ ).'/../default.log';

        $this->rolled_filename = pathinfo( $this->log_file, PATHINFO_DIRNAME ).
                            '/'.pathinfo( $this->log_file, PATHINFO_FILENAME ).
                            '_'.date( 'Ymd' ).
                            '.'.pathinfo( $this->log_file, PATHINFO_EXTENSION );
        
    }

    function teardown() {
        
        if( file_exists( $this->log_file ) ) {
         
            unlink( $this->log_file );
        }

         if( file_exists( $this->rolled_filename ) ) {
            
             unlink( $this->rolled_filename );
         }

         if( file_exists( $this->rolled_size_filename ) ) {
            
             unlink( $this->rolled_size_filename );
         }
    }

    function test_should_create_logger() {
    
        $this->logger = Focs_Logger::get_instance( array() );
        $this->assertTrue( !empty( $this->logger ), 'Logger was not created' );
    }

    function test_should_create_default_log_with_simple_pattern() {
        
        $this->logger = Focs_Logger::get_instance( array() );
        $this->logger->info( 'test file based logger' );

        $this->assertTrue( file_exists( $this->log_file ), 'log file does not exist' );

        $file_content = file_get_contents( $this->log_file );
        $this->assertTrue( false !== strpos( $file_content, date( 'Y-m-d G:i:s' ) ), 'date not found' );
        $this->assertTrue( false !== strpos( $file_content, FOCSLO_INFO ), 'info keyword not found' );
        $this->assertTrue( false !== strpos( $file_content, 'test file based logger' ), 'message does not exist' );
        
    }

    function test_should_support_pattern_layout() {
        
        $config = array(
          'layout' => array(
                'type' => 'pattern',
                'params' =>array( 'pattern' => '%message' )
           )
        );

        $this->logger = Focs_Logger::get_instance( $config );
        $this->logger->info( 'test file based logger' );

        $this->assertTrue( file_exists( $this->log_file ), 'log file does not exist' );

    }

    function test_should_have_target_param() {
        
        $config = array(
          'layout' => array(
                'type' => 'pattern',
                'params' =>array( 'pattern' => '%message' )
           ),
           'appender' => array(
                'type' => 'file',
                'params' => array( 'target' =>  $this->log_file )
           )
        );

        $this->logger = Focs_Logger::get_instance( $config );        
        $this->logger->info( 'test file based logger' );

        $this->assertTrue( file_exists( $this->log_file ), 'log file does not exist' );

    }

    function test_should_roll_daily() {
        
        $config = array(
          'layout' => array(
                'type' => 'pattern',
                'params' =>array( 'pattern' => '%message' )
           ),
           'appender' => array(
                'type' => 'file',
                'params' => array( 
                        'target' =>  $this->log_file,
                        'roll_period' => 'daily'
                 )
           )
        );

        $this->logger = Focs_Logger::get_instance( $config );        
        $this->logger->info( 'test file based logger' );

        touch( $this->log_file, strtotime( '-1 day' ) );

        $this->logger->info( 'test file based logger' ); 

        $this->assertTrue( file_exists( $this->rolled_filename ), 'rolled log file does not exist' );

    }

    function test_should_roll_monthly() {
        
        $config = array(
          'layout' => array(
                'type' => 'pattern',
                'params' =>array( 'pattern' => '%message' )
           ),
           'appender' => array(
                'type' => 'file',
                'params' => array( 
                        'target' =>  $this->log_file,
                        'roll_period' => 'monthly'
                 )
           )
        );

        $this->logger = Focs_Logger::get_instance( $config );        
        $this->logger->info( 'test file based logger' );

        touch( $this->log_file, strtotime( '-1 month' ) );

        $this->logger->info( 'test file based logger' ); 

        $this->assertTrue( file_exists( $this->rolled_filename ), 'rolled log file does not exist' );

    }

    function test_should_not_roll_when_monthly_but_file_is_one_day_old() {
        
        $config = array(
          'layout' => array(
                'type' => 'pattern',
                'params' =>array( 'pattern' => '%message' )
           ),
           'appender' => array(
                'type' => 'file',
                'params' => array( 
                        'target' =>  $this->log_file,
                        'roll_period' => 'monthly'
                 )
           )
        );

        $this->logger = Focs_Logger::get_instance( $config );        
        $this->logger->info( 'test file based logger' );

        touch( $this->log_file, strtotime( '-1 day' ) );

        $this->logger->info( 'test file based logger' ); 

        $this->assertTrue( !file_exists( $this->rolled_filename ), 'rolled log file exists but should not' );

    }

    function test_should_roll_yearly() {
        
        $config = array(
          'layout' => array(
                'type' => 'pattern',
                'params' =>array( 'pattern' => '%message' )
           ),
           'appender' => array(
                'type' => 'file',
                'params' => array( 
                        'target' =>  $this->log_file,
                        'roll_period' => 'yearly'
                 )
           )
        );

        $this->logger = Focs_Logger::get_instance( $config );        
        $this->logger->info( 'test file based logger' );

        touch( $this->log_file, strtotime( '-1 year' ) );

        $this->logger->info( 'test file based logger' ); 

        $this->assertTrue( file_exists( $this->rolled_filename ), 'rolled log file does not exist' );

    }

    function test_should_roll_size() {
        
        $config = array(
          'layout' => array(
                'type' => 'pattern',
                'params' =>array( 'pattern' => '%message' )
           ),
           'appender' => array(
                'type' => 'file',
                'params' => array( 
                        'target' =>  $this->log_file,
                        'roll_size' => 1000
                 )
           )
        );

        $this->logger = Focs_Logger::get_instance( $config );

        $this->rolled_size_filename = $this->get_rolled_by_size_name();

        $log_string = str_pad( 'xx', 2000, '@' );
        $this->logger->info( $log_string );
        $this->logger->info( 'test file appender' );

        $this->assertTrue( file_exists( $this->rolled_size_filename ), 'rolled log file does not exist' );
    }

    function test_should_have_date_level_message_with_simple_layout() {
        
        $config = array(
          'layout' => array(
                'type' => 'simple'
           )
        );

        $message = 'test file appender';

        $this->logger = Focs_Logger::get_instance( $config );
        $this->logger->info( $message );

        $log_content = file_get_contents( $this->log_file );

        $date_test = date( 'Y-m-d' );
        $level_test = 'info';

        $this->assertTrue( false !== strpos( $log_content, $message ), 'message was not found in log file' );
        $this->assertTrue( false !== strpos( $log_content, $date_test ), 'date was not found in log file' );
        $this->assertTrue( false !== strpos( strtolower( $log_content ), $level_test ), 'level was not found in log file' );
    }

    function test_should_have_date_with_date_pattern() {
        
        $config = array(
          'layout' => array(
                'type' => 'pattern',
                'params' =>array( 'pattern' => '%date' )
           )
        );

        $this->logger = Focs_Logger::get_instance( $config );
        $this->logger->info( 'test file appender' );
        $log_content = file_get_contents( $this->log_file );

        $date_test = date( 'Y-m-d' );
        $this->assertTrue( false !== strpos( $log_content, $date_test ), 'date was not found in log file' );
    }

    function test_should_have_level_with_level_pattern() {
        
        $config = array(
          'layout' => array(
                'type' => 'pattern',
                'params' =>array( 'pattern' => '%level' )
           )
        );

        $this->logger = Focs_Logger::get_instance( $config );
        $this->logger->info( 'test file appender' );
        $log_content = file_get_contents( $this->log_file );

        $level_test = 'info';
        $this->assertTrue( false !== stripos( $log_content, $level_test ), 'level was not found in log file' );

    }

    function test_should_have_message_with_message_pattern() {
        
        $config = array(
          'layout' => array(
                'type' => 'pattern',
                'params' =>array( 'pattern' => '%message' )
           )
        );

        $message = 'test file appender';

        $this->logger = Focs_Logger::get_instance( $config );
        $this->logger->info( $message );
        $log_content = file_get_contents( $this->log_file );

        $this->assertTrue( false !== stripos( $log_content, $message ), 'message was not found in log file' );

    }

    function test_should_have_exception_with_exception_pattern() {
        
        $config = array(
          'layout' => array(
                'type' => 'pattern',
                'params' =>array( 'pattern' => '%exception' )
           )
        );

        $message = 'test exception';

        $this->logger = Focs_Logger::get_instance( $config );
        $this->logger->info( 'test file appender', new \Exception( $message ) );
        $log_content = file_get_contents( $this->log_file );

        $this->assertTrue( false !== stripos( $log_content, $message ), 'exception was not found in log file' );

    }

    function test_should_support_custom_pattern() {
        
        $config = array(
          'layout' => array(
                'type' => 'pattern',
                'params' =>array( 
                    'pattern' => '%blurg',
                    'custom_pattern_callbacks' => array(
                        array( $this, 'custom_pattern' )
                     )
                 )                
           )
        );

        $message = 'blurg_replacement';

        $this->logger = Focs_Logger::get_instance( $config );
        $this->logger->info( 'test file appender' );
        $log_content = file_get_contents( $this->log_file );

        $this->assertTrue( false !== stripos( $log_content, $message ), 'custom pattern was not found in log file' );
    }

    function test_should_not_log_when_under_threshold() {
        
        //logger set at logging_level info by default

        $this->logger = Focs_Logger::get_instance( array() );
        $this->logger->info( 'test file based logger - info log' );
        $this->logger->trace( 'test file based logger - trace log' );

        $log_content = file_get_contents( $this->log_file );
        $this->assertTrue( false !== stripos( $log_content, 'info log' ), 'info was not found in logfile' );
        $this->assertTrue( false === stripos( $log_content, 'trace log' ), 'trace was found in logfile but should not have been' );
    }

    function test_should_log_when_under_threshold_but_level_is_warn_or_fatal() {
        
        $this->logger = Focs_Logger::get_instance( array() );
        $this->logger->info( 'test file based logger - info log' );
        $this->logger->warn( 'test file based logger - warn log' );

        $log_content = file_get_contents( $this->log_file );
        $this->assertTrue( false !== stripos( $log_content, 'info log' ), 'info was not found in logfile' );
        $this->assertTrue( false !== stripos( $log_content, 'warn log' ), 'warn was not found in logfile' );

    }

    function test_should_throw_exception_when_no_required_params() {
        
        $config = array(
          'appender' => array(
                'type' => 'file',
                'params' => array( 
                        'target' =>  ''
                 )
           )
        );

        $this->logger = Focs_Logger::get_instance( $config );
        $have_exception = false;
        try{
        
            $this->logger->info( 'test file appender' );
        } catch( \Exception $e ) {
            
            if( false !== strpos( $e->getMessage(),  'does not have the required parameters' ) ) {
            
                $have_exception = true;
            }
        }

        $this->assertTrue( $have_exception, 'Exception not raised and should have been' );
    }

    function test_should_throw_exception_when_layout_not_supported() {
    
        $config = array(
          'layout' => array(
                'type' => 'blurg',
                'params' =>array( 
                    'pattern' => '%blurg'
                 )                
           )
        );

        $this->logger = Focs_Logger::get_instance( $config );
        $have_exception = false;
        try{
            
            $this->logger->info( 'test file appender' );
        }
        catch( \Exception $e ) {
            
            if( false !== strpos( $e->getMessage(),  'does not support' ) ) {
                
                $have_exception = true;
            }
        }

        $this->assertTrue( $have_exception, 'Exception not raised and should have been' );

    }

    function test_should_have_trace_keyword() {
        
        $this->logger = Focs_Logger::get_instance( array( 'logging_level' => FOCSLO_TRACE ) );
        $this->logger->trace( 'test file based logger - trace log' );

        $log_content = file_get_contents( $this->log_file );
        $this->assertTrue( false !== stripos( $log_content, 'trace log' ), 'trace was not found in logfile' );
        
    }

    function test_should_have_debug_keyword() {
        
        $this->logger = Focs_Logger::get_instance( array( 'logging_level' => FOCSLO_DEBUG ) );
        $this->logger->debug( 'test file based logger - debug log' );

        $log_content = file_get_contents( $this->log_file );
        $this->assertTrue( false !== stripos( $log_content, 'debug log' ), 'debug was not found in logfile' );
    }

    function test_should_have_info_keyword() {
        
        $this->logger = Focs_Logger::get_instance( array( 'logging_level' => FOCSLO_INFO ) );
        $this->logger->info( 'test file based logger - info log' );

        $log_content = file_get_contents( $this->log_file );
        $this->assertTrue( false !== stripos( $log_content, 'info log' ), 'info was not found in logfile' );

    }

    function test_should_have_warn_keyword() {
        
        $this->logger = Focs_Logger::get_instance( array( 'logging_level' => FOCSLO_WARN ) );
        $this->logger->warn( 'test file based logger - warn log' );

        $log_content = file_get_contents( $this->log_file );
        $this->assertTrue( false !== stripos( $log_content, 'warn log' ), 'warn was not found in logfile' );

    }

    function test_should_have_fatal_keyword() {
        
        $this->logger = Focs_Logger::get_instance( array( 'logging_level' => FOCSLO_FATAL ) );
        $this->logger->fatal( 'test file based logger - fatal log' );

        $log_content = file_get_contents( $this->log_file );
        $this->assertTrue( false !== stripos( $log_content, 'fatal log' ), 'fatal was not found in logfile' );

    }

    /**
     * Provide a callback to test custom pattern replacement
     * 
     * @param mixed $pattern The passed in pattern
     * @return mixed The pattern with the pattern placeholder replaced
     * 
     */
    public function custom_pattern( $pattern ) {
        
        $pattern = str_replace( '%blurg', 'blurg_replacement', $pattern );
        return $pattern;
    }

    /******************************************************************************
     * private functions
     ******************************************************************************/

    private function get_rolled_by_size_name() {
        
        $previous_files = @glob( pathinfo( $this->log_file, PATHINFO_DIRNAME ).'/'.pathinfo( $this->log_file, PATHINFO_FILENAME ).'_roll*' );
        $idx = count( $previous_files ) + 1;
        
        return pathinfo( $this->log_file, PATHINFO_DIRNAME ).'/'
            .pathinfo( $this->log_file, PATHINFO_FILENAME ).'_roll'.$idx
            .'.'.pathinfo( $this->log_file, PATHINFO_EXTENSION );

    }
}