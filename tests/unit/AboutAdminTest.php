<?php
use Codeception\Util\Stub;

class aboutAdminTest extends \Codeception\TestCase\Test
{
   /**
    * @var \CodeGuy
    */
    protected $codeGuy;

    /**
     * @var the model object
     */
    private $_model;

    /**
     * @var some mock data
     */
    private $_data = array ( "about" => array ( "name" => "Unit Test Data", "email" => "Unit Test Data", "phone" => "Unit Test Data", "postcode" => "Unit Test Data" ) );

    protected function _before()
    {
        $this->_model = new about_model();
    }

    protected function _after() {}

    public function testInstantiation ()
    {
        $this->assertInstanceOf ( 'about_model', $this->_model );
    }

    public function testSaveWithoutName() {
                unset ( $this->_data[ "about" ][ "name" ] );
                $this->assertFalse( $this->_model->save ( $this->_data[ "about" ] ) );
           }
           public function testSaveWithoutEmail() {
                unset ( $this->_data[ "about" ][ "email" ] );
                $this->assertFalse( $this->_model->save ( $this->_data[ "about" ] ) );
           }
           public function testSaveWithoutPhone() {
                unset ( $this->_data[ "about" ][ "phone" ] );
                $this->assertFalse( $this->_model->save ( $this->_data[ "about" ] ) );
           }
           public function testSaveWithoutPostcode() {
                unset ( $this->_data[ "about" ][ "postcode" ] );
                $this->assertFalse( $this->_model->save ( $this->_data[ "about" ] ) );
           }
           

    public function testSaveSuccessful ()
    {
        $this->assertTrue ( $this->_model->save ( $this->_data[ 'about' ] ) );
    }
}

?>