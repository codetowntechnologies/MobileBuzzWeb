<?php
class Emailtemplate_model extends CI_Model
{
	var $tablename='email_template';

	function getAdminAllTemplateCount($task=NULL)
	{
		if($task)
			$this->db->where('status',$task);

	 	$query=$this->db->get($this->tablename);
		$row= $query->num_rows();
	 	return $row;
	}

	function GetRecordsById($id)
	{
		$this->db->where("id",$id);
	 	$query=$this->db->get($this->tablename);
		$row	=	$query->row();
		return $row;
	}

	#====================Get Result of Email Template Functions=================

	function getAllTemplate($num,$offset)
	{
		$this->db->select();
		$this->db->order_by("id","desc");
	 	$query=$this->db->get($this->tablename,$num,$offset);
		$record	=	$query->result();
		return $record;
	}
	#====================Add New Email Template =================

	function addTemplate()
	{
	    $email_template_title = $this->input->post('email_template_title');
		$email_subject = $this->input->post('email_subject');
		$template_description = $this->input->post('template_description');
		$email_to_be_sent_from = $this->input->post('email_to_be_sent_from');
		$this->db->set('email_template_title', $email_template_title);
		$this->db->set('email_subject', $email_subject);
		$this->db->set('template_description', $template_description);
		//$this->db->set('email_to_be_sent_from', $email_to_be_sent_from);
		$this->db->set('status','Active');
		$this->db->set('ip',$_SERVER['REMOTE_ADDR']);
		$this->db->insert($this->tablename);
		return $this->db->insert_id();

	}
	#===========================================================================================


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


	#====================Update Email Template===============================

	function update_template($id)
	{
		$email_template_title = $this->input->post('email_template_title');
		$email_subject = $this->input->post('email_subject');
		$template_description = $this->input->post('template_description');
		$email_to_be_sent_from = $this->input->post('email_to_be_sent_from');
		$this->db->set('email_template_title', $email_template_title);
		$this->db->set('email_subject', $email_subject);
		$this->db->set('template_description', $template_description);
		//$this->db->set('email_to_be_sent_from', $email_to_be_sent_from);
		$this->db->where('id',$id);
		$this->db->update($this->tablename);
	}

	#==================================================================================

	#=====================Delete Record Function ======================


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

	#================== To get All Email Template Filter Count 'START'=========================

	function getAllTemplateFilterCount($filter=NULL,$show_me=NULL,$sort_by=NULL)
	{

		if($filter!='NULL')
		$this->db->like('email_template_title ',$filter);

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
			$this->db->order_by("email_template_title","asc");
			if($sort_by=='Desc')
			$this->db->order_by("email_template_title","desc");

	 	}
	 	$query=$this->db->get($this->tablename);
		$row= $query->num_rows();
	 	return $row;
	}

	#================== get All Record Of Filter=======================================

	function getAllTemplateFilter($filter=NULL,$show_me=NULL,$sort_by=NULL,$num,$offset)
	{
		if($filter!='NULL')
		$this->db->like('email_template_title',$filter);

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
			$this->db->order_by("email_template_title","asc");
			if($sort_by=='Desc')
			$this->db->order_by("email_template_title","desc");

	 	}
		$this->db->order_by("id","desc");
	 	$query=$this->db->get($this->tablename,$num,$offset);
		$record	=	$query->result();
		return $record;
	}


}

