<?php
/**
 * Install controller class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @since 0.5
 */
class InstallController extends Controller
{
	/**
	 * Creates and renders a new version of a specific image.
	 * @param integer $id the image id.
	 * @param string $version the name of the image version.
	 * @throws CHttpException if the requested version is not defined.
	 */
	public function actionCreate($id,$version)
	{
		$versions=Yii::app()->image->versions;
		if(isset($versions[$version]))
		{
			$thumb=Yii::app()->image->createImageVersion($id,$version);
			$thumb->render();
		}
		else
			throw new CHttpException(404,Img::t('error','Failed to create image! Version is unknown.'));
	}

	/**
	 * Displays the install page.
	 * @param integer $confirm whether the install has been confirmed.
	 */
	public function actionIndex($confirm=null)
	{
		$installer=$this->module->installer;

		if($installer->isInstalled===false || (bool)$confirm===true)
		{
			try
			{
				$installer->run();
				$this->redirect(array('ready'));
			}
			catch(CException $e)
			{
				$this->redirect(array('error'));
			}
		}
		else
			$this->redirect(array('confirm'));

		$this->render('index');
	}

	/**
	 * Displays the confirm page.
	 */
	public function actionConfirm()
	{
		$this->render('confirm');
	}

	/**
	 * Displays the install ready page.
	 */
	public function actionReady()
	{
		$this->render('ready');
	}

	/**
	 * Displays the error page.
	 */
	public function actionError()
	{
		$this->render('error');
	}
}
