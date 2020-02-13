<?php

class Invoice_model extends CI_Model
{
  var $tablename = 'invoice';
  var $tablenameitems = 'invoice_items';
  var $tablesettle = 'invoice_settlement';

  function getAllRecordsCount($task = NULL)
  {
    if ($task)
      $this->db->where('status', $task);
    $this->db->where('member_id', $this->parent_user_id);
    $query = $this->db->get($this->tablename);
    $row = $query->num_rows();
    return $row;
  }

  function GetRecordsById($id)
  {
    $this->db->select('invoice.*,member.name as client_name,email,phone_number,address,company_name');
    $this->db->join('member', 'member.id = invoice.client_id');
    $this->db->where("invoice.id", $id);
    $this->db->where('member_id', $this->parent_user_id);
    $query = $this->db->get($this->tablename);
    $row = $query->row();
    return $row;
  }

  function GetItemsRecordsById($id)
  {
    $this->db->where("invoice_id", $id);
    $this->db->where('member_id', $this->parent_user_id);
    $query = $this->db->get($this->tablenameitems);
    $record = $query->result();
    return $record;
  }

  function getAllRecords($num, $offset)
  {
    $this->db->select('invoice.*,member.name as client_name');
    $this->db->where('member_id', $this->parent_user_id);
    $this->db->join('member', 'member.id = invoice.client_id');
    $this->db->order_by("id", "desc");
    $query = $this->db->get($this->tablename, $num, $offset);
    $record = $query->result();
    return $record;
  }

  function getAllDueRecords($client_id)
  {
    $this->db->select('invoice.*,member.name as client_name');
    $this->db->where('invoice.client_id', $client_id);
    $this->db->where('invoice.member_id', $this->parent_user_id);
    $this->db->join('member', 'member.id = invoice.client_id');
    $query = $this->db->get($this->tablename);
    $record = $query->result();
    //echo $this->db->last_query();
    return $record;
  }

  function getLastInvoice($client_id)
  {
    $this->db->select('invoice.*,member.name as client_name');
    $this->db->select('DATE_FORMAT(invoice_date,"%m/%d/%Y") as invoice_date_to', false);
    $this->db->where('invoice.client_id', $client_id);
    $this->db->where('invoice.member_id', $this->parent_user_id);
    $this->db->join('member', 'member.id = invoice.client_id');
    $this->db->order_by('invoice.invoice_date','desc');
    $this->db->order_by('invoice.id','desc');
    $query = $this->db->get($this->tablename);
    $record = $query->row();
    //echo $this->db->last_query();
    return $record;
  }

  #==================================================================================#

  function add_record($member_id)
  {
    $this->db->set('member_id', $member_id);
    $this->db->set('client_id', $this->input->post('client_id'));
    $this->db->set('tax_type', $this->input->post('tax_type'));
    $this->db->set('from_date', date('Y-m-d', strtotime($this->input->post('from_date'))));
    $this->db->set('invoice_date', date('Y-m-d', strtotime($this->input->post('invoice_date'))));
    $this->db->set('due_date', date('Y-m-d', strtotime($this->input->post('due_date'))));
    $this->db->set('subtotal_amount', $this->input->post('subtotal_amount'));
    $this->db->set('tax', $this->input->post('tax'));
    $this->db->set('total_amount', $this->input->post('total_amount'));
    $this->db->set('paid_amount', 0.00);
    $this->db->set('add_date', time());
    $this->db->set('status', 'Active');
    $this->db->set('ip', $this->input->ip_address());
    $this->db->insert($this->tablename);
    $invoice_id = $this->db->insert_id();


    $items = $this->input->post('items');
    foreach ($items as $key => $value) {
      $this->db->set('invoice_id', $invoice_id);
      $this->db->set('member_id', $member_id);
      $this->db->set('client_id', $this->input->post('client_id'));
      $this->db->set('product_id', $value['product_id']);
      $this->db->set('item_type', 'product');
      $this->db->set('product_name', $value['title']);
      $this->db->set('quantity', $value['qty']);
      $this->db->set('rate', $value['mrp']);
      $this->db->set('selling_rate', $value['selling_price']);
      $this->db->set('tax', $value['tax']);
      $this->db->set('discount', $value['discount']);
      $this->db->set('discount_amount', ($value['mrp'] * $value['discount'] / 100));
      $this->db->set('gross_amount', $value['gross_amt']);
      $this->db->set('total_amount', $value['total_amt']);
      $this->db->set('add_date', time());
      $this->db->insert($this->tablenameitems);

      $client_id = $this->input->post('client_id');
      $date = date('Y-m-d', strtotime($this->input->post('invoice_date')));
      $product_id = $value['product_id'];

      if ($product_id) {
        $rows = $this->getAllPendingItems($member_id, $client_id, $date, $product_id);
        $ids = array_column($rows, 'id');

        if (!empty($ids)) {
          $this->db->set('bill_status', 'Generated');
          $this->db->where_in('id', $ids);
          $this->db->update('product_usage');
        }
      }
    }

    $extra = $this->input->post('extra');
    foreach ($extra as $key => $value) {
      $this->db->set('invoice_id', $invoice_id);
      $this->db->set('member_id', $member_id);
      $this->db->set('client_id', $this->input->post('client_id'));
      $this->db->set('item_type', 'extra');
      $this->db->set('product_name', $value['title']);
      $this->db->set('quantity', $value['qty']);
      $this->db->set('rate', $value['mrp']);
      $this->db->set('selling_rate', $value['mrp']);
      $this->db->set('gross_amount', $value['gross_amt']);
      $this->db->set('total_amount', $value['total_amt']);
      $this->db->set('add_date', time());
      $this->db->insert($this->tablenameitems);
    }

    return $invoice_id;
  }

