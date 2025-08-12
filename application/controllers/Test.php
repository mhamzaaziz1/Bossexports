<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('unit_test');
    }

    public function index() {
        echo '<h2>Unit Test Example</h2>';
        
        // Simple test
        $test = 1 + 1;
        $expected_result = 2;
        $test_name = 'Simple addition test';
        $this->unit->run($test, $expected_result, $test_name);
        
        // String test
        $test = 'Hello World';
        $expected_result = 'Hello World';
        $test_name = 'String comparison test';
        $this->unit->run($test, $expected_result, $test_name);
        
        // Boolean test
        $test = true;
        $expected_result = 'is_true';
        $test_name = 'Boolean TRUE test';
        $this->unit->run($test, $expected_result, $test_name);
        
        // Display the test results
        echo $this->unit->report();
    }
}