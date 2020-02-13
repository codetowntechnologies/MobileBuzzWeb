<?php
class Manager_Model extends CI_Model
{
	var $tablename='manager';	
//	var $tableComment='tbl_blog_comment';
	
	function __construct()
	{
	
	   if (!$this->db->table_exists($this->tablename))
	   {
		   $this->create_table();
	   }
		

	}
	private function create_table()
	{
		//-------------------------- Table Blog ----------------------//
	$this->db->query("
		   CREATE TABLE IF NOT EXISTS `tbl_manager` (
			  `mng_id` int(11) NOT NULL AUTO_INCREMENT,
			  `manager_name` varchar(255) COLLATE latin1_general_ci NOT NULL,
			  `manager_name_italian` varchar(256) COLLATE latin1_general_ci NOT NULL,
			  `parent_id` int(5) NOT NULL DEFAULT '0',
			  `page_set` varchar(256) COLLATE latin1_general_ci NOT NULL,
			  `page_link` varchar(255) COLLATE latin1_general_ci NOT NULL DEFAULT 'underconstructions.php',
			  `display_order` int(5) NOT NULL,
			  `status` enum('Active','Inactive') COLLATE latin1_general_ci NOT NULL DEFAULT 'Active',
			  `class_name` varchar(255) COLLATE latin1_general_ci NOT NULL,
			  PRIMARY KEY (`mng_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1");
				
	}
#================================= Admin Model Start ======================#	
	function insertEntery($filename)
	{
		$this->db->set('manager_name',$this->input->post('manager_name'));
		if($this->input->post('parent_id'))
		$this->db->set('parent_id',$this->input->post('parent_id'));
		$this->db->set('page_set',$this->input->post('page_set'));
		$this->db->set('page_link',$this->input->post('page_link'));
		$this->db->set('display_order',$this->input->post('display_order'));						
		$this->db->set('status',$this->input->post('status'));
		if($this->input->post('class_name'))
		$this->db->set('class_name',$this->input->post('class_name'));
		$this->db->insert($this->tablename);	
	}
	
	function performMultipleOperations($idsArray,$task)
		{ 
			if($task=='delete')
				{
					for($i=0;isset($idsArray[$i]);$i++) 
						{
							$this->db->where('mng_id',$idsArray[$i]);
							$this->db->delete($this->tablename);
							
						}
					$message	=	"Selected records has been deleted successfully.";		
				}
			if($task=='Active' || $task=='Inactive' )
				{
					for($i=0;isset($idsArray[$i]);$i++) 
						{
							$this->db->set('status',$task);
							$this->db->where('mng_id',$idsArray[$i]);
							$this->db->update($this->tablename);
							
						}
					$message	=	"Selected records has been ".$task." successfully.";		
					//$message	=	"Status of selected records changed successfully.";		
				}
			return $message; 	
	}
	
	function getAllRecordCountForAdmin($task=NULL)
	{
		if($task)
			$this->db->where('status',$task);
			
	 	$query=$this->db->get($this->tablename);
		$row= $query->num_rows();		
	 	return $row;
	}
	
	function getAllRecordList($num,$offset)
	{	
	    $this->db->order_by('mng_id','desc');
		$query=$this->db->get($this->tablename,$num,$offset);
		$result= $query->result();
		//echo $this->db->last_query();die;
		//echo '<pre>'; print_r($result);die;
		return $result;
	}
	
	function getDetailByManagerId($mng_id)
	{
		$this->db->where('mng_id',$mng_id);
	 	$query=$this->db->get($this->tablename);
		$record	=	$query->row();	
		return $record;
	}
	
	function updateEntery($mng_id)
	{
		$this->db->set('manager_name',$this->input->post('manager_name'));
		//if($this->input->post('parent_id'))
		$this->db->set('parent_id',$this->input->post('parent_id'));
		$this->db->set('page_set',$this->input->post('page_set'));
		$this->db->set('page_link',$this->input->post('page_link'));
		$this->db->set('display_order',$this->input->post('display_order'));						
		$this->db->set('status',$this->input->post('status'));
		//if($this->input->post('class_name'))
		$this->db->set('class_name',$this->input->post('class_name'));
		$this->db->where('mng_id',$mng_id);
		$this->db->update($this->tablename);
	}
	
	function getAllManagerFilterCount($filterkey=NULL,$show_me=NULL,$sort_by=NULL,$category=NULL)
	{
		if($filterkey!='NULL')
		{	
			$where="(manager_name like '%".$filterkey."%' )";
			$this->db->where($where);
	 	}
		if($show_me!='NULL')
		{	
			if($show_me=='Active' || $show_me=='Inactive')
			$this->db->where('status',$show_me);
		}
		if($sort_by!='NULL')
		{	
			if($sort_by=='New')
			$this->db->order_by("mng_id","desc");
			if($sort_by=='Old')
			$this->db->order_by("mng_id","asc"); 
			if($sort_by=='Asc')
			$this->db->order_by("manager_name","asc");
			if($sort_by=='Desc')
			$this->db->order_by("manager_name","desc");
			
	 	}
	 	$query=$this->db->get($this->tablename);
		$row= $query->num_rows();	
		
	 	return $row;
		
	}
	function getAllManagerFilter($filterkey=NULL,$show_me=NULL,$sort_by=NULL,$num,$offset)
	{
	   if($filterkey!='NULL')
		{	
			$where="(manager_name like '%".$filterkey."%' )";
			$this->db->where($where);
	 	}
		if($show_me!='NULL')
		{	
			if($show_me=='Active' || $show_me=='Inactive')
			$this->db->where('status',$show_me);
		}
		if($sort_by!='NULL')
		{	
			if($sort_by=='New')
			$this->db->order_by("mng_id","desc");
			if($sort_by=='Old')
			$this->db->order_by("mng_id","asc"); 
			if($sort_by=='Asc')
			$this->db->order_by("manager_name","asc");
			if($sort_by=='Desc')
			$this->db->order_by("manager_name","desc");
			
	 	}
		$this->db->order_by("mng_id","desc");
	 	$query=$this->db->get($this->tablename,$num,$offset);
		$record	=	$query->result();	
		return $record;		
	}
	
	function perform_task($task,$id)
	{
			if($task=='Delete')
				{
				$this->db->where('mng_id',$id);
				$this->db->delete($this->tablename); 
				}
			else
				{
				$this->db->set('status', $task);
				$this->db->where('mng_id',$id);
				$this->db->update($this->tablename);
				}
	}
	
	function getAllRecordBySortingOrder()
	{
		$this->db->select();
		$this->db->order_by("display_order","asc");
		$this->db->where('parent_id',0);	
		$this->db->where('status','Active');
	 	$query=$this->db->get($this->tablename);
		$record	=	$query->result();	
		return $record;		
	}
	
	function updateOrder($id,$order)
	{
		$this->db->where('mng_id',$id);
		$this->db->set('display_order',$order);
		$this->db->update($this->tablename);	
	}
#================================= Admin Model End ======================#

}
