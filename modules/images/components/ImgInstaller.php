<?php
/**
 * Image installer class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @since 0.5
 */
class ImgInstaller extends CComponent
{
	/**
	 * @property ImgModule the image module.
	 */
	private $_module;
	/**
	 * @property string the image path.
	 */
	private $_imagePath;
	/**
	 * @property string the base path.
	 */
	private $_basePath;
	/**
	 * @var string the image database table name.
	 */
	private $_imageTable='Image';

	/**
	 * Initializes the component.
	 */
	public function init()
	{
		$this->_imagePath=Yii::app()->image->imagePath;
		$this->_basePath=realpath( Yii::app()->basePath.'/../' ).'/';
	}

	/**
	 * Runs the installer.
	 * @throws CException if an error occurs during the installation process.
	 */
	public function run()
	{
		$this->createDirectories();
		$this->executeSchema();

		// We only need to create the .htaccess file
		// if creating of images on-demand is enabled.
		if($this->_module->createOnDemand===true)
			$this->createHtaccessFile();
	}

	/**
	 * Creates the directories required by this module.
	 */
	public function createDirectories()
	{
		$path='';
		$directories=explode('/',trim($this->_imagePath,'/'));
		foreach($directories as $directory)
		{
			if(empty($path))
				$path=$this->_basePath;

			$path.='/'.$directory;
			if(!file_exists($path))
				mkdir($path);
		}

		$versionPath=$path.'/versions';
		if(!file_exists($versionPath))
			mkdir($versionPath);
	}

	/**
	 * Creates the .htaccess-file required for creating images on-demand.
	 * @throws CException if the template file cannot be read or the .htaccess created cannot be created.
	 */
	public function createHtaccessFile()
	{

		// Determine the URL for the rewrite.
		$urlManager=Yii::app()->getUrlManager();
		if($urlManager->getUrlFormat()===CUrlManager::PATH_FORMAT)
		{
			$targetUrl='';

			// Append the script name if necessary.
			if($urlManager->showScriptName===true)
				$targetUrl.='index.php/';

			$targetUrl.='image/default/create?id=$2&version=$1';
		}
		else
			$targetUrl='index.php?r=image/default/create&id=$2&version=$1';

		// Read the template file.
		if(($htaccess=file_get_contents(dirname(__FILE__).'/../files/htaccess'))===false)
			throw new CException(Img::t('error','Failed to create the access file! Template could not be read.'));

		// Replace the placeholders in the template file.
		$htaccess=strtr($htaccess,array(
			'{baseUrl}'=>Yii::app()->getRequest()->getBaseUrl().'/',
			'{sourceUrl}'=>'^versions/([a-zA-Z0-9]+)/([0-9]+)\.(gif|jpg|png)$',
			'{targetUrl}'=>$targetUrl,
		));

		// Create the .htaccess file.
		if((file_put_contents($this->_basePath.$this->_imagePath.$this->_module->accessFileName,$htaccess))===false)
			throw new CException(Img::t('error','Failed to create the access file! File could not be created.'));
	}

	/**
	 * Executes the database schema provided with this module.
	 * @throws CException if the schema cannot be read or if one of the queries fails.
	 */
	public function executeSchema()
	{
		$db=Yii::app()->getDb();

		// Read the schema file.
		if(($schema=file_get_contents(dirname(__FILE__).'/../data/schema.sql'))===false)
			throw new CException(Img::t('error','Failed to execute the database schema! File could not be read.'));

		// We need to append the table prefix if necessary.
		$schema=strtr($schema,array(
			$this->_imageTable=>$db->tablePrefix.$this->_imageTable,
		));

		// Split the schema into separate SQL-statements.
        $schema=preg_split("/;\s*/",trim($schema,';'));

        $trx=$db->beginTransaction();

        try
        {
            foreach($schema as $sql)
                $db->createCommand($sql)->execute();

	        // All statements executed successfully.
	        $trx->commit();
        }
        catch(CException $e)
        {
	        // Something went wrong.
	        $trx->rollback();
	        throw $e;
        }
	}

	/**
	 * Returns whether the image module is already installed.
	 * @return boolean whether the module is installed.
	 */
	public function getIsInstalled()
	{
		try
		{
			$sql="SELECT COUNT(*) FROM {$this->_imageTable}";
			Yii::app()->db->createCommand($sql)->queryScalar();
			return true;
		}
		catch(CDbException $e)
		{
			return false;
		}
	}

	/**
	 * Sets the image module.
	 * @param ImgModule $value the module.
	 */
	public function setModule($value)
	{
		$this->_module=$value;
	}
}
