set_include_path(get_include_path() . PATH_SEPARATOR . 'C:\\Users\\Engin\\Code\\PHP\\WMI\\api\\0.1\\');
require_once "PHPUnit/Extensions/Database/TestCase.php";

require('Models/User.php');

use WMI\Models\User;

const db_uri    = 'mysql:host=localhost;dbname=wmi';
const db_user   = 'root';
const db_pass   = 'test';

class UserTest extends PHPUnit_Extensions_Database_TestCase
{
	protected $u;
	
	/**
	 * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
	 */
	public function getConnection()
	{
		$pdo = new \PDO(db_uri, db_user, db_pass);
		return $this->createDefaultDBConnection($pdo);
	}
	
	/**
	 * @return PHPUnit_Extensions_Database_DataSet_IDataSet
	 */
	public function getDataSet()
	{
	}
	
	protected function setUp()
	{
		$this->u = new User(new \PDO(db_uri, db_user, db_pass));
	}

	public function testInit()
	{
		$this->assertInstanceOf('\\WMI\\Models\\User', $this->u);
	}
	
	public function testSave()
	{
		$this->u->setUsername('engin')->setPassword('Yarak')
		->setEmail('a@b.c')->setFbid(5)->setCompany_id(5)
		->setData('bok')->setRole('ADMIN')->setModified(date('Y-m-d H:i:s'))
		->setCreated(date('Y-m-d H:i:s'))->setLastlogin(date('Y-m-d H:i:s'));
		$this->assertTrue($this->u->save());
	}
}