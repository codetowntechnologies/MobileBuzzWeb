<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Autoacl
{
    function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->helper('url');
        $this->ci->load->library('session');
        $this->ci->load->config('autoacl');
        $this->ci->load->helper('string');

        // Clear error_uri session
        $this->ci->session->unset_userdata('error_uri');

        // Auto check ACL
        $this->check_acl();
    }

    /**
     * check_acl
     * autoacl control logic
     * @return  bool
     *
     */

    function check_acl()
    {
        $current_page = $this->current_uri();


        $role_id = ($this->get_role_id()) ? $this->get_role_id() : $this->ci->config->item('default_role');
        $config_page = $this->ci->config->item('page_control');
        $config_vpp_page = $this->ci->config->item('vpp_control');

        if ($this->get_role_id() == 1) {
            return;
        } elseif ($current_page != $this->ci->config->item('adminName') . '/login/') {
            if ($this->is_allowed($current_page, $role_id, $config_page, $config_vpp_page)) {
                return;
            } else {
                $error_uri = $this->ci->session->userdata('error_uri');
                redirect($error_uri, 'refresh');
            }
        }
    }

    /**
     * autoacl general check
     *
     * @return  bool
     */
    function is_allowed($current_page, $role_id, $config_page, $config_vpp_page)
    {

        log_message('debug', "autoacl -> You're trying to access this page: " . $current_page);
        log_message('debug', "autoacl -> Your role_id is: " . $role_id);
        $filter_page = trim($this->segments_filter($current_page, $config_page, $config_vpp_page));

        if ($filter_page) {
            log_message('debug', "autoacl -> FILTER PAGE return TRUE");


            if (isset($config_page[$filter_page]['allowed'])) {
                log_message('debug', "autoacl -> FILTER PAGE INSIDE THE FIRST ISSET");
                if (in_array($role_id, $config_page[$filter_page]['allowed'])) {
                    log_message('debug', "autoacl -> This page is controlled. But you CAN access.");
                    return TRUE;
                }
                log_message('debug', "autoacl -> READY TO ENTER SECOND ISSET");
            }

            if (isset($config_vpp_page[$filter_page]['allowed'])) {
                log_message('debug', "autoacl -> FILTER PAGE INSIDE THE SECOND ISSET");

                if (in_array($role_id, $config_vpp_page[$filter_page]['allowed'])) {
                    log_message('debug', "autoacl -> This page is VPP controlled. You're ROLE IS ACCEPTED.");

                    // Run function to check vpp
                    if ($this->vpp_check($config_vpp_page, $filter_page)) {
                        log_message('debug', "autoacl -> This page is VPP controlled. You're VPP IS ACCEPTED.");
                        return TRUE;
                    } else {
                        log_message('debug', "autoacl -> This page is VPP controlled. But SORRY you CAN'T access.");
                        log_message('debug', "autoacl -> ERROR_URI: " . $config_vpp_page[$filter_page]['error_uri']);
                        log_message('debug', "autoacl -> ERROR_MSG: " . $config_vpp_page[$filter_page]['error_msg']);
                        $this->ci->session->set_userdata('error_uri', $config_vpp_page[$filter_page]['error_uri']);
                        $this->ci->session->set_flashdata('error_msg', $this->ci->lang->line($config_vpp_page[$filter_page]['error_msg']));
                        return FALSE;
                    }
                } else {
                    log_message('debug', "autoacl -> This page is VPP controlled. But SORRY you CAN'T access.");
                    log_message('debug', "autoacl -> ERROR_URI: " . $config_vpp_page[$filter_page]['error_uri']);
                    log_message('debug', "autoacl -> ERROR_MSG: " . $config_vpp_page[$filter_page]['error_msg']);
                    $this->ci->session->set_userdata('error_uri', $config_vpp_page[$filter_page]['error_uri']);
                    $this->ci->session->set_flashdata('error_msg', $this->ci->lang->line($config_vpp_page[$filter_page]['error_msg']));
                    return FALSE;
                }
            } else {
                log_message('debug', "autoacl -> This page is controlled. But SORRY you CAN'T access.");
                $this->ci->session->set_userdata('error_uri', $config_page[$filter_page]['error_uri']);
                $this->ci->session->set_flashdata('error_msg', $this->ci->lang->line($config_page[$filter_page]['error_msg']));
                return FALSE;
            }
        } else {
            log_message('debug', 'autoacl -> This page is UNCONTROLLED');
            return TRUE;
        }
    }

    /**
     * Get role_id
     *
     * @return  string
     */
    function get_role_id()
    {
        log_message('debug', 'autoacl -> get_role_id INITIALIZED');

        // For example
        // return 2;
        return $this->ci->session->userdata($this->ci->config->item('sess_role_var'));
    }

    /**
     * Get key id from session
     * In common, key id is user_id or username
     *
     * @return  string
     */
    function get_key_id_sess($config_vpp_page, $filter_page)
    {
        log_message('debug', 'autoacl -> get_key_id_sess INITIALIZED');

        // For example
        // return 15;
        return $this->ci->session->userdata($config_vpp_page[$filter_page]['vpp_sess_name']);

    }

    /**
     * Get is_admin
     *
     * @return  bool
     */
    function is_admin()
    {
        if ($this->get_role_id() == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Get current_uri
     * Also remove index or index.php from uri
     *
     * @return  string
     */
    function current_uri($uri = '')
    {
        $segments = $this->ci->uri->segment_array();

        // Remove index and index.php from array
        $segments = array_diff($segments, array('index', 'index.php'));

        // Reindex array
        $segments = array_values($segments);

        foreach ($segments as $segment) {
            $uri .= $segment . '/';
        }
        return $uri;
    }

    /**
     * Filter current uri so that sub module or sub controller also can be controlled
     *
     * @return  string bool
     */
    function segments_filter($current_page, $config_page, $config_vpp_page)
    {
        log_message('debug', 'autoacl -> segments_filter INITIALIZED');
        if (@array_key_exists($current_page, $config_page)) {
            log_message('debug', 'autoacl -> DIRECT DETECTION');
            return $current_page;
        } else {
            $segments = explode("/", $current_page);
            $filter_check_uri = "";
            $true_val = array();
            for ($i = 1; $i <= $this->ci->config->item('segment_max'); $i++) {
                $segment_num = $i - 1;
                if (isset($segments[$segment_num])) {
                    $filter_check_uri .= $segments[$segment_num] . '/';
                }
                log_message('debug', 'autoacl -> CHECKED URI: ' . $filter_check_uri);
                if (@array_key_exists(trim($filter_check_uri), $config_page)) {
                    $true_val[] = $filter_check_uri;
                    log_message('debug', 'autoacl -> FOUND THE PAGE THAT SHOULD BE CONTROLLED');
                }
                if (@array_key_exists(trim($filter_check_uri), $config_vpp_page)) {
                    $true_val[] = $filter_check_uri;
                    log_message('debug', 'autoacl -> FOUND THE VPP PAGE THAT SHOULD BE CONTROLLED');
                }
            }
            if (end($true_val)) {
                $filter_page = trim(end($true_val));
                log_message('debug', 'autoacl -> THIS URI IS NEED TO BE CONTROLLED: ' . $filter_page);
                return $filter_page;
            }
        }
        return FALSE;
    }

    /**
     * vpp_check
     * Compare key id session with key id from uri
     *
     * @return  bool
     */
    function vpp_check($config_vpp_page, $filter_page)
    {
        $key_id_sess = $this->get_key_id_sess($config_vpp_page, $filter_page);
        $key_segment_num = $config_vpp_page[$filter_page]['vpp_key'];
        $key_id_page = $this->ci->uri->segment($key_segment_num);
        if ($key_id_page == $key_id_sess) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}

?>
