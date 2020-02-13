<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Api_model extends CI_Model
{
    public function generate_user_folder($mem_id)
    {
        @mkdir("assets/media/users/" . $mem_id, 0777, true);
        @chmod("assets/media/users/" . $mem_id, 0777);
        @mkdir("assets/media/users/" . $mem_id . "/images/", 0777, true);
        @chmod("assets/media/users/" . $mem_id . "/images/", 0777);
    }

    public function employee_login($params)
    {
        $result = array();
        $status = 'error';
        $msg = '';

        $userdata = $this->db->select('id,password,user_type,email,name,device_id')
          ->where('email', $params['email'])
          ->where('user_type', 'employee')
          ->where('role', 'employee')
          ->get('member')->row();

        if ($userdata) {
            $password = $userdata->password;
            if (get_encrypted_pass($params['password']) == $password) {
                $proceed  = false;
                if($userdata->device_id == ''){
                    $proceed  = true;
                }else if($userdata->device_id == $params['device_id']){
                    $proceed  = true;
                }else{
                    $status = 'error';
                    $msg = config_item('error_login_device_not_match');
                }

                if($proceed == true){
                    $user_login_date= date('Y-m-d H:i:s');
                    $device_id      = $params['device_id'];

                    $user_update = array('user_last_login_date' => $user_login_date,'device_id' => $device_id);
                    $this->db->where('id', $userdata->id);
                    $this->db->update('member', $user_update);

                    $result['data'] = $userdata;
                    $status = 'success';
                    $msg = config_item('success');
                }
            } else {
                $status = 'error';
                $msg = config_item('error_login_not_match');
            }
        } else {
            $status = 'error';
            $msg = config_item('error_login_email_not_match');
        }

        $result['replyStatus'] = $status;
        $result['replyMessage'] = $msg;

        return $result;
    }

    public function getcompanieslist()
    {
        $this->db->select('id,company_name');
        $this->db->where('status','Active');
        $this->db->order_by('company_name','asc');
        $output['data']=$this->db->get('company')->result();
        $output['replyStatus']='success';
        $output['replyMessage']='';
        return $output;
    }

    public function getProfileDetail($user_id, $select = null)
    {
        return $this->db->select($select ? $select : 'user_id,user_type,user_first_name as first_name,user_last_name as last_name,user_full_name as user_name,user_gender,user_age,user_about_content as user_about_me,user_location_latitude as location_latitude,user_location_longitude as location_longitude,user_location_address as user_location,user_locality,user_region,profile_image,user_phone_num,user_referral_code')->select("IF(STRCMP(tbl_member.profile_image,''),REPLACE(CONCAT('" . base_url() . config_item('path_media_users') . "',tbl_member.profile_image),'{id}',tbl_member.user_id),'') as profile_image", false)->where('user_id', $user_id)->get('tbl_member')->row();
    }

    public function customer_add($params)
    {
        $result = array();
        $status = 'error';
        $msg = '';

        $userdata = $this->db->select('id,parent_user_id,user_type')->where('id', $params['user_id'])->where('user_type', 'employee')->where('role', 'employee')->get('member')->row();
        if (empty($userdata)) {
            $status = 'error';
            $msg = config_item('user_not_exist');
        } else {

            $customer = $this->db->select('id,user_type')
              ->where('phone_number', $params['phone_number'])
              ->where('role', 'member')
              ->where('user_type', 'user')
              ->get('member')->row();

            if(empty($customer)) {
                $this->db->set('parent_user_id', $userdata->parent_user_id);
                $this->db->set('employee_id', $this->input->post('user_id'));
                $this->db->set('name', $this->input->post('name'));
                $this->db->set('phone_number', $this->input->post('phone_number'));
                $this->db->set('dob', date('Y-m-d',strtotime($this->input->post('dob'))));

                $this->db->set('role', 'member');
                $this->db->set('user_type', 'user');

                $this->db->set('add_date', time());
                $this->db->insert('member');
                $customer_id = $this->db->insert_id();
            }else{
                $customer_id = $customer->id;
            }

            $this->db->set('customer_id', $customer_id);
            $this->db->set('name', $this->input->post('name'));
            $this->db->set('phone_number', $this->input->post('phone_number'));
            $this->db->set('dob', date('Y-m-d',strtotime($this->input->post('dob'))));
            $this->db->set('company_id', $this->input->post('company_id'));
            $this->db->set('model', $this->input->post('model'));
            $this->db->set('visiting_type', $this->input->post('visiting_type'));
            $this->db->set('payment_mode', $this->input->post('payment_mode'));
            $this->db->set('due_amount', $this->input->post('due_amount'));
            $this->db->set('description', $this->input->post('description'));
            $this->db->set('visit_date', date('Y-m-d H:i:s'));
            $this->db->set('add_date', time());
            $this->db->set('ip', $this->input->ip_address());
            $this->db->insert('customer_visit');

            $status = 'success';
            $msg = config_item('success');
        }
        $result['replyStatus'] = $status;
        $result['replyMessage'] = $msg;

        return $result;
    }

    public function employee_attendance_save($params)
    {
        $result = array();
        $status = 'error';
        $msg    = '';

        $userdata = $this->db->select('id,parent_user_id,user_type')->where('id', $params['user_id'])->where('user_type', 'employee')->where('role', 'employee')->get('member')->row();
        if (empty($userdata)) {
            $status = 'error';
            $msg = config_item('user_not_exist');
        } else {

            $attendance = $this->db->select('id')
              ->where('member_id', $params['user_id'])
              ->where('task', $params['task'])
              ->where('DATE(add_date)', date('Y-m-d'))
              ->get('attendance')->row();

            if(empty($attendance)) {

                $address = $this->db->select('id')
                  ->where('member_id', $userdata->parent_user_id)
                  ->where('latitude', $params['latitude'])
                  ->where('longitude', $params['longitude'])
                  ->get('superuser_address')->row();

                if(!empty($address)) {
                    $this->db->set('member_id', $params['user_id']);
                    $this->db->set('task', $params['task']);
                    $this->db->set('latitude', $params['latitude']);
                    $this->db->set('longitude', $params['longitude']);
                    $this->db->set('add_date', date('Y-m-d H:i:s'));
                    $this->db->set('add_time', time());
                    $this->db->set('ip', $this->input->ip_address());
                    $this->db->insert('attendance');

                    $status = 'success';
                    $msg = config_item('success');
                }else{
                    $status = 'error';
                    $msg    = 'Sorry, you are not at proper location.';
                }
            }else{
                $status = 'error';
                $msg    = 'Sorry, you already perform this for today.';
            }
        }
        $result['replyStatus'] = $status;
        $result['replyMessage'] = $msg;

        return $result;
    }

    public function superuser_login($params)
    {
        $result = array();
        $status = 'error';
        $msg    = '';

        $userdata = $this->db->select('id,user_type,email,name')
          ->where('username', $params['code'])
          ->where('user_type', 'admin')
          ->where('role', 'administrator')
          ->get('member')->row();

        if ($userdata) {
            $user_login_date= date('Y-m-d H:i:s');
            $user_update    = array('user_last_login_date' => $user_login_date);
            $this->db->where('id', $userdata->id);
            $this->db->update('member', $user_update);

            $result['data'] = $userdata;
            $status = 'success';
            $msg    = config_item('success');

        } else {
            $status = 'error';
            $msg = config_item('error_login_code_not_match');
        }
        $result['replyStatus'] = $status;
        $result['replyMessage'] = $msg;

        return $result;
    }

    public function employee_list($params)
    {
        $result = array();
        $status = 'error';
        $msg = '';

        $userdata = $this->db->select('id,user_type')->where('id', $params['user_id'])->where('role', 'administrator')->get('tbl_member')->row();
        if (empty($userdata)) {
            $status = 'error';
            $msg = config_item('user_not_exist');
        } else {

            $records = $this->db->select('id,name,email,phone_number')
              ->where('parent_user_id', $params['user_id'])
              ->where('user_type', 'employee')->where('role', 'employee')
              ->order_by('id', 'desc')
              ->get('member')->result();

            $result['data'] = $records;
            $status = 'success';
            $msg = config_item('success');
        }
        $result['replyStatus'] = $status;
        $result['replyMessage'] = $msg;

        return $result;
    }

    public function manage_employee($params)
    {
        $result = array();
        $status = 'error';
        $msg    = '';
        $userdata = $this->db->select('id,user_type')->where('id', $params['user_id'])->where('role', 'administrator')->get('member')->row();
        if (empty($userdata)) {
            $status = 'error';
            $msg = config_item('user_not_exist');
        } else {
            if ($params['task'] == 'add') {

                $this->db->set('parent_user_id', $params['user_id']);
                $this->db->set('name', $params['name']);
                $this->db->set('email', $params['email']);
                $this->db->set('phone_number', $params['phone_number']);
                $this->db->set('password', get_encrypted_pass($params['password']));
                $this->db->set('role', 'employee');
                $this->db->set('user_type', 'employee');

                $this->db->set('add_date', time());
                $this->db->insert('member');
                //$customer_id = $this->db->insert_id();

                $status = 'success';
                $msg = config_item('success');

            } elseif ($params['task'] == 'edit') {

                $this->db->set('name', $params['name']);
                $this->db->set('email', $params['email']);
                $this->db->set('phone_number', $params['phone_number']);
                if($params['password'])
                $this->db->set('password', get_encrypted_pass($params['password']));

                $this->db->where('id', $params['id']);
                $this->db->update('member');

                $status = 'success';
                $msg = config_item('success');

            } elseif ($params['task'] == 'remove') {

                $this->db->where('id', $params['id']);
                $this->db->delete('member');

                $status = 'success';
                $msg = config_item('success');
            } else {
                $status = 'error';
                $msg = config_item('error_nothing_update');
            }
        }
        $result['replyStatus'] = $status;
        $result['replyMessage'] = $msg;
        return $result;
    }

    public function employee_add($params)
    {
        $result = array();
        $status = 'error';
        $msg = '';

        $userdata = $this->db->select('id,user_type')->where('id', $params['user_id'])->where('role', 'administrator')->get('member')->row();
        if (empty($userdata)) {
            $status = 'error';
            $msg = config_item('user_not_exist');
        } else {

            $this->db->set('parent_user_id', $params['user_id']);
            $this->db->set('name', $params['name']);
            $this->db->set('email', $params['email']);
            $this->db->set('phone_number', $params['phone_number']);
            $this->db->set('password', get_encrypted_pass($params['password']));
            $this->db->set('role', 'employee');
            $this->db->set('user_type', 'employee');

            $this->db->set('add_date', time());
            $this->db->insert('member');
            $customer_id = $this->db->insert_id();

            $status = 'success';
            $msg = config_item('success');
        }
        $result['replyStatus'] = $status;
        $result['replyMessage'] = $msg;

        return $result;
    }

    public function client_list($params)
    {
        $result = array();
        $status = 'error';
        $msg    = '';

        $userdata = $this->db->select('id,user_type')->where('id', $params['user_id'])->where('role', 'administrator')->get('tbl_member')->row();
        if (empty($userdata)) {
            $status = 'error';
            $msg = config_item('user_not_exist');
        } else {

            if($params['task'] == 'birthday'){
                $this->db->where('dob', date('Y-m-d'));
            }
            if($params['task'] == 'month'){
                $date = strtotime('-4 months');
                $this->db->where('add_date >', $date);
            }

            $records = $this->db->select('id,name,phone_number,dob')
              ->where('parent_user_id', $params['user_id'])
              ->where('user_type', 'user')->where('role', 'member')
              ->order_by('id', 'desc')
              ->get('member')->result();

            $result['data'] = $records;
            $status = 'success';
            $msg = config_item('success');
        }
        $result['replyStatus'] = $status;
        $result['replyMessage'] = $msg;

        return $result;
    }

    public function client_detail($params)
    {
        $result = array();
        $status = 'error';
        $msg    = '';

        $userdata = $this->db->select('id,user_type')->where('id', $params['user_id'])->where('role', 'administrator')->get('tbl_member')->row();
        if (empty($userdata)) {
            $status = 'error';
            $msg = config_item('user_not_exist');
        } else {

            $detail = $this->db->select('id,name,phone_number,dob')
              ->where('parent_user_id', $params['user_id'])
              ->where('id', $params['client_id'])
              ->where('user_type', 'user')->where('role', 'member')
              ->get('member')->row();

            $records = $this->db->select('customer_visit.id,name,phone_number,dob,company_name,model,visiting_type,payment_mode,due_amount,description,visit_date')
              ->where('customer_id', $params['client_id'])
              ->join('company', 'company.id = customer_visit.company_id')
              ->order_by('customer_visit.id', 'desc')
              ->get('customer_visit')->result();

            $data['detail'] = $detail;
            $data['list']   = $records;
            $result['data'] = $data;
            $status = 'success';
            $msg = config_item('success');
        }
        $result['replyStatus'] = $status;
        $result['replyMessage'] = $msg;

        return $result;
    }

    public function client_send_message($params)
    {
        $result = array();
        $status = 'error';
        $msg    = '';

        $userdata = $this->db->select('id,user_type')->where('id', $params['user_id'])->where('role', 'administrator')->get('tbl_member')->row();
        if (empty($userdata)) {
            $status = 'error';
            $msg = config_item('user_not_exist');
        } else {

            //$result['data'] = $records;
            $status = 'success';
            $msg = config_item('success');
        }
        $result['replyStatus'] = $status;
        $result['replyMessage'] = $msg;

        return $result;
    }

    public function client_send_campaign($params)
    {
        $result = array();
        $status = 'error';
        $msg    = '';

        $userdata = $this->db->select('id,user_type')->where('id', $params['user_id'])->where('role', 'administrator')->get('tbl_member')->row();
        if (empty($userdata)) {
            $status = 'error';
            $msg = config_item('user_not_exist');
        } else {

            //$result['data'] = $records;
            $status = 'success';
            $msg = config_item('success');
        }
        $result['replyStatus'] = $status;
        $result['replyMessage'] = $msg;

        return $result;
    }

    public function report_get_employee_client_list($params)
    {
        $result = array();
        $status = 'error';
        $msg    = '';

        $userdata = $this->db->select('id,user_type')->where('id', $params['user_id'])->where('role', 'administrator')->get('tbl_member')->row();
        if (empty($userdata)) {
            $status = 'error';
            $msg = config_item('user_not_exist');
        } else {

            if($params['from_date']){
                $from_date = date('Y-m-d', strtotime($params['from_date']));
                $this->db->where('DATE(visit_date) >=', $from_date);
            }
            if($params['to_date']){
                $to_date = date('Y-m-d', strtotime($params['to_date']));
                $this->db->where('DATE(visit_date) <=', $to_date);
            }

            $records = $this->db->select('customer_visit.*,company_name')
              ->where('parent_user_id', $params['user_id'])
              ->where('employee_id', $params['employee_id'])
              ->where('user_type', 'user')->where('role', 'member')
              ->join('customer_visit', 'customer_visit.customer_id = member.id')
              ->join('company', 'company.id = customer_visit.company_id')
              ->order_by('customer_visit.id', 'asc')
              ->get('member')->result();

            $result['data'] = $records;
            $status = 'success';
            $msg = config_item('success');
        }
        $result['replyStatus']  = $status;
        $result['replyMessage'] = $msg;

        return $result;
    }

    public function report_get_employee_attendance_list($params)
    {
        $result = array();
        $status = 'error';
        $msg    = '';

        $userdata = $this->db->select('id,user_type')->where('id', $params['user_id'])->where('role', 'administrator')->get('tbl_member')->row();
        if (empty($userdata)) {
            $status = 'error';
            $msg = config_item('user_not_exist');
        } else {

            if($params['from_date']){
                $from_date = date('Y-m-d', strtotime($params['from_date']));
                $this->db->where('DATE(tbl_attendance.add_date) >=', $from_date);
            }
            if($params['to_date']){
                $to_date = date('Y-m-d', strtotime($params['to_date']));
                $this->db->where('DATE(tbl_attendance.add_date) <=', $to_date);
            }

            $records = $this->db->select('attendance.id,attendance.member_id,attendance.add_date as in_time,name,phone_number')
              ->select('(Select add_date from tbl_attendance as a where DATE(a.add_date) = DATE(tbl_attendance.add_date) and a.task="in") as in_time')
              ->select('(Select add_date from tbl_attendance as a where DATE(a.add_date) = DATE(tbl_attendance.add_date) and a.task="out") as out_time')
              ->where('parent_user_id', $params['user_id'])
              ->where('member_id', $params['employee_id'])
              ->where('user_type', 'employee')->where('role', 'employee')
              ->join('member', 'member.id = attendance.member_id')
              ->group_by('DATE(tbl_attendance.add_date)')
              ->order_by('DATE(tbl_attendance.add_date)', 'desc')
              ->order_by('attendance.task', 'asc')
              ->get('attendance')->result();

            $result['data'] = $records;
            $status = 'success';
            $msg = config_item('success');
        }
        $result['replyStatus'] = $status;
        $result['replyMessage'] = $msg;

        return $result;
    }

    public function report_get_employee_selling_list($params)
    {
        $result = array();
        $status = 'error';
        $msg    = '';

        $userdata = $this->db->select('id,user_type')->where('id', $params['user_id'])->where('role', 'administrator')->get('tbl_member')->row();
        if (empty($userdata)) {
            $status = 'error';
            $msg = config_item('user_not_exist');
        } else {

            if($params['from_date']){
                $from_date = date('Y-m-d', strtotime($params['from_date']));
                $this->db->where('DATE(visit_date) >=', $from_date);
            }
            if($params['to_date']){
                $to_date = date('Y-m-d', strtotime($params['to_date']));
                $this->db->where('DATE(visit_date) <=', $to_date);
            }

            $records = $this->db->select('customer_visit.*,company_name')
              ->where('parent_user_id', $params['user_id'])
              ->where('employee_id', $params['employee_id'])
              ->where('visiting_type', 'Purchase')
              ->where('user_type', 'user')->where('role', 'member')
              ->join('customer_visit', 'customer_visit.customer_id = member.id')
              ->join('company', 'company.id = customer_visit.company_id')
              ->order_by('customer_visit.id', 'desc')
              ->get('member')->result();

            $result['data'] = $records;
            $status = 'success';
            $msg = config_item('success');
        }
        $result['replyStatus']  = $status;
        $result['replyMessage'] = $msg;

        return $result;
    }

    public function report_get_client_list($params)
    {
        $result = array();
        $status = 'error';
        $msg    = '';

        $userdata = $this->db->select('id,user_type')->where('id', $params['user_id'])->where('role', 'administrator')->get('tbl_member')->row();
        if (empty($userdata)) {
            $status = 'error';
            $msg = config_item('user_not_exist');
        } else {

            if($params['task'] == 'company'){
                $this->db->where('company_id', $params['company_id']);
            }

            if($params['from_date']){
                $from_date = date('Y-m-d', strtotime($params['from_date']));
                $this->db->where('DATE(visit_date) >=', $from_date);
            }
            if($params['to_date']){
                $to_date = date('Y-m-d', strtotime($params['to_date']));
                $this->db->where('DATE(visit_date) <=', $to_date);
            }

            $records = $this->db->select('customer_visit.*,company_name')
              ->where('parent_user_id', $params['user_id'])
              ->where('user_type', 'user')->where('role', 'member')
              ->join('customer_visit', 'customer_visit.customer_id = member.id')
              ->join('company', 'company.id = customer_visit.company_id')
              ->order_by('customer_visit.id', 'asc')
              ->get('member')->result();

            $result['data'] = $records;
            $status = 'success';
            $msg = config_item('success');
        }
        $result['replyStatus']  = $status;
        $result['replyMessage'] = $msg;

        return $result;
    }

    public function report_get_last_client_list($params)
    {
        $result = array();
        $status = 'error';
        $msg    = '';

        $userdata = $this->db->select('id,user_type')->where('id', $params['user_id'])->where('role', 'administrator')->get('tbl_member')->row();
        if (empty($userdata)) {
            $status = 'error';
            $msg = config_item('user_not_exist');
        } else {

            $date = strtotime('-4 months');

            $records = $this->db->select('id,name,phone_number,dob')
              ->where('parent_user_id', $params['user_id'])
              ->where('add_date >', $date)
              ->where('user_type', 'user')->where('role', 'member')
              ->order_by('id', 'asc')
              ->get('member')->result();

            $result['data'] = $records;
            $status = 'success';
            $msg = config_item('success');
        }
        $result['replyStatus'] = $status;
        $result['replyMessage'] = $msg;

        return $result;
    }

}
