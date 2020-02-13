<?php

class Employee_Model extends CI_Model
{
  var $tablename = 'tbl_member';

  #====================== Admin Model functions ===============#

  function getAdminAllRecordsCount($task = NULL, $role = 'employee', $user_type = 'employee', $filter = NULL)
  {
    $this->db->where('role', $role);
    $this->db->where('user_type', $user_type);
    if ($task)
      $this->db->where('status', $task);
    if ($role)
      $this->db->where('role', $role);

    $this->db->where('parent_user_id', $this->parent_user_id);

    if ($filter != NULL && $filter != 'NULL')
      $this->db->like('username', $filter);

    return $this->db->get($this->tablename)->num_rows();
  }

  function getAllAdminRecords($num, $offset, $role = 'employee', $user_type = 'employee', $filter = NULL, $show_me = NULL, $sort_by = NULL)
  {
    $this->db->select();

    $this->db->where('role', $role);
    $this->db->where('user_type', $user_type);

    if ($filter)
      $this->db->like('username', $filter);

    if ($show_me) {
      if ($show_me == 'Active' || $show_me == 'Inactive')
        $this->db->where('status', $show_me);
    }
    $this->db->where('parent_user_id', $this->parent_user_id);
    if ($sort_by) {
      if ($sort_by == 'New')
        $this->db->order_by("id", "desc");
      if ($sort_by == 'Old')
        $this->db->order_by("id", "asc");
      if ($sort_by == 'Asc')
        $this->db->order_by("username", "asc");
      if ($sort_by == 'Desc')
        $this->db->order_by("username", "desc");

    } else {
      $this->db->order_by("id", "desc");
    }
    $query = $this->db->get($this->tablename, $num, $offset);
    $record = $query->result();
    return $record;
  }

  function getRecordsById($id, $select = null)
  {
    if ($select)
      $this->db->select($select);
    else
      $this->db->select('member.*');
    $this->db->select('region.region_name,city.name as city_name');

    $this->db->join('region', 'region.region_id = member.region', 'left');
    $this->db->join('city', 'city.cty_id = member.city', 'left');
    $this->db->where("member.id", $id);
    $this->db->where('parent_user_id', $this->parent_user_id);
    $query = $this->db->get($this->tablename);
    $row = $query->row();
    return $row;
  }

  function add_user($parent_user_id, $filename)
  {
    $this->db->set('parent_user_id', $parent_user_id);
    $this->db->set('name', $this->input->post('name'));
    $this->db->set('password', get_encrypted_pass($this->input->post('password')));
    $this->db->set('phone_number', $this->input->post('phone_number'));
    $this->db->set('phone_number_2', $this->input->post('phone_number_2'));
    $this->db->set('email', $this->input->post('email'));
    $this->db->set('permanent_address', $this->input->post('permanent_address'));
    $this->db->set('address', $this->input->post('address'));
    $this->db->set('employee_type', $this->input->post('employee_type'));
    $this->db->set('pan_number', $this->input->post('pan_number'));
    $this->db->set('bank_account_detail', $this->input->post('bank_account_detail'));
    $this->db->set('target', $this->input->post('target'));
    $this->db->set('area', implode(',', $this->input->post('area_id')));
    $this->db->set('salary', json_encode($this->input->post('salary')));
    if ($filename)
      $this->db->set('profile_image', $filename);

    $this->db->set('role', 'employee');
    $this->db->set('user_type', 'employee');

    $this->db->set('add_date', time());
    $this->db->insert($this->tablename);
    $user_id = $this->db->insert_id();

    $parent_id = substr("000", strlen($parent_user_id)) . $parent_user_id;
    $_id = substr("0000", strlen($user_id)) . $user_id;

    $username = 'EMP' . $parent_id . $_id;
    $qrcode = qrcode($username, 'employee');

    $this->db->set('username', $username);
    $this->db->set('qrcode', $qrcode);
    $this->db->where('id', $user_id);
    $this->db->update($this->tablename);
  }

  function update_user($id, $filename)
  {
    $this->db->set('name', $this->input->post('name'));
    if ($this->input->post('password'))
      $this->db->set('password', get_encrypted_pass($this->input->post('password')));
    $this->db->set('phone_number', $this->input->post('phone_number'));
    $this->db->set('phone_number_2', $this->input->post('phone_number_2'));
    $this->db->set('email', $this->input->post('email'));
    $this->db->set('permanent_address', $this->input->post('permanent_address'));
    $this->db->set('address', $this->input->post('address'));
    $this->db->set('employee_type', $this->input->post('employee_type'));
    $this->db->set('pan_number', $this->input->post('pan_number'));
    $this->db->set('bank_account_detail', $this->input->post('bank_account_detail'));
    $this->db->set('target', $this->input->post('target'));
    $this->db->set('salary', json_encode($this->input->post('salary')));
    $this->db->set('area', implode(',', $this->input->post('area_id')));
    if ($filename)
      $this->db->set('profile_image', $filename);

    $this->db->where('id', $id);
    $this->db->where('parent_user_id', $this->parent_user_id);
    $this->db->update($this->tablename);
  }

