<?php
use Codeception\Util\Stub;

class stuffAdminTest extends \Codeception\TestCase\Test
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
    private $_data = array ( "stuff" => array ( "title" => "Unit Test Data", "content" => "Unit Test Data", "type" => "Unit Test Data", "tag" => "Unit Test Data" ) );

    protected function _before()
    {
        $this->_model = new stuff_model();
    }

    protected function _after() {}

    public function testInstantiation ()
    {
        $this->assertInstanceOf ( 'stuff_model', $this->_model );
    }

    public function testSaveWithoutTitle() {
                unset ( $this->_data[ "stuff" ][ "title" ] );
                $this->assertFalse( $this->_model->save ( $this->_data[ "stuff" ] ) );
           }
           public function testSaveWithoutContent() {
                unset ( $this->_data[ "stuff" ][ "content" ] );
                $this->assertFalse( $this->_model->save ( $this->_data[ "stuff" ] ) );
           }
           public function testSaveWithoutType() {
                unset ( $this->_data[ "stuff" ][ "type" ] );
                $this->assertFalse( $this->_model->save ( $this->_data[ "stuff" ] ) );
           }
           public function testSaveWithoutTag() {
                unset ( $this->_data[ "stuff" ][ "tag" ] );
                $this->assertFalse( $this->_model->save ( $this->_data[ "stuff" ] ) );
           }
           

    public function testSaveSuccessful ()
    {
        $this->assertTrue ( $this->_model->save ( $this->_data[ 'stuff' ] ) );
    }
}

?>