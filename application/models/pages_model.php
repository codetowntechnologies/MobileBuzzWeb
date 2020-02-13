<?php
class Pages_model extends CI_Model
{
	var $tablename='page';


	function getAdminAllPagesCount($task=NULL)
	{
		if($task)
			$this->db->where('status',$task);

	 	$query=$this->db->get($this->tablename);
		$row= $query->num_rows();
	 	return $row;
	}

	function GetRecordsById($id)
	{
		$this->db->where("pg_id",$id);
	 	$query=$this->db->get($this->tablename);
		$row	=	$query->row();
		return $row;
	}

	function getAllPages($num,$offset)
	{
		$this->db->select();
		$this->db->order_by("pg_id","desc");
	 	$query=$this->db->get($this->tablename,$num,$offset);
		$record	=	$query->result();
		return $record;
	}


	#==================================================================================#

	function addPages()
	{
	   // $slug = $this->input->post('slug');
	    $page_name = $this->input->post('page_name');
	    $page_content = $this->input->post('page_content');
	    $meta_title = $this->input->post('meta_title');
	    $meta_keywords = $this->input->post('meta_keywords');
	    $meta_description = $this->input->post('meta_description');
	    $menu_links_display = $this->input->post('menu_links_display');
	    if($page_name)
		$this->db->set('slug',$this->common_model->create_unique_slug_for_common($page_name,$this->tablename));

		$this->db->set('page_name', $page_name);
		$this->db->set('page_content', $page_content);
		$this->db->set('meta_title', $meta_title);
		$this->db->set('meta_keywords', $meta_keywords);
		$this->db->set('meta_description', $meta_description);
		$this->db->set('status','Active');
		$this->db->set('ip',$_SERVER['REMOTE_ADDR']);
		$this->db->insert($this->tablename);
		return $this->db->insert_id();

	}

	function performMultipleOperations($ids,$task)
	{
		if($task=='delete')
			{
				for($i=0;isset($ids[$i]);$i++)
					{
						$this->db->where('pg_id',$ids[$i]);
						$this->db->delete($this->tablename);

					}
				$message	=	"Selected records has been deleted successfully.";
			}
		if($task=='Active' || $task=='Inactive' )
			{
				for($i=0;isset($ids[$i]);$i++)
					{
						$this->db->set('status',$task);
						$this->db->where('pg_id',$ids[$i]);
						$this->db->update($this->tablename);
					}
				$message	=	"Selected records has been ".$task." successfully.";
			}
		return $message;
	}

	#====================Function Update Pages ===============================

	function update_pages($id)
	{

		$page_name_old=$this->common_model->getSingleFieldFromAnyTable('page_name','pg_id',$id,$this->tablename);
		$slug = $this->input->post('slug');
	    $page_name = $this->input->post('page_name');
	    $page_content = $this->input->post('page_content');
	    $meta_title = $this->input->post('meta_title');
	    $meta_keywords = $this->input->post('meta_keywords');
	    $meta_description = $this->input->post('meta_description');
	    $menu_links_display = $this->input->post('menu_links_display');

		$this->db->set('page_name', $page_name);
		$this->db->set('page_content', $page_content);
		$this->db->set('meta_title', $meta_title);
		$this->db->set('meta_keywords', $meta_keywords);
		$this->db->set('meta_description', $meta_description);
		$this->db->where('pg_id',$id);
		$this->db->update($this->tablename);
	}

	function perform_task($task,$id)
	{
		if($task=='Delete')
			{
			$this->db->where('pg_id',$id);
			$this->db->delete($this->tablename);


			}
		else
			{
			$this->db->set('status', $task);
			$this->db->where('pg_id',$id);
			$this->db->update($this->tablename);
			}
	}

	function getAllPagesFilterCount($filter=NULL,$show_me=NULL,$sort_by=NULL)
	{

		if($filter!='NULL')
		$this->db->like('page_name',$filter);

		if($show_me!='NULL')
		{
			if($show_me=='Active' || $show_me=='Inactive')
			$this->db->where('status',$show_me);
		}
		if($sort_by!='NULL')
		{
			if($sort_by=='New')
			$this->db->order_by("pg_id","desc");
			if($sort_by=='Old')
			$this->db->order_by("pg_id","asc");
			if($sort_by=='Asc')
			$this->db->order_by("page_name","asc");
			if($sort_by=='Desc')
			$this->db->order_by("page_name","desc");

	 	}
	 	$query=$this->db->get($this->tablename);
		$row= $query->num_rows();
	 	return $row;
	}

	function getAllPagesFilter($filter=NULL,$show_me=NULL,$sort_by=NULL,$num,$offset)
	{
		if($filter!='NULL')
		$this->db->like('page_name',$filter);

		if($show_me!='NULL')
		{
			if($show_me=='Active' || $show_me=='Inactive')
			$this->db->where('status',$show_me);
		}
		if($sort_by!='NULL')
		{
			if($sort_by=='New')
			$this->db->order_by("pg_id","desc");
			if($sort_by=='Old')
			$this->db->order_by("pg_id","asc");
			if($sort_by=='Asc')
			$this->db->order_by("page_name","asc");
			if($sort_by=='Desc')
			$this->db->order_by("page_name","desc");

	 	}
		$this->db->order_by("pg_id","desc");
	 	$query=$this->db->get($this->tablename,$num,$offset);
		$record	=	$query->result();
		return $record;
	}


	// front function starts here//

	function GetRecordsBySlug($slug)
	{
		$this->db->where('status','Active');
		$this->db->where("slug",$slug);
	 	$query=$this->db->get($this->tablename);
		$row	=	$query->row();
		return $row;
	}

	// front function ends here//


}

