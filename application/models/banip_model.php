<?php
class Banip_model extends CI_Model
{
	var $tablename='ban_ip';

	function getRecordById($id)
	{
		$this->db->where("id",$id);
	 	$query=$this->db->get($this->tablename);
		$record	=	$query->row();
		return $record;
	}

	function getAdminAllRecordCount($task=NULL)
	{
		if($task)
			$this->db->where('status',$task);

	 	$query=$this->db->get($this->tablename);
		$row= $query->num_rows();
	 	return $row;
	}


	function performMultipleOperations($ids,$task)
		{
			if($task=='delete')
				{
					for($i=0;isset($ids[$i]);$i++)
						{
							$this->db->where('id',$ids[$i]);
							$this->db->delete($this->tablename);
						}
					$message	=	"Selected records has been deleted successfully.";
				}
			if($task=='Active' || $task=='Inactive' )
				{
					for($i=0;isset($ids[$i]);$i++)
						{
							$this->db->set('status',$task);
							$this->db->where('id',$ids[$i]);
							$this->db->update($this->tablename);

						}
					$message	=	"Selected records has been ".$task." successfully.";
				}
			return $message;
		}

	function perform_task($task,$id)
	{
			if($task=='Delete')
				{
				$this->db->where('id',$id);
				$this->db->delete($this->tablename);
				}
			else
				{
				$this->db->set('status', $task);
				$this->db->where('id',$id);
				$this->db->update($this->tablename);
				}
	}

	#====================Get All BAn IP's=================

	function getAllRecord($num,$offset)
	{
		$this->db->select();
		$this->db->order_by("id","desc");
	 	$query=$this->db->get($this->tablename,$num,$offset);
		$record	=	$query->result();
		return $record;
	}

	#====================Function Add New Ban Ip =================

	function addRecord()
	{
		$this->db->set('ip', $this->input->post('ban_ip'));
		$this->db->set('status','Active');
		$this->db->insert($this->tablename);
		return $this->db->insert_id();
	}


	#================== To get All Pages Filter Count 'START'=========================

	function getAllBanipFilterCount($filter=NULL,$show_me=NULL,$sort_by=NULL)
	{

		if($filter!='NULL')
		$this->db->like('ip',$filter);

		if($show_me!='NULL')
		{
			if($show_me=='Active' || $show_me=='Inactive')
			$this->db->where('status',$show_me);
		}
		if($sort_by!='NULL')
		{
			if($sort_by=='New')
			$this->db->order_by("id","desc");
			if($sort_by=='Old')
			$this->db->order_by("id","asc");
			if($sort_by=='Asc')
			$this->db->order_by("id","asc");
			if($sort_by=='Desc')
			$this->db->order_by("id","desc");

	 	}
	 	$query=$this->db->get($this->tablename);
		$row= $query->num_rows();
	 	return $row;
	}



	#================== To get All Record Of Filter 'START'=========================

	function getAllBanipFilter($filter=NULL,$show_me=NULL,$sort_by=NULL,$num,$offset)
	{
		if($filter!='NULL')
		$this->db->like('ip',$filter);

		if($show_me!='NULL')
		{
			if($show_me=='Active' || $show_me=='Inactive')
			$this->db->where('status',$show_me);
		}
		if($sort_by!='NULL')
		{
			if($sort_by=='New')
			$this->db->order_by("id","desc");
			if($sort_by=='Old')
			$this->db->order_by("id","asc");
			if($sort_by=='Asc')
			$this->db->order_by("id","asc");
			if($sort_by=='Desc')
			$this->db->order_by("id","desc");

	 	}
		$this->db->order_by("id","desc");
	 	$query=$this->db->get($this->tablename,$num,$offset);
		$record	=	$query->result();
		return $record;
	}




}

