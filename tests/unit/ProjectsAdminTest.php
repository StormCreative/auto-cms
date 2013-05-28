<?php
use Codeception\Util\Stub;

class projectsAdminTest extends \Codeception\TestCase\Test
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
    private $_data = array ( "projects" => array ( "title" => "Unit Test Data", "content" => "Unit Test Data", "type" => "Unit Test Data", "tag" => "Unit Test Data" ) );

    protected function _before()
    {
        $this->_model = new projects_model();
    }

    protected function _after() {}

    public function testInstantiation ()
    {
        $this->assertInstanceOf ( 'projects_model', $this->_model );
    }

    public function testSaveWithoutTitle() {
                unset ( $this->_data[ "projects" ][ "title" ] );
                $this->assertFalse( $this->_model->save ( $this->_data[ "projects" ] ) );
           }
           public function testSaveWithoutContent() {
                unset ( $this->_data[ "projects" ][ "content" ] );
                $this->assertFalse( $this->_model->save ( $this->_data[ "projects" ] ) );
           }
           public function testSaveWithoutType() {
                unset ( $this->_data[ "projects" ][ "type" ] );
                $this->assertFalse( $this->_model->save ( $this->_data[ "projects" ] ) );
           }
           public function testSaveWithoutTag() {
                unset ( $this->_data[ "projects" ][ "tag" ] );
                $this->assertFalse( $this->_model->save ( $this->_data[ "projects" ] ) );
           }
           

    public function testSaveSuccessful ()
    {
        $this->assertTrue ( $this->_model->save ( $this->_data[ 'projects' ] ) );
    }
}

?>