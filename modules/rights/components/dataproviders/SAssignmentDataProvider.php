<?php
/**
 * Class SAssignmentDataProvider
 * This class is futher customized to fit into shopbay use. 
 *
 * Restrict to select user with Administrator role only
 * 
 * @author kwlok
 */
class SAssignmentDataProvider extends CActiveDataProvider
{
	/**
	 * @property RAuthorizer
	 */
	private $_authorizer;

	/**
	 * Constructor.
	 * (e.g. <code>Post::model()</code>, <code>Post::model()->published()</code>).
	 * @param array $config configuration (name=>value) to be applied as the initial property values of this class.
	 */
	public function __construct($config=array())
	{
		$module = Rights::module();
		$userClass = $module->userClass;
		//---- BEGIN CUSTOMIZATION ----
		//Changes: restrict to admin only
		parent::__construct($userClass::model()->admin(true), $config);

		$this->_authorizer = $module->getAuthorizer();
	}

	/**
	 * Fetches the data from the persistent data storage.
	 * @return array list of data items
	 */
	protected function fetchData()
	{
		$data = parent::fetchData();

		foreach( $data as $model )
			$this->_authorizer->attachUserBehavior($model);

		return $data;
	}
}