  function update_record($member_id,$invoice_id)
  {
    //$this->db->set('member_id', $member_id);
    //$this->db->set('client_id', $this->input->post('client_id'));
    $this->db->set('tax_type', $this->input->post('tax_type'));
    $this->db->set('from_date', date('Y-m-d', strtotime($this->input->post('from_date'))));
    $this->db->set('invoice_date', date('Y-m-d', strtotime($this->input->post('invoice_date'))));
    $this->db->set('due_date', date('Y-m-d', strtotime($this->input->post('due_date'))));
    $this->db->set('subtotal_amount', $this->input->post('subtotal_amount'));
    $this->db->set('tax', $this->input->post('tax'));
    $this->db->set('total_amount', $this->input->post('total_amount'));
    //$this->db->set('paid_amount', 0.00);
    //$this->db->set('add_date', time());
    //$this->db->set('status', 'Active');
    //$this->db->set('ip', $this->input->ip_address());
    $this->db->where('id', $invoice_id);
    $this->db->where('member_id', $member_id);
    $this->db->where('client_id', $this->input->post('client_id'));
    $this->db->update($this->tablename);

    $this->db->where('invoice_id', $invoice_id);
    $this->db->where('member_id', $member_id);
    $this->db->delete($this->tablenameitems);

    $items = $this->input->post('items');
    foreach ($items as $key => $value) {
      $this->db->set('invoice_id', $invoice_id);
      $this->db->set('member_id', $member_id);
      $this->db->set('client_id', $this->input->post('client_id'));
      $this->db->set('product_id', $value['product_id']);
      $this->db->set('item_type', 'product');
      $this->db->set('product_name', $value['title']);
      $this->db->set('quantity', $value['qty']);
      $this->db->set('rate', $value['mrp']);
      $this->db->set('selling_rate', $value['selling_price']);
      $this->db->set('tax', $value['tax']);
      $this->db->set('discount', $value['discount']);
      $this->db->set('discount_amount', ($value['mrp'] * $value['discount'] / 100));
      $this->db->set('gross_amount', $value['gross_amt']);
      $this->db->set('total_amount', $value['total_amt']);
      $this->db->set('add_date', time());
      $this->db->insert($this->tablenameitems);

      /*$client_id = $this->input->post('client_id');
      $date = date('Y-m-d', strtotime($this->input->post('invoice_date')));
      $product_id = $value['product_id'];

      if ($product_id) {
        $rows = $this->getAllPendingItems($member_id, $client_id, $date, $product_id);
        $ids = array_column($rows, 'id');

        if (!empty($ids)) {
          $this->db->set('bill_status', 'Generated');
          $this->db->where_in('id', $ids);
          $this->db->update('product_usage');
        }
      }*/
    }

    $extra = $this->input->post('extra');
    foreach ($extra as $key => $value) {
      $this->db->set('invoice_id', $invoice_id);
      $this->db->set('member_id', $member_id);
      $this->db->set('client_id', $this->input->post('client_id'));
      $this->db->set('item_type', 'extra');
      $this->db->set('product_name', $value['title']);
      $this->db->set('quantity', $value['qty']);
      $this->db->set('rate', $value['mrp']);
      $this->db->set('selling_rate', $value['mrp']);
      $this->db->set('gross_amount', $value['gross_amt']);
      $this->db->set('total_amount', $value['total_amt']);
      $this->db->set('add_date', time());
      $this->db->insert($this->tablenameitems);
    }

    return $invoice_id;
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

  function getAllPendingItems($user_id, $client_id, $date, $product_id)
  {
    $this->db->select('id');
    $this->db->where('user_id', $user_id);
    $this->db->where('client_id', $client_id);
    $this->db->where('product_id', $product_id);
    $this->db->where('DATE(checkin_date) <=', $date);
    $this->db->where('bill_status', 'Pending');
    $query = $this->db->get('product_usage');
    $record = $query->result_array();
    return $record;
  }

  private function arrangeSalesFigures($values)
  {
    $return = [];
    foreach ($values as $val) {
      $return[$val->month] = $val->total;
    }
    return $return;
  }

  public function getGrossMonthlySales($year)
  {
    $reports = [];
    /*$products = $this->db->select('MONTH(invoice_date) as month, sum('.$this->db->dbprefix('invoice_items').'.total_amount) as total')
      ->join('invoice_items', 'invoice_items.invoice_id = invoice.id')
      ->where('YEAR(invoice_date)', $year)
      ->group_by(['MONTH(invoice_date)'])
      ->order_by("invoice_date", "desc")
      ->get($this->tablename)->result();
    $reports['invoice'] = $this->arrangeSalesFigures($products);*/

    $products = $this->db->select('MONTH(invoice_date) as month, sum(' . $this->db->dbprefix('invoice_items') . '.gross_amount) as total')
      ->join('invoice_items', 'invoice_items.invoice_id = invoice.id')
      ->where('YEAR(invoice_date)', $year)
      ->group_by(['MONTH(invoice_date)'])
      ->order_by("invoice_date", "desc")
      ->get($this->tablename)->result();
    $reports['products'] = $this->arrangeSalesFigures($products);

    $sales = $this->db->select('MONTH(invoice_date) as month, sum(' . $this->db->dbprefix('invoice_items') . '.quantity) as total')
      ->join('invoice_items', 'invoice_items.invoice_id = invoice.id')
      ->where('YEAR(invoice_date)', $year)
      ->group_by(['MONTH(invoice_date)'])
      ->order_by("invoice_date", "desc")
      ->get($this->tablename)->result();
    $reports['sales'] = $this->arrangeSalesFigures($sales);

    $coustomers = $this->db->select('MONTH(invoice_date) as month, count(DISTINCT `tbl_invoice`.`client_id`) as total')
      ->join('invoice_items', 'invoice_items.invoice_id = invoice.id')
      ->where('YEAR(invoice_date)', $year)
      ->group_by(['MONTH(invoice_date)'])
      ->order_by("invoice_date", "desc")
      ->get($this->tablename)->result();
    $reports['coustomers'] = $this->arrangeSalesFigures($coustomers);

    $couponDiscounts = $this->db->select('MONTH(invoice_date) as month, sum(' . $this->db->dbprefix('invoice_items') . '.discount_amount * ' . $this->db->dbprefix('invoice_items') . '.quantity) as total')
      ->join('invoice_items', 'invoice_items.invoice_id = invoice.id')
      ->where('YEAR(invoice_date)', $year)
      ->group_by(['MONTH(invoice_date)'])
      ->order_by("invoice_date", "desc")
      ->get($this->tablename)->result();
    $reports['discounts'] = $this->arrangeSalesFigures($couponDiscounts);

    $tax = $this->db->select('MONTH(invoice_date) as month, sum(' . $this->db->dbprefix('invoice_items') . '.total_amount - ' . $this->db->dbprefix('invoice_items') . '.gross_amount) as total')
      ->join('invoice_items', 'invoice_items.invoice_id = invoice.id')
      ->where('YEAR(invoice_date)', $year)
      ->group_by(['MONTH(invoice_date)'])
      ->order_by("invoice_date", "desc")
      ->get($this->tablename)->result();
    $reports['tax'] = $this->arrangeSalesFigures($tax);

    return $reports;
  }

  public function getSalesYears()
  {
    $this->db->order_by("invoice_date", "desc");
    $this->db->select('YEAR(invoice_date) as year');
    $this->db->group_by('YEAR(invoice_date)');
    $records = $this->db->get($this->tablename)->result();
    $years = [];
    foreach ($records as $r) {
      $years[] = $r->year;
    }
    return $years;
  }

  function add_settle_record($member_id,$client_id)
  {
    $this->db->set('member_id', $member_id);
    $this->db->set('client_id', $client_id);
    $this->db->set('amount', $this->input->post('total_settle_amount'));
    $this->db->set('payment_method', $this->input->post('payment_method'));
    $this->db->set('reference', $this->input->post('reference'));
    $this->db->set('items', json_encode($this->input->post('invoice')));
    $this->db->set('add_date', time());
    $this->db->insert($this->tablesettle);
  }
}