  #========= function for multiple operations============#
  function performMultipleOperations($ids, $task)
  {
    $task = ucfirst($task);
    for ($i = 0; isset($ids[$i]); $i++) {
      $records = $this->getRecordsById($ids[$i]);
      if ($records->position != 'sadmin') {
        $this->perform_task($task, $ids[$i]);
      }
    }

    return "Selected records has been modified successfully.";
  }

  #============Function for perform task on the record============#
  function perform_task($task, $id)
  {
    if ($task == 'Delete') {
      $this->db->where('id', $id);
      $this->db->where('parent_user_id', $this->parent_user_id);
      $this->db->delete($this->tablename);
    } else {
      $this->db->set('status', $task);
      $this->db->where('id', $id);
      $this->db->update($this->tablename);
    }
  }

  #====================Admin Model End here==========================#


  function getAllrecordWithTaskCount($task)
  {
    $this->db->select('COUNT(*) AS numrows');
    $this->db->where('status', $task);
    $this->db->where('parent_user_id', $this->parent_user_id);
    $query = $this->db->get($this->tablename);
    $row = $query->row();
    return $row->numrows;
  }

  function getAllRecordFilterCount($filter, $sortby)
  {
    $this->db->select('COUNT(*) AS numrows');
    if ($filter != all)
      $this->db->like('username', $filter);
    if ($filter != all)
      $this->db->like('display_name', $filter);
    if ($sortby != all)
      $this->db->where('status', $sortby);
    $this->db->where('parent_user_id', $this->parent_user_id);
    $query = $this->db->get($this->tablename);
    $row = $query->row();
    return $row->numrows;
  }

  function getAllRecordFilter($filter, $sortby, $num, $offset)
  {
    if ($filter != all)
      $this->db->like('username', $filter);
    if ($filter != all)
      $this->db->like('display_name', $filter);
    if ($sortby != all)
      $this->db->where('status', $sortby);
    $this->db->where('parent_user_id', $this->parent_user_id);
    $this->db->order_by("id", "desc");
    $query = $this->db->get($this->tablename, $num, $offset);
    return $row = $query->result();
  }

  function getAllRecordsReportFilter()
  {
    $this->db->select('member.*,area.name as area_name');
    if ($this->input->get('employee_name'))
      $this->db->like('member.name', $this->input->get('employee_name'));
    if ($this->input->get('area'))
      $this->db->like('area', $this->input->get('area'));

    if ($this->input->get('sort_by')) {
      $order_by = $this->input->get('order_by');
      if ($this->input->get('order_by') == '')
        $order_by = 'asc';

      $this->db->order_by('member.' . $this->input->get('sort_by'), $order_by);
    } else {
      $this->db->order_by("member.id", "desc");
    }
    $this->db->where('role', 'employee');
    $this->db->where('user_type', 'employee');
    $this->db->where('parent_user_id', $this->parent_user_id);

    $this->db->join('area', 'area.id = member.area', 'left');

    $query = $this->db->get($this->tablename);
    return $row = $query->result();
  }

  function delete_rec($id)
  {
    $this->db->where('id', $id);
    $this->db->where('parent_user_id', $this->parent_user_id);
    $this->db->delete($this->tablename);
  }

  function set_status($task, $id)
  {
    $this->db->set('status', $task);
    $this->db->where('parent_user_id', $this->parent_user_id);
    $this->db->where('id', $id);
    $this->db->update($this->tablename);
  }

  function getAllRecordsByMemberId($parent_user_id, $status = NULL, $select = NULL)
  {
    if ($select)
      $this->db->select($select);
    else
      $this->db->select('member.*');
    $this->db->select('region.region_name,city.name as city_name');
    $this->db->join('region', 'region.region_id = member.region', 'left');
    $this->db->join('city', 'city.cty_id = member.city', 'left');
    if ($status)
      $this->db->where('status', $status);
    $this->db->where('role', 'employee');
    $this->db->where('user_type', 'employee');
    $this->db->where('parent_user_id', $parent_user_id);
    $query = $this->db->get($this->tablename);
    return $query->result();
  }

}