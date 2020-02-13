<?php

class Tax_Model extends CI_Model
{
  var $tablename = 'tax';

  function GetRecordsById($id)
  {
    $this->db->where('id', $id);
    $query = $this->db->get($this->tablename);
    $row = $query->row();
    return $row;
  }

  function performMultipleOperations($ids, $task)
  {
    if ($task == 'delete') {
      for ($i = 0; isset($ids[$i]); $i++) {
        $this->db->where('id', $ids[$i]);
        $this->db->delete($this->tablename);
        $message = "Selected records has been deleted successfully.";
      }
    }
    if ($task == 'Active' || $task == 'Inactive') {
      for ($i = 0; isset($ids[$i]); $i++) {
        $this->db->set('status', $task);
        $this->db->where('id', $ids[$i]);
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
      $this->db->delete($this->tablename);
    } else {
      $this->db->set('status', $task);
      $this->db->where('id', $id);
      $this->db->update($this->tablename);
    }
  }

  function updateRecord($id)
  {
    $this->db->set('title', $this->input->post('title'));
    $this->db->set('tax_rate', $this->input->post('tax_rate'));
    $this->db->set('sgst_rate', $this->input->post('sgst_rate'));
    $this->db->set('cgst_rate', $this->input->post('cgst_rate'));
    $this->db->set('igst_rate', $this->input->post('igst_rate'));
    $this->db->where('id', $id);
    $this->db->update($this->tablename);
  }

  function getAdminAllRecordsCount($task = NULL)
  {
    if ($task)
      $this->db->where('status', $task);

    $query = $this->db->get($this->tablename);
    $row = $query->num_rows();
    return $row;
  }

  function getAdminAllRecords($num, $offset)
  {
    $this->db->select();
    $this->db->order_by('id', "desc");
    $query = $this->db->get($this->tablename, $num, $offset);
    $record = $query->result();
    return $record;
  }

  function addRecord()
  {
    $this->db->set('title', $this->input->post('title'));
    $this->db->set('tax_rate', $this->input->post('tax_rate'));
    $this->db->set('sgst_rate', $this->input->post('sgst_rate'));
    $this->db->set('cgst_rate', $this->input->post('cgst_rate'));
    $this->db->set('igst_rate', $this->input->post('igst_rate'));
    $this->db->set('add_date', time());
    $this->db->set('ip', $_SERVER['REMOTE_ADDR']);
    $this->db->set('status', 'Active');
    $this->db->insert($this->tablename);
    return $this->db->insert_id();
  }

  function getAdminAllRecordsFilterCount($filter = NULL, $show_me = NULL, $sort_by = NULL)
  {
    if ($filter != 'NULL') {
      $where = "(title like '%" . $filter . "%' )";
      $this->db->where($where);
    }
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

  function getAdminAllRecordsFilter($filter = NULL, $show_me = NULL, $sort_by = NULL, $num, $offset)
  {
    if ($filter != 'NULL') {
      $where = "(title like '%" . $filter . "%' )";
      $this->db->where($where);
    }

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
    $query = $this->db->get($this->tablename, $num, $offset);
    $record = $query->result();
    return $record;
  }

  function getAllRecords($order_by = null, $sort_by = null)
  {
    $this->db->select();
    $this->db->where('status', "Active");
    if ($order_by == '')
      $order_by = 'tax_rate';
    if ($sort_by == '')
      $sort_by = "asc";
    $this->db->order_by($order_by, $sort_by);
    return $this->db->get($this->tablename)->result();
  }

}
