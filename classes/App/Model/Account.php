<?php
namespace App\Model;
class Account extends \PHPixie\ORM\Model{
 
    //Specify the PRIMARY KEY
    public $id_field='AccountID';
 
    //Specify table name
    public $table='account';
 
    //Specify which connection to use
    public $connection = 'default';
	
	protected $has_many=array(
        'Character'=>array(
            'model'=>'Character',
            'key'=>'AccountID'
        ),
		'Role'=>array(
            'model'=>'Role',
			'through'=>'account_role',
            'key'=>'AccountID',
			'foreign_key'=>'RoleID'
        )
    );

	public function RegenerateAuthenticationToken($randomData = null)
	{
		if($randomData === null) $randomData = time().rand();
		$this->AuthToken = null;
		while($this->AuthToken == null || $this->pixie->orm->get('Account')->where('AuthToken', $this->AuthToken)->count_all() > 0)
		{
			$randomData = sha1($randomData);
			$this->AuthToken = sha1(time().rand().$randomData);
		}
		$this->save();
	}
	
}