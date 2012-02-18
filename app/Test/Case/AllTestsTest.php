<?php
/**
 * AllTests file
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Test.Case
 * @since         CakePHP(tm) v 2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('CakeTestLoader', 'TestSuite');
 
/**
 * AllTests class
 *
 * This test group will run all tests.
 *
 * @package       Cake.Test.Case
 */
class AllTests extends PHPUnit_Framework_TestSuite 
{

    /**
    * Suite define the tests for this suite
    */
    public static function suite() 
    {
        $suite = new PHPUnit_Framework_TestSuite('All Tests');
        $path = TESTS . 'Case' . DS;
        $config['app'] = 1;
        $tests = CakeTestLoader::generateTestList($config); 
        foreach ($tests as $test) {
            if ($test != 'AllTests') {
                $suite->addTestFile($path . $test . 'Test.php');
            }
        }
        return $suite;
    }
    
    
}
