<?php
class Weblog_model extends CI_Model
{
	var $tablename='admin_log';

	function getAdminAllRecordCount($task=NULL)
	{
	 	$query=$this->db->get($this->tablename);
		$row= $query->num_rows();
	 	return $row;
	}

#====================Function to Get Result of WebLog=================

	function getAllRecord($num,$offset)
	{
		$this->db->select();
		$this->db->order_by("log_id","desc");
	 	$query=$this->db->get($this->tablename,$num,$offset);
		$record	=	$query->result();
		return $record;
	}

	function performMultipleOperations($ids,$task)
		{
			if($task=='delete')
				{
					for($i=0;isset($ids[$i]);$i++)
						{
							$this->db->where('log_id',$ids[$i]);
							$this->db->delete($this->tablename);
						}
					$message	=	"Selected records has been deleted successfully.";
				}

			return $message;
		}
#====================Function to Single WebLog Record By ID=================

	function getRecordById($id)
	{
		$this->db->where("log_id",$id);
	 	$query=$this->db->get($this->tablename);
		$record	=	$query->row();
		return $record;
	}


#=================================Function To Clear All Web Log ==============#
	function clear_log(){
		$this->db->where('log_id !=',0);
		$this->db->delete($this->tablename);
	}

	function perform_task($task,$id)
	{
		if($task=='Delete')
		{
			$this->db->where('log_id',$id);
			$this->db->delete($this->tablename);
		}
		else
		{
			$this->db->set('status', $task);
			$this->db->where('pg_id',$id);
			$this->db->update($this->tablename);
		}
	}

#========================Function for Database Backup================================#
	function insert_backup_data()
	{
		$this->db->set('last_database_backup_date', time());
		$this->db->where('id',1);
		$this->db->update('website_config');
	}

}

