<?php

class Product_stock_model extends CI_Model
{
	var $tablename = 'product_stock';
	
	function getAllRecordsCount($product_id,$task=NULL)
	{
		if($task)
			$this->db->where('status',$task);
		$this->db->where('product_id', $product_id);
		$query = $this->db->get($this->tablename);
		$row = $query->num_rows();
		return $row;
	}
	
	function GetRecordsById($id)
	{
		$this->db->where("id", $id);
		$this->db->where('member_id', $this->parent_user_id);
		$query = $this->db->get($this->tablename);
		$row = $query->row();
		return $row;
	}
	
	function getAllRecords($product_id, $num, $offset)
	{
		$this->db->where('status', 'Active');
		$this->db->where('product_id', $product_id);
		$query = $this->db->get($this->tablename, $num, $offset);
		$record = $query->result();
		return $record;
	}
	
	function getAllScrapRecords($product_id, $num, $offset)
	{
		$this->db->where('status', 'Inactive');
		$this->db->where('product_id', $product_id);
		$query = $this->db->get($this->tablename, $num, $offset);
		$record = $query->result();
		return $record;
	}
	
	#==================================================================================#
	function performMultipleOperations($product_id, $ids, $task)
	{
		if ($task == 'delete') {
			for ($i = 0; isset($ids[$i]); $i++) {
				$this->db->where('id', $ids[$i]);
				$this->db->where('product_id', $product_id);
				$this->db->delete($this->tablename);
			}
			$message = "Selected records has been deleted successfully.";
		}
		if ($task == 'Active' || $task == 'Inactive') {
			for ($i = 0; isset($ids[$i]); $i++) {
				$this->db->set('status', $task);
				$this->db->where('id', $ids[$i]);
				$this->db->where('product_id', $product_id);
				$this->db->update($this->tablename);
			}
			$message = "Selected records has been " . $task . " successfully.";
		}
		return $message;
	}
	
	function perform_task($product_id, $task, $id)
	{
		if ($task == 'Delete') {
			$this->db->where('id', $id);
			$this->db->where('product_id', $product_id);
			$this->db->delete($this->tablename);
		}else {
			$this->db->set('status', $task);
			$this->db->where('id', $id);
			$this->db->where('product_id', $product_id);
			$this->db->update($this->tablename);
			
			$this->db->set('status', $task);
			$this->db->set('stock_id', $id);
			$this->db->set('note', $this->input->post('note'));
			$this->db->set('add_date', time());
			$this->db->insert('tbl_product_stock_scrap_history');
		}
	}
	
	function getMaxId($product_id)
	{
		$bottle_id = 0;
		$record = $this->db->select_max('bottle_id')
			->where('product_id', $product_id)
			->get($this->tablename)->row();
		if ($record->bottle_id) {
			$bottle_id = $record->bottle_id;
		}
		return $bottle_id;
	}
	
	function add_record($product_id, $parent_code)
	{
		$bottle_id = $this->getMaxId($product_id);
		$quantity = $this->input->post('quantity');
		for ($i = 0; $i < $quantity; $i++) {
			$bottle_id = $bottle_id + 1;
			$_id = substr("000", strlen($bottle_id)) . $bottle_id;
			$code = $parent_code . $_id;
			$qrcode = qrcode($code, 'products/' . $product_id);
			$this->db->set('product_id', $product_id);
			$this->db->set('bottle_id', $bottle_id);
			$this->db->set('bottle_code', $code);
			$this->db->set('qrcode', $qrcode);
			$this->db->set('status', 'Active');
			$this->db->set('add_date', time());
			$this->db->insert($this->tablename);
		}
	}
}