<?php 
namespace App\Controller;
class Auth extends \App\Page{

	public function before() {
		if($this->request->get('ajax'))
		{
			if($this->request->get('target') == 'body')
			{
				$this->view = $this->pixie->view('body');
			}
			else
			{
				$this->view = $this->pixie->view('blank');
			}
		}
		else
		{
			$this->view = $this->pixie->view('index');
		}
		$this->view->errors = '';
		$this->view->post = $this->request->post();
	}

	public function action_register()
	{
	
		if($this->request->method == 'POST')
		{
		
			$validate = $this->pixie->validate->get($this->request->post());
			$validate->field('Username')->rules('filled', 'alpha_numeric')->error('Username must be alphanumeric');
			$pixie = $this->pixie;
			$validate->field('Username', true)->rule('callback', function($value) use ($pixie){
				return $pixie->orm->get('Account')->where('Username', $value)->count_all() === 0 ? false : true;
			})->error('Username is already taken.'); // Should probably go case insensitive
			$validate->field('Password')->rules('filled')->rule('min_length', 8)->error('Password must be at least 8 characters long');
			$validate->field('EmailAddress')->rules('filled', 'email')->error('Valid email address required');
			
			if($validate->valid())
			{
				$post = $this->request->post();
			
				$hashed = $this->pixie->auth->provider('password')->hash_password($post['Password']);
				
				$account = $this->pixie->orm->get('Account');
				$account->Username = $post['Username'];
				$account->Password = $hashed;
				$account->EmailAddress = $post['EmailAddress'];
				$account->save();
				$this->view->subview = 'account/registered';
			}
			else
			{
				$this->view->errors = $validate->errors();
				$this->view->subview = 'account/register';
			}
		
		}
		else
		{
		
			$this->view->subview = 'account/register';
		}
	}
	
	
	
    public function action_index(){
        $this->view->subview = 'account/login';
    }
 
    public function action_login() {
        if($this->request->method == 'POST'){
            $login = $this->request->post('Username');
            $password = $this->request->post('Password');
 
            $logged = $this->pixie->auth->provider('password')->login($login, $password);
 
            if ($logged)
			{
                $this->view->subview = 'account/logincomplete';
				return;
			}
			else
			{
				$this->view->errors = array('Unable to log you in with those credentials!');
			}
        }
        $this->view->subview = 'account/login';
    }
 
    public function action_logout() {
		// Yeah, let's avoid an error if you logout twice...
        if($this->pixie->auth->user()) $this->pixie->auth->logout();
        $this->view->subview = 'account/logout';
    }
}