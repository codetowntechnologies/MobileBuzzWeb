<?php
class Invoice_payment_model extends CI_Model
{
	var $tablename      =   'invoice_payments';
	var $tableparent    =   'invoice';

	function getAllRecordsCount($task=NULL)
	{
		if($task)
			$this->db->where('status',$task);
		$this->db->where('member_id',$this->parent_user_id);
	 	$query=$this->db->get($this->tablename);
		$row= $query->num_rows();
	 	return $row;
	}

	function GetRecordsById($id)
	{
		$this->db->select('invoice.*,member.name as client_name,email,phone_number,address,company_name');
		$this->db->join('member','member.id = invoice.client_id');
		$this->db->where("invoice.id",$id);
		$this->db->where('member_id',$this->parent_user_id);
	 	$query=$this->db->get($this->tablename);
		$row	=	$query->row();
		return $row;
	}
	
	function GetItemsRecordsById($id)
	{
		$this->db->where("invoice_id",$id);
		$this->db->where('member_id',$this->parent_user_id);
	 	$query=$this->db->get($this->tablenameitems);
		$record	=	$query->result();
		return $record;
	}

	function getAllRecords($num,$offset)
	{
		$this->db->select('invoice.*,member.name as client_name');
		$this->db->where('member_id',$this->parent_user_id);
		$this->db->join('member','member.id = invoice.client_id');
	 	$query=$this->db->get($this->tablename,$num,$offset);
		$record	=	$query->result();
		return $record;
	}

	#==================================================================================#

	function add_record($member_id,$invoice_id,$client_id)
	{
		$this->db->set('invoice_id', $invoice_id);
		$this->db->set('member_id', $member_id);
		$this->db->set('client_id', $client_id);
	    $this->db->set('amount', $this->input->post('amount'));
		$this->db->set('payment_date', date('Y-m-d',strtotime($this->input->post('payment_date'))));
		$this->db->set('payment_method', $this->input->post('payment_method'));
		$this->db->set('reference', $this->input->post('reference'));
		if($this->input->post('note'))
		$this->db->set('note', $this->input->post('note'));
		$this->db->set('add_date',time());
		$this->db->set('ip',$this->input->ip_address());
		$this->db->insert($this->tablename);
		
		$amount = floatval($this->input->post('amount'));
		$this->db->set('paid_amount','paid_amount+'.$amount, FALSE);
		$this->db->where('id',$invoice_id);
		$this->db->update($this->tableparent);
	}

	function add_record_by_settle($member_id,$client_id,$data)
	{
    foreach ($data as $val) {
      if($val['amount'] > 0){
        $this->db->set('invoice_id', $val['id']);
        $this->db->set('member_id', $member_id);
        $this->db->set('client_id', $client_id);
        $this->db->set('amount', $val['amount']);
        $this->db->set('payment_date', date('Y-m-d'));
        $this->db->set('payment_method', $this->input->post('payment_method'));
        $this->db->set('reference', $this->input->post('reference'));
        $this->db->set('note', 'settlement');
        $this->db->set('add_date', time());
        $this->db->set('ip', $this->input->ip_address());
        $this->db->insert($this->tablename);

      }
      $amount = floatval($val['amount']);
      $this->db->set('status', 'Settled');
      $this->db->set('paid_amount', 'paid_amount+' . $amount, FALSE);
      $this->db->where('id', $val['id']);
      $this->db->update($this->tableparent);
    }
	}


}