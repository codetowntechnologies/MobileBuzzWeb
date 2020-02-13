<?php
/**
 * @Copyright : Creative Bridge
 * @AppName     MobileBuzz
 * @version     V-1.0*
 * @author      Himanshu Gautam
 * @created     16/08/2017 : Sep 16.2017
 *$output['data'] for result
 *$output['replyMessage'] for message
 *$output['replyStatus']  success/error
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller
{
    var $replyStatus = array('success', 'error');
    var $data;

    function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->config('message');
        $this->load->helper('string');
        $this->load->library('user_agent');

        $this->load->model('api_model');
        $this->load->model('mailsending_model');
    }

    private function mailtesting($msg, $methodname)
    {
        //mail('ehimanshugautam@gmail.com',$methodname,$msg);
        //mail('itsgauravjain.gj@gmail.com',$methodname,$msg);
    }

    private function decodedata()
    {
        $data = $this->input->post('request');
        $data = json_decode($data);

        if (sizeof($data) == 0) {

            $this->output
              ->set_content_type('application/json')
              ->set_output(json_encode($data));
        } else {
            return $data;
        }
    }

    private function postvalue($data)
    {
        $str = "";
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $key1 => $val1) {
                    $_POST[$key][$key1] = $val1;
                }
            } else {
                $_POST[$key] = $val;
            }
        }
    }

    private function nullvaluecheck($data, $req)
    {
        if (sizeof($data['data']) == 0) {
            $data['data'] = array();
        }
        $data['req'] = $req;
        return $data;
    }

    public function index()
    {
        $output = array();
        $data = $this->decodedata();
        $this->postvalue($data->data);
        $metnodname = $data->methodName;
        $this->data = (array)$data->data;
        $output = $this->{$metnodname}();
        $output = $this->nullvaluecheck($output, $data->data);
        $output['methodName'] = $data->methodName;

        $this->output
          ->set_content_type('application/json')
          ->set_output(json_encode($output));
    }

    protected function employee_login()
    {
        $this->form_validation->set_rules('email', 'Email', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        $this->form_validation->set_rules('device_id', 'device info', 'trim|required');

        if ($this->form_validation->run()) {
            $response = $this->api_model->employee_login($this->data);
        } else {
            $response['replyStatus'] = 'error';
            $response['replyMessage'] = strip_tags(validation_errors());
        }
        return $response;
    }

    protected function getcompanieslist()
    {
        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
        if ($this->form_validation->run()) {
            $response = $this->api_model->getcompanieslist($this->data);
        } else {
            $response['replyStatus'] = 'error';
            $response['replyMessage'] = strip_tags(validation_errors());
        }
        return $response;
    }

    protected function customer_add()
    {
        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
        $this->form_validation->set_rules('name', 'Name', 'trim|required');
        $this->form_validation->set_rules('phone_number', 'Mobile Number', 'trim|required');
        $this->form_validation->set_rules('dob', 'Date of Birth', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company', 'trim|required');
        $this->form_validation->set_rules('model', 'User Id', 'trim|required');
        $this->form_validation->set_rules('visiting_type', 'Visiting purpose', 'trim|required');
        $this->form_validation->set_rules('payment_mode', 'Payment mode', 'trim|required');
        $this->form_validation->set_rules('due_amount', 'Due amount', 'trim|required');
        $this->form_validation->set_rules('description', 'Description', 'trim');

        if ($this->form_validation->run()) {
            $response = $this->api_model->customer_add($this->data);
        } else {
            $response['replyStatus'] = 'error';
            $response['replyMessage'] = strip_tags(validation_errors());
        }
        return $response;
    }

    protected function employee_attendance_save()
    {
        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
        $this->form_validation->set_rules('task', 'Task', 'trim|required');
        $this->form_validation->set_rules('latitude', 'Location', 'trim|required');
        $this->form_validation->set_rules('longitude', 'Location', 'trim|required');

        if ($this->form_validation->run()) {
            $response = $this->api_model->employee_attendance_save($this->data);
        } else {
            $response['replyStatus'] = 'error';
            $response['replyMessage'] = strip_tags(validation_errors());
        }
        return $response;
    }

    protected function superuser_login()
    {
        $this->form_validation->set_rules('code', 'Code', 'trim|required');

        if ($this->form_validation->run()) {
            $response = $this->api_model->superuser_login($this->data);
        } else {
            $response['replyStatus'] = 'error';
            $response['replyMessage'] = strip_tags(validation_errors());
        }
        return $response;
    }

    protected function employee_list()
    {
        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');

        if ($this->form_validation->run()) {
            $response = $this->api_model->employee_list($this->data);
        } else {
            $response['replyStatus'] = 'error';
            $response['replyMessage'] = strip_tags(validation_errors());
        }
        return $response;
    }

    protected function manage_employee()
    {
        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
        $this->form_validation->set_rules('task', 'Task', 'trim|required|callback_checktask');
        if ($this->input->post('task') == 'add') {
            $this->form_validation->set_rules('name', 'Name', 'trim|required');
            $this->form_validation->set_rules('phone_number', 'Mobile Number', 'trim|required|is_unique[member.phone_number]');
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[member.email]');
            $this->form_validation->set_rules('password', 'Password', 'trim|required');
        }
        if ($this->input->post('task') == 'edit') {

            $detail = $this->db->select('id,name,phone_number,email')
              ->where('id', $this->data['id'])
              ->where('user_type', 'employee')->where('role', 'employee')
              ->get('member')->row();

            $this->form_validation->set_rules('id', 'Id', 'trim|required');
            $this->form_validation->set_rules('name', 'Name', 'trim|required');

            if($this->data['phone_number'] == $detail->phone_number)
            $this->form_validation->set_rules('phone_number', 'Mobile Number', 'trim|required');
            else
            $this->form_validation->set_rules('phone_number', 'Mobile Number', 'trim|required|is_unique[member.phone_number]');

            if($this->data['email'] == $detail->email)
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
            else
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[member.email]');

            $this->form_validation->set_rules('password', 'Password', 'trim');
        }
        if ($this->input->post('task') == 'remove') {
            $this->form_validation->set_rules('id', 'Id', 'trim|required');
        }

        if ($this->form_validation->run()) {
            $response = $this->service_api_model->manage_employee($this->data);
        } else {
            $response['replyStatus'] = 'error';
            $response['replyMessage'] = strip_tags(validation_errors());
        }
        return $response;
    }

    protected function client_list()
    {
        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');

        if ($this->form_validation->run()) {
            $response = $this->api_model->client_list($this->data);
        } else {
            $response['replyStatus'] = 'error';
            $response['replyMessage'] = strip_tags(validation_errors());
        }
        return $response;
    }

    protected function client_detail()
    {
        $this->form_validation->set_rules('user_id', 'Member Id', 'trim|required');
        $this->form_validation->set_rules('client_id', 'Client Id', 'trim|required');
        if ($this->form_validation->run()) {
            $response = $this->api_model->client_detail($this->data);
        } else {
            $response['replyStatus'] = 'error';
            $response['replyMessage'] = strip_tags(validation_errors());
        }
        return $response;
    }

    protected function client_send_message()
    {
        $this->form_validation->set_rules('user_id', 'Member Id', 'trim|required');
        $this->form_validation->set_rules('client_id', 'Client Id', 'trim|required');
        $this->form_validation->set_rules('message', 'Message', 'trim|required');
        if ($this->form_validation->run()) {
            $response = $this->api_model->client_send_message($this->data);
        } else {
            $response['replyStatus'] = 'error';
            $response['replyMessage'] = strip_tags(validation_errors());
        }
        return $response;
    }

    protected function client_send_campaign()
    {
        $this->form_validation->set_rules('user_id', 'Member Id', 'trim|required');
        $this->form_validation->set_rules('message', 'Message', 'trim|required');
        if ($this->form_validation->run()) {
            $response = $this->api_model->client_send_campaign($this->data);
        } else {
            $response['replyStatus'] = 'error';
            $response['replyMessage'] = strip_tags(validation_errors());
        }
        return $response;
    }

    protected function report_get_employee_client_list()
    {
        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
        $this->form_validation->set_rules('employee_id', 'Employee Id', 'trim|required');
        $this->form_validation->set_rules('from_date', 'Date from', 'trim');
        $this->form_validation->set_rules('to_date', 'Date To', 'trim');

        if ($this->form_validation->run()) {
            $response = $this->api_model->report_get_employee_client_list($this->data);
        } else {
            $response['replyStatus'] = 'error';
            $response['replyMessage'] = strip_tags(validation_errors());
        }
        return $response;
    }

    protected function report_get_employee_attendance_list()
    {
        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
        $this->form_validation->set_rules('employee_id', 'Employee Id', 'trim|required');
        $this->form_validation->set_rules('from_date', 'Date from', 'trim');
        $this->form_validation->set_rules('to_date', 'Date To', 'trim');

        if ($this->form_validation->run()) {
            $response = $this->api_model->report_get_employee_attendance_list($this->data);
        } else {
            $response['replyStatus'] = 'error';
            $response['replyMessage'] = strip_tags(validation_errors());
        }
        return $response;
    }

    protected function report_get_employee_selling_list()
    {
        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
        $this->form_validation->set_rules('employee_id', 'Employee Id', 'trim|required');
        $this->form_validation->set_rules('from_date', 'Date from', 'trim');
        $this->form_validation->set_rules('to_date', 'Date To', 'trim');

        if ($this->form_validation->run()) {
            $response = $this->api_model->report_get_employee_selling_list($this->data);
        } else {
            $response['replyStatus'] = 'error';
            $response['replyMessage'] = strip_tags(validation_errors());
        }
        return $response;
    }

    protected function report_get_client_list()
    {
        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
        $this->form_validation->set_rules('task', 'Task', 'trim|required');

        if($this->data['task'] == 'company')
        $this->form_validation->set_rules('company_id', 'Company', 'trim|required');
        $this->form_validation->set_rules('from_date', 'Date from', 'trim');
        $this->form_validation->set_rules('to_date', 'Date To', 'trim');

        if ($this->form_validation->run()) {
            $response = $this->api_model->report_get_client_list($this->data);
        } else {
            $response['replyStatus'] = 'error';
            $response['replyMessage'] = strip_tags(validation_errors());
        }
        return $response;
    }

    protected function report_get_last_client_list()
    {
        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');

        if ($this->form_validation->run()) {
            $response = $this->api_model->report_get_last_client_list($this->data);
        } else {
            $response['replyStatus'] = 'error';
            $response['replyMessage'] = strip_tags(validation_errors());
        }
        return $response;
    }

    public function checkresp()
    {
        if (empty($_POST)) $_POST = $_GET;
        $method = $this->input->post('method');
        $data_type = $this->input->post('data_type');
        $show_post = $this->input->post('show_post');
        if ($method) {
            $this->data = $_POST;
            $data = $this->{$method}();
            if ($show_post == 'y') {
                echo "<pre>"; print_r($_POST);//die;
            } elseif ($data_type == 'array') {
                echo "<pre>"; print_r($data); die;
            } else {
                echo json_encode($data); die;
            }
        } else {
            echo "Enter the method name"; die;
        }
    }

}