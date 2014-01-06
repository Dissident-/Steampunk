<?php
namespace App;

/**
 * Base controller
 *
 * @property-read \App\Pixie $pixie Pixie dependency container
 */
class Page extends \PHPixie\Controller {
	
	protected $view;
	
	public function before() {
		if($this->request->get('ajax'))
		{
			$this->view = $this->pixie->view('blank');
		}
		else
		{
			$this->view = $this->pixie->view('index');
		}
	}
	
	public function after() {
	
		$this->view->logged_in = $this->logged_in(null, false);
		$this->view->has_role = array('Admin' => $this->logged_in('Admin'));
	
		$this->response->body = $this->view->render();
	}
	
	protected function logged_in($role = null, $redirect = false){
        if($this->pixie->auth->user() == null){
            if(strpos($_SERVER['REQUEST_URI'], '/login') === false && $redirect)   $this->redirect('/login');
            return false;
        }
 
        if($role !== null && !$this->pixie->auth->has_role($role)){
            //$this->response->body = "You don't have the permissions to access this page";
            //$this->execute=false;
            return false;
        }
 
        return true;
    }
	
	
	
	
}
