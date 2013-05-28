<?php
use Codeception\Util\Stub;

class contactAdminTest extends \Codeception\TestCase\Test
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
    private $_data = array ( "contact" => array ( "name" => "Unit Test Data", "email" => "Unit Test Data", "phone" => "Unit Test Data", "postcode" => "Unit Test Data" ) );

    protected function _before()
    {
        $this->_model = new contact_model();
    }

    protected function _after() {}

    public function testInstantiation ()
    {
        $this->assertInstanceOf ( 'contact_model', $this->_model );
    }

    public function testSaveWithoutName() {
                unset ( $this->_data[ "contact" ][ "name" ] );
                $this->assertFalse( $this->_model->save ( $this->_data[ "contact" ] ) );
           }
           public function testSaveWithoutEmail() {
                unset ( $this->_data[ "contact" ][ "email" ] );
                $this->assertFalse( $this->_model->save ( $this->_data[ "contact" ] ) );
           }
           public function testSaveWithoutPhone() {
                unset ( $this->_data[ "contact" ][ "phone" ] );
                $this->assertFalse( $this->_model->save ( $this->_data[ "contact" ] ) );
           }
           public function testSaveWithoutPostcode() {
                unset ( $this->_data[ "contact" ][ "postcode" ] );
                $this->assertFalse( $this->_model->save ( $this->_data[ "contact" ] ) );
           }
           

    public function testSaveSuccessful ()
    {
        $this->assertTrue ( $this->_model->save ( $this->_data[ 'contact' ] ) );
    }
}

?>