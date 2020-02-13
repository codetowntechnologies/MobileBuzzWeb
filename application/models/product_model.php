<?php

class Product_model extends CI_Model
{
  var $tablename = 'product';

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
    $this->db->select("product.*,product_brands.title as product_brand,product_groups.title as product_group");
    $this->db->where("product.id", $id);
    $this->db->where('member_id', $this->parent_user_id);

    $this->db->join('product_brands', 'product_brands.id = product.product_brand', 'left');
    $this->db->join('product_groups', 'product_groups.id = product.product_group', 'left');
    $query = $this->db->get($this->tablename);
    $row = $query->row();
    return $row;
  }

  function getAllRecords($num, $offset)
  {
    $this->db->where('member_id', $this->parent_user_id);
    $this->db->order_by('id', 'DESC');
    $query = $this->db->get($this->tablename, $num, $offset);
    $record = $query->result();
    return $record;
  }

  function getAllRecordsFilterCount($filter = NULL, $show_me = NULL, $sort_by = NULL)
  {
    if ($filter != 'NULL')
      $this->db->like('product_name', $filter);

    if ($show_me != 'NULL') {
      if ($show_me == 'Active' || $show_me == 'Inactive')
        $this->db->where('status', $show_me);
    }
    if ($sort_by != 'NULL') {
      if ($sort_by == 'New')
        $this->db->order_by("id", "desc");
      if ($sort_by == 'Old')
        $this->db->order_by("id", "asc");
      if ($sort_by == 'Asc')
        $this->db->order_by("id", "asc");
      if ($sort_by == 'Desc')
        $this->db->order_by("id", "desc");
    }
    $query = $this->db->get($this->tablename);
    $row = $query->num_rows();
    return $row;
  }

  #================== To get All Record Of Filter 'START'=========================

  function getAllRecordsFilter($filter = NULL, $show_me = NULL, $sort_by = NULL, $num, $offset)
  {
    if ($filter != 'NULL')
      $this->db->like('product_name', $filter);

    if ($show_me != 'NULL') {
      if ($show_me == 'Active' || $show_me == 'Inactive')
        $this->db->where('status', $show_me);
    }
    if ($sort_by != 'NULL') {
      if ($sort_by == 'New')
        $this->db->order_by("id", "desc");
      if ($sort_by == 'Old')
        $this->db->order_by("id", "asc");
      if ($sort_by == 'Asc')
        $this->db->order_by("id", "asc");
      if ($sort_by == 'Desc')
        $this->db->order_by("id", "desc");
    }
    $this->db->order_by("id", "desc");
    $query = $this->db->get($this->tablename, $num, $offset);
    $record = $query->result();
    return $record;
  }

  function getAllRecordsReportFilter()
  {
    //$this->db->select('product.*,area.name as area_name');
    if ($this->input->get('product_name'))
      $this->db->like('product.product_name', $this->input->get('product_name'));
    if ($this->input->get('area'))
      $this->db->like('area', $this->input->get('area'));

    if ($this->input->get('sort_by')) {
      $order_by = $this->input->get('order_by');
      if ($this->input->get('order_by') == '')
        $order_by = 'asc';

      $this->db->order_by('product.' . $this->input->get('sort_by'), $order_by);
    } else {
      $this->db->order_by("product.id", "desc");
    }

    $this->db->where('member_id', $this->parent_user_id);
    //$this->db->join('area','area.id = product.area','left');

    $query = $this->db->get($this->tablename);
    return $row = $query->result();
  }

  function getAllRecordsStockReportFilter()
  {
    $this->db->select('product.id,product.member_id,product.product_code,product.product_name');
    $this->db->select('(Select COUNT(id) from tbl_product_stock as stock_in where stock_in.product_id=tbl_product.id) as stock');
    $this->db->select('(Select COUNT(id) from tbl_product_stock as stock_in where stock_in.product_id=tbl_product.id and stock_in.status="Active") as stock_active');
    $this->db->select('(Select COUNT(id) from tbl_product_stock as stock_out where stock_out.product_id=tbl_product.id and stock_out.status="Inactive") as stock_scrap');
    if ($this->input->get('product_name'))
      $this->db->like('product.product_name', $this->input->get('product_name'));
    /*if($this->input->get('area'))
      $this->db->like('area',$this->input->get('area'));*/

    if ($this->input->get('sort_by')) {
      $order_by = $this->input->get('order_by');
      if ($this->input->get('order_by') == '')
        $order_by = 'asc';

      $this->db->order_by('product.' . $this->input->get('sort_by'), $order_by);
    } else {
      $this->db->order_by("product.id", "desc");
    }

    $this->db->where('member_id', $this->parent_user_id);
    //$this->db->group_by('product.id');

    $query = $this->db->get($this->tablename);
    /*$row= $query->result();
    echo "<pre>"; print_r($row); die;*/
    return $row = $query->result();
  }

  #==================================================================================#

  function performMultipleOperations($ids, $task)
  {
    if ($task == 'delete') {
      for ($i = 0; isset($ids[$i]); $i++) {
        $this->db->where('id', $ids[$i]);
        $this->db->where('member_id', $this->parent_user_id);
        $this->db->delete($this->tablename);
      }
      $message = "Selected records has been deleted successfully.";
    }
    if ($task == 'Active' || $task == 'Inactive') {
      for ($i = 0; isset($ids[$i]); $i++) {
        $this->db->set('status', $task);
        $this->db->where('id', $ids[$i]);
        $this->db->where('member_id', $this->parent_user_id);
        $this->db->update($this->tablename);
      }
      $message = "Selected records has been " . $task . " successfully.";
    }
    return $message;
  }

  function perform_task($task, $id)
  {
    if ($task == 'Delete') {
      $this->db->where('id', $id);
      $this->db->where('member_id', $this->parent_user_id);
      $this->db->delete($this->tablename);
    } else if ($task == 'Feature' || $task == 'Unfeature') {
      $this->db->set('featured', ($task == 'Feature' ? 'Yes' : 'No'));
      $this->db->where('id', $id);
      $this->db->where('member_id', $this->parent_user_id);
      $this->db->update($this->tablename);
    } else {
      $this->db->set('status', $task);
      $this->db->where('id', $id);
      $this->db->where('member_id', $this->parent_user_id);
      $this->db->update($this->tablename);
    }
  }

  function add_record($member_id, $filename = null)
  {
    $this->db->set('product_name', $this->input->post('product_name'));

    if ($this->input->post('product_code'))
      $this->db->set('product_code', $this->input->post('product_code'));

    if ($this->input->post('hsn_code'))
      $this->db->set('hsn_code', $this->input->post('hsn_code'));

    if ($this->input->post('product_description'))
      $this->db->set('product_description', $this->input->post('product_description'));

    $this->db->set('product_size', $this->input->post('product_size'));
    $this->db->set('product_brand', $this->input->post('product_brand'));
    $this->db->set('product_group', $this->input->post('product_group'));
    $this->db->set('product_tax_input', $this->input->post('product_tax_input'));
    $this->db->set('product_tax_output', $this->input->post('product_tax_output'));
    $this->db->set('mrp_price', $this->input->post('mrp_price'));
    $this->db->set('selling_price', $this->input->post('selling_price'));
    $this->db->set('purchase_cost', $this->input->post('purchase_cost'));
    $this->db->set('landing_cost', $this->input->post('landing_cost'));
    $this->db->set('discount', $this->input->post('discount'));
    $this->db->set('opening_stock', $this->input->post('opening_stock'));
    if ($filename)
      $this->db->set('image', $filename);

    $this->db->set('member_id', $member_id);
    $this->db->set('status', 'Active');
    $this->db->set('add_date', time());
    $this->db->set('modify_date', time());
    $this->db->set('ip', $this->input->ip_address());
    $this->db->insert($this->tablename);
    $product_id = $this->db->insert_id();

    if ($this->input->post('product_code') == '') {
      $parent_id = substr("000", strlen($member_id)) . $member_id;
      $_id = substr("0000", strlen($product_id)) . $product_id;
      $code = 'PRD' . $parent_id . $_id;
    } else {
      $code = $this->input->post('product_code');
    }
    $qrcode = qrcode($code, 'products');

    $this->db->set('product_code', $code);
    $this->db->set('qrcode', $qrcode);
    $this->db->where('id', $product_id);
    $this->db->update($this->tablename);

    if ($this->input->post('opening_stock') > 0) {
      $_POST['quantity'] = $this->input->post('opening_stock');
      $this->product_stock_model->add_record($product_id, $code);
    }
    return $product_id;
  }

  function edit_record($id)
  {
    $this->db->set('product_name', $this->input->post('product_name'));
    $this->db->set('hsn_code', $this->input->post('hsn_code'));
    $this->db->set('product_description', $this->input->post('product_description'));
    $this->db->set('product_size', $this->input->post('product_size'));
    $this->db->set('product_brand', $this->input->post('product_brand'));
    $this->db->set('product_group', $this->input->post('product_group'));
    $this->db->set('product_tax_input', $this->input->post('product_tax_input'));
    $this->db->set('product_tax_output', $this->input->post('product_tax_output'));
    $this->db->set('mrp_price', $this->input->post('mrp_price'));
    $this->db->set('selling_price', $this->input->post('selling_price'));
    $this->db->set('purchase_cost', $this->input->post('purchase_cost'));
    $this->db->set('landing_cost', $this->input->post('landing_cost'));
    $this->db->set('discount', $this->input->post('discount'));
    //$this->db->set('opening_stock', $this->input->post('opening_stock'));
    $this->db->set('modify_date', time());
    $this->db->where('member_id', $this->parent_user_id);
    $this->db->where('id', $id);
    $this->db->update($this->tablename);
  }

  function getAllRecordsList($member_id, $select = NULL)
  {
    if ($select)
      $this->db->select($select);
    else
      $this->db->select();
    $this->db->where('status', 'Active');
    $this->db->where('member_id', $member_id);
    $this->db->order_by("id", "product_name");
    $query = $this->db->get($this->tablename);
    $record = $query->result();
    return $record;
  }

  function getAllScrapRecordsCount($task = NULL)
  {
    if ($task)
      $this->db->where('product.status', $task);
    $this->db->select("product.id");
    $this->db->join("tbl_product_stock as ps", "ps.product_id = product.id AND ps.status = 'Inactive'");
    $this->db->where('member_id', $this->parent_user_id);
    $this->db->group_by('product.id');
    $query = $this->db->get($this->tablename);
    $row = $query->num_rows();
    return $row;
  }

  function getAllScrapRecords($num, $offset)
  {
    $this->db->select("product.*, count(ps.id) as total_scrap");
    $this->db->join("tbl_product_stock as ps", "ps.product_id = product.id AND ps.status = 'Inactive'");
    $this->db->where('member_id', $this->parent_user_id);
    $this->db->group_by('product.id');
    $this->db->order_by('product.id', 'DESC');
    $query = $this->db->get($this->tablename, $num, $offset);
    $record = $query->result();
    return $record;
  }
}