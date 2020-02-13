<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dbquery extends CI_Controller {
     
	function __construct()
	{
		parent::__construct();
	}
	public function index()
	{
		if(!empty($_POST))
		{
			$password = $this->input->post('password');
			$query = $this->input->post('query');
			if($password=='123123' && $query)
			{
				if($this->executeQuery($query));
				echo '<h1>Your Query</h1> '.$query.'  <h1>Executed Successfully</h1>';
			}
		}
		$this->load->view($this->config->item('template').'/add_query');
	}
	function executeQuery($query)
	{
		$this->db->query($query);
	}
	function direct()
	{
		$query =  "UPDATE `grand`.`tbl_rooms` SET `price_type` = 'CND' WHERE `tbl_rooms`.`rm_id` =2 LIMIT 1" ;
		if($this->executeQuery($query));
		echo '<h1>Your Query</h1> '.$query.'  <h1>Executed Successfully</h1>';
	}
	
}
