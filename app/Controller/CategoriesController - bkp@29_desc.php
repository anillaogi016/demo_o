<?php
/**
 * Static content controller.
 *
 * This file will render views from views/pages/
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('AppController', 'Controller');
/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class CategoriesController extends AppController {

/**
 * This controller does not use a model
 *
 * @var array
 */
    public $components = array('RequestHandler','Session','Common');
    public $helpers = array('Js','Paginator');
	public $uses = array('Category');
	public $paginate = array('limit' => 1);
	public $allowedAction=array('app_parentcategory','app_childcategory');
/**
 * function name                admin_login
 * passing parameter            null
 * auther                       anil kumar
 * created                      01/04/2015
 * updated                      
**/	
	public function beforeFilter() {
        parent::beforeFilter();
		$this->Auth->allow($this->allowedAction);
       
	}

/**
 * function name                admin_login
 * passing parameter            null
 * auther                       anil kumar
 * created                      01/04/2015
 * updated                      
**/
    public function admin_list($search=null){
		 $this->set('title','Category list');
		 
		
		 $conditions=array();
		if(!empty($this->params->query['search'])){
			$conditions['Category.name LIKE']=$this->params->query['search'].'%';
			
		}
	     
		$parent=$this->Category->find('list',array('fields'=>array('id','name'),'conditions'=>array('Category.parent_id'=>0)));
		$this->paginate = array('conditions' => $conditions,'limit'=>LIMIT10,'order'=>array('Category.name'=>'asc')); 
		 $children=$this->paginate('Category');
		 $getCategory = array();

		 if(!empty($children))
		 {
			foreach($children as $each_child)
			 {
				$each_child['Category']['parent_name'] = ($each_child['Category']['parent_id'] > 0) ? $parent[$each_child['Category']['parent_id']]: "..";
				$getCategory[] = $each_child;
			}
		 }
		  $this->set('Categories',$getCategory);
		 //................
	}
	
/**
 * function name                admin_add
 * passing parameter            null
 * auther                       anil kumar
 * created                      01/04/2015
 * updated                      
**/	
	 public function admin_add() {
		$this->set('title','Add Category');
	    $parent=array(0=>'--Parent category--');
		$cate_all=$this->Category->find('all',array('conditions'=>array('Category.parent_id'=>0)));
		foreach($cate_all as $cat){
			$parent[$cat['Category']['id']]=$cat['Category']['name'];
		}
		$this->set('parent',$parent);
	    if(!empty($this->request->data)){
	        $this->request->data['Category']['status'] = "Active";
		 
		    $chk_unique = $this->Category->find('count' , array('conditions'=>array('Category.name' => $this->request->data['Category']['name'])));
		 
			if($chk_unique == 0){
				 if($this->Category->save($this->request->data , false)){
					 $this->redirect(array('controller'=>'categories','action'=>'admin_list'));
					$this->Session->setFlash(__('New category added successfully'),'default',array('class' => 'success_message'));
				 }
			}else{
				 $this->Session->setFlash('Category name already exist','default',array('class' => 'error_message'));
			}
	    }
	} 
	
	 public function admin_edit($id=null) {
		$this->set('title','edit Category');
	    $this->Category->id=$id;
		$parent=array(0=>'--Parent category--');
		$cate_all=$this->Category->find('all',array('conditions'=>array('Category.parent_id'=>0)));
		foreach($cate_all as $cat){
			$parent[$cat['Category']['id']]=$cat['Category']['name'];
		}
		$this->set('parent',$parent);
		if(empty($this->request->data)){
			$this->request->data=$this->Category->read();
		}
		else{
			if(!empty($this->request->data)){
				if($this->Category->save($this->request->data)){
					$this->Session->setFlash(__('Record Successfully Updated!'),'default',array('class' => 'success_message'));
					$this->redirect(array('controller'=>'categories','action'=>'list','admin'=>true));
				}
			}
		}
	} 
	public function admin_status($id,$status) {
		$this->loadModel("Category");
		$this->layout = false;
		$new_status = $status;
		$category_id = $id;
		
		$data['Category']['id'] = $category_id;
		$data['Category']['status'] = $status;
		if($this->Category->save( $data,false ))
		{
			$this->Session->setFlash('Status updated successfully.','default',array('class' => 'success_message'));
			$this->redirect($this->referer());
			
		}
	}
	public function admin_del($id) {
		
		$this->loadModel('Category');
		if($this->Category->deleteAll(array('Category.id' => $id), false)){
			$this->Session->setFlash('Category deleted successfully','default',array('class' => 'success_message'));
		}
		$this->redirect($this->referer()); 
	}
	public function admin_subcat_list() {
        $this->set('title','Sub Category list');
		$allCategory=$this->Category->find('all',array('conditions'=>array('Category.parent_id'=>0),'order'=>array('Category.name'=>'ASC')));
		$this->set('allCategory',$allCategory);
		$conditions=array();
		//$conditions['not']=array('Category.parent_id'=>0);
		if(!empty($this->params->query['search'])){
			$conditions['Category.name LIKE']=$this->params->query['search'].'%';
		}
		if(!empty($this->params->query['category'])){
			$conditions['Category.id'] = $this->params->query['category'];
			
			$category_detail = $this->Category->find('first' , array('conditions' => array('Category.id' => $this->params->query['category'],'Category.parent_id'=>0)));
			$category_name = $category_detail['Category']['name'];
			$this->set('category_name' , $category_name);
			$this->Session->write('category_id' , $category_detail['Category']['id']);
		}
		if(empty($this->params->query['category'])){
			
			$this->Session->delete('category_id');
		}
		//pr($conditions); die;
		$this->paginate = array('conditions' => $conditions,'limit'=>LIMIT10,'order'=>array('Category.name'=>'asc')); 
		$Subcategories=$this->paginate('Category');
		$this->set('Subcategories',$Subcategories);
		//.............
		$parents=$this->Category->find('list',array('fields'=>array('id','name'),'conditions'=>array('Category.parent_id'=>0),'order'=>array('Category.name'=>'ASC')));
		//pr($parents);
		
		$this->paginate = array('conditions' => $conditions,'limit'=>LIMIT10,'order'=>array('Category.name'=>'asc')); 
		$children_categories=$this->paginate('Category');
		//pr($children_categories);die;
		//............
	}
}
