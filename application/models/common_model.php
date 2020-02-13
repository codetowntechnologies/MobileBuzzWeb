<?php
class Common_Model extends CI_Model
{
	function __construct()
	{
		global $URI, $CFG, $IN;
        $ci = get_instance();
        $ci->load->config('config');
        $this->load->library('user_agent');
		//======== admin facebook Twitter link =================//

		$this->db->select('copyright,facebook_url,pinterest_url,twitter_url,youtube,linkedin,google_url,page_size_admin,page_size_front,site_title,logo,contact_phone,contact_address,contact_email,support_email,site_currency,meta_title,meta_keywords,meta_description,site_info');
		$query = $this->db->get('tbl_website_config');
		$row=$query->row();
	    $ci->config->set_item('facebook',$row->facebook_url);
	    $ci->config->set_item('pinterest',$row->pinterest_url);
		$ci->config->set_item('twitter',$row->twitter_url);
		$ci->config->set_item('linkedin',$row->linkedin);
		$ci->config->set_item('google',$row->google_url);
		$ci->config->set_item('youtube',$row->youtube);
		$ci->config->set_item('page_size_front',$row->page_size_front);
		$ci->config->set_item('page_size_admin',$row->page_size_admin);
		$ci->config->set_item('site_title',$row->site_title);
		$ci->config->set_item('meta_title',$row->meta_title);
		$ci->config->set_item('meta_keywords',$row->meta_keywords);
		$ci->config->set_item('meta_description',$row->meta_description);
		$ci->config->set_item('logo',$row->logo);
		$ci->config->set_item('contact_phone',$row->contact_phone);
		$ci->config->set_item('contact_address',$row->contact_address);
		$ci->config->set_item('contact_email',$row->contact_email);
		$ci->config->set_item('support_email',$row->support_email);
		$ci->config->set_item('site_currency',$row->site_currency);
		$ci->config->set_item('copyright',$row->copyright);
		$ci->config->set_item('site_info',$row->site_info);
		$ci->config->set_item('currency_iso','INR');
		$ci->config->set_item('body_class','home-1');

		#========== For Check Ban ip =============
		//$this->checkForBlockIP();
		#========== For Check Site on off =============
		//$this->checkSiteOnOff();

		//$this->tempSiteUnavailble();
		//===== For Admin Privileges ======================================//
		if($this->session->userdata('ADM_ID')!='')
		{
			$this->db->where('parent_id','0');
			$this->db->where('status','Active');
			$this->db->order_by('display_order','ASC');
			$query = $this->db->get('tbl_manager');
			$managers	=	$query->result();

			for($i=0;isset($managers[$i]);$i++)
				$managers[$i]->submanagers	=	$this->getSubmanagers($managers[$i]->mng_id);
			$ci->config->set_item('adminManagers',$managers);
		}

		/***************************** Visitor Log Detail ***************************************/
	    //$this->create_log($this->agent->browser(),$this->agent->version(),$this->agent->platform(),$this->agent->mobile());
		/***************************** Visitor Log Detail ***************************************/


		if($this->session->userdata('ADM_ID')!='' && $this->session->userdata('ADM_ID')!=1 && eregi(config_item('adminName'),$_SERVER['REQUEST_URI']))
		{
			/*
			$cur_uri =  $this->uri->segment(2);
			$myarrsubAdm = array("updateAdmin","adminProfile","index","adminLogout","admin-profile");
			if(current_url() != site_url(config_item('adminName')) && ! in_array($cur_uri,$myarrsubAdm))
			{
				$allowed = $this->checkAdminAccess();
				if($allowed==false)
				{
					die("You can't access requested page. To go Back <a href='".site_url(config_item('adminName'))."' title='click here'>CLICK HERE</a> ");
					redirect(config_item('adminName'));
				//$this->session->set_userdata('ERROR_MESSAGE',"");
				}
			}*/
		}

		if($this->session->userdata('memberId')!='')
		{
			$this->db->select('username,email');
			$this->db->where('id',$this->session->userdata('memberId'));
			$query	=	$this->db->get('tbl_member');
			$row	=	$query->row();

			$ci->config->set_item('login_username',$row->username);
			$ci->config->set_item('login_email',$row->email);
		}


		$this->set_default_country();
	}

	function set_default_country()
	{
		$this->db->select('id');
		$this->db->where('code','IN');
		$coutry_id = $this->db->get('country')->row()->id;

		$this->config->set_item('default_country',$coutry_id);
	}

	function checkMemberLogin()
	{
		if($this->session->userdata('memberId') == '')
		{
			$this->session->set_flashdata('ERROR_MESSAGE', "Please login to access this page");
			redirect('login');
		}
	}

	#=============Function Check IP Blocked=============================================================#
	function checkForBlockIP()
	{
	   $currenturl = current_url();
	   if($currenturl!=site_url('admin/login'))
	   {
		   if($this->session->userdata('ADM_ID')!=1)
			{
			$this->db->where('ip',$_SERVER["REMOTE_ADDR"]);
			$query=$this->db->get('tbl_ban_ip');
			$result= $query->num_rows();
					if($result>0)
					{
						echo '<h1 align="center">Your IP Address is '.$_SERVER["REMOTE_ADDR"].' Which is Currently Block due to security reasons, please contact to admin('.$this->config->item('contact_email').') for further details.</h1>'; die;
					}
					else
					{
						return true;
					}
			}
		}
	}

	#=============Function Create Unique Slug===========================================================#
	function create_unique_slug_for_common($app_title,$table)
	{
		$slug = url_title($app_title);
		$slug = strtolower($slug);
		$i = 0;
		$params = array ();
		$params['slug'] = $slug;
		while ($this->db->where($params)->get($table)->num_rows())
		{
			if (!preg_match ('/-{1}[0-9]+$/', $slug ))
				{
					$slug .= '-' . ++$i;
				}
			else
				{
					$slug = preg_replace ('/[0-9]+$/', ++$i, $slug );
				}
			$params ['slug'] = $slug;
		}
		$app_title=$slug;
		return $app_title;
	}

	function getSingleFieldFromAnyTable($field_name,$condition_coloum,$condition_value,$table_name)
	{
		$this->db->select($field_name);
		$this->db->where($condition_coloum,$condition_value);
		$query = $this->db->get($table_name);
	 	$data = $query->row();
		return $data->$field_name;
	}

	function getSingleRowFromAnyTable($condition_coloum,$condition_value,$table_name)
	{
		$this->db->select($field_name);
		$this->db->where($condition_coloum,$condition_value);
		$query = $this->db->get($table_name);
	 	$data = $query->row();
		return $data;
	}

	function getCountAllFromAnyTable($condition_coloum,$condition_value,$table_name,$status)
	{
		$this->db->where($condition_coloum,$condition_value);
		if($status)
		$this->db->where('status',$status);
		$query = $this->db->get($table_name);
	 	$nums = $query->num_rows();
		return $nums;
	}

	function getFieldsFromAnyTable($condition_coloum,$condition_value,$table_name,$order_coloum,$status)
	{
		$this->db->where($condition_coloum,$condition_value);
		if($status)
		$this->db->where('status',$status);
		if($order_coloum)
		$this->db->order_by($order_coloum,'asc');
		$query = $this->db->get($table_name);
	 	$data = $query->result();
		return $data;
	}

	function getAllRegionbyCountry($id)
	{
		$this->db->where('country_id',$id);
		$this->db->where('status','Active');
	 	$query=$this->db->get('region');
		$record	=	$query->result();
		return $record;
	}

	function getAllstates($type)
	{
		if($type=='affiliated')
		$this->db->where('affiliated_status','Yes');
		if($type=='addressing')
		$this->db->where('addressing_status','Yes');

		$this->db->where('status','Active');
	 	$query=$this->db->get('tbl_states');
		$record	=	$query->result();
		return $record;
	}

	function getAllPages()
	{
		$this->db->where('status','Active');
		$this->db->order_by('pg_id','DESC');
	 	return $this->db->get('page')->result();
	}
	
	function getAllStateFront($country_id = 88)
	{
		$this->db->where('country_id',$country_id);
		$this->db->where('status','Active');
		$this->db->order_by('region_name','ASC');
		$query	=	$this->db->get('tbl_region');
		$result	=	$query->result();
		return $result;
	}
	
	function getAllCityFront($state_id)
	{
		$this->db->where('sta_id',$state_id);
		$this->db->where('status','Active');
		$this->db->order_by('name','ASC');
		$query	=	$this->db->get('tbl_city');
		$result	=	$query->result();
		return $result;
	}
	
	function getAllAreaFront($city_id)
	{
		$this->db->set('member_id', $this->session->userdata('ADM_ID'));
		$this->db->where('city_id',$city_id);
		$this->db->where('status','Active');
		$this->db->order_by('name','ASC');
		$query	=	$this->db->get('tbl_area');
		$result	=	$query->result();
		return $result;
	}

	function getFieldsFromAnyTableTwoCondtion($condition_coloum1,$condition_value1,$condition_coloum2,$condition_value2,$table_name,$order_coloum,$order_by,$status,$limit,$group_by)
	{
		$this->db->where($condition_coloum1,$condition_value1);
		if($condition_coloum2)
		$this->db->where($condition_coloum2,$condition_value2);
		if($status)
		$this->db->where('status',$status);
		if($limit)
		$this->db->limit($limit);
		if($order_coloum)
		$this->db->order_by($order_coloum,$order_by);
		if($group_by)
		$this->db->group_by($group_by);
		$query = $this->db->get($table_name);
		$data = $query->result();
		return $data;
	}

	function showLimitedText($string,$len)
	{
		$string = strip_tags($string);
		if (strlen($string) > $len)
			$string = substr($string, 0, $len-3) . "...";
		return $string;
	}

	protected function getSubmanagers($id)
	{
		$this->db->where('parent_id',$id);
		$this->db->where('status','Active');
		$this->db->order_by('display_order','ASC');
		$query = $this->db->get('tbl_manager');
		$managers	=	$query->result();
		return $managers;
	}

	function getAllcity($country_id=NULL,$region_id=NULL)
	{
		if($region_id)
		$this->db->where(region,$region_id);
		if($country_id)
		$this->db->where(country_id,$country_id);

		$query = $this->db->get('tbl_city');
	 	$data = $query->result();
		return $data;
	}

	function checkAdminAccess()
	{
		$total_segments	=	$this->uri->total_segments();
		$segments		=	$this->uri->segment_array();
		array_shift($segments);
		if($total_segments > 0)
		{
			for($i = 1 ; $i <= $total_segments-1; $i++)
			{
				$current_url	=	implode('/',$segments);
				$this->db->where('page_link',$current_url);
				//$this->db->where('status','Active');
				$query=$this->db->get('tbl_manager');
				$record	=	$query->row();
				if($record)
				{
					$pArr = explode(',', $this->session->userdata('PRIVILEDGES'));
					 if(in_array($record->mng_id, $pArr))
						return true;
						else
						return false;
				}
				else
				{
					array_pop($segments);
				}
			}
		}
		return false;
	}

	function sendMessage($msg,$mobile_number)
	{
		//$myurl_fre			=   'http://sms2.tradelitindia.com/sendsms.jsp?user=codesprt&password=codesprt&sms='.urlencode($msg).'&mobiles='.$mobile_number.'&senderid=AKFIPC';
		$gt_fre				=   file_get_contents($myurl_fre);
	}

	function getAllCategoryFront($parent_id,$popular=null)
	{
		$this->db->select('id,parent_id,slug,title,icon');
		$this->db->where('status','Active');
		if($popular)
		$this->db->where('popular','Yes');
		$this->db->where('parent_id',$parent_id);
		$this->db->order_by('title','ASC');
		return	$this->db->get('tbl_category')->result();
	}

	function getprimaryimage($type,$id,$options)
    {
		if($type == 'deal')
		{
			$this->load->model('deal_model');
			$imgdata= $this->deal_model->getprimaryimage($id);


			if($imgdata)
			{
				$imgURL =	site_url().image('uploads/deals/'.$imgdata->filename,array($options['size']['height'],$options['size']['width']),$options['option']);
			}
			else
			{
				//$imgURL =	site_url().image('uploads/no-image/product-small.jpg', array($options['size']['height'],$options['size']['width']),$options['option']);
				$imgURL =	$this->config->item('templateassets').'img/no_picture.png';
			}
		}
        return $imgURL;
    }

	function create_log($browser,$version,$platform,$mobile)
	{
		$this->db->select('login_ip');
		$this->db->where('login_ip',$_SERVER['REMOTE_ADDR']);
		$this->db->where('login_date',date('Y-m-d'));
		$query = $this->db->get('tbl_visitor_log');
		$admin_logs = $query->result();

		if(sizeof($admin_logs)==0)
		{
			$operating_system=($mobile!="")?$platform.'-'.$mobile:$platform;
			$data = array(
			   'login_date' => date('Y-m-d'),
			   'login_date_strtotime' => time(),
			   'login_ip' => $_SERVER['REMOTE_ADDR'],
			   'operating_system' => $operating_system,
			   'browser' => $browser."-Version-".$version
			);
			$this->db->insert('tbl_visitor_log', $data);
		}
	}

	function getAllVisitors()
  	{
		return $this->db->get('tbl_visitor_log')->num_rows();
	}

	function getAllTodayVisitors()
  	{
		$current = date('Y-m-d');
		$this->db->select('count(*) as num_rows');
		$this->db->where('login_date',$current);
		$query = $this->db->get('tbl_visitor_log');
		return $managers	=	$query->num_rows();
	}

	function getAllThisWeekVisitors()
  	{
		$date_less_seven = strtotime(date('Y-m-d',strtotime('-7 days')));
		$current = time();
		$this->db->select('DISTINCT(login_ip)');
		$this->db->where('login_date_strtotime between '.$date_less_seven.' and '.  $current);
		$query = $this->db->get('tbl_visitor_log');
		return $managers	=	$query->num_rows();
	}

	function getAllThisMonthVisitors()
  	{
		$date_less_seven = strtotime(date('Y-m-d',strtotime('-30 days')));
		$current = time();
		$this->db->select('DISTINCT(login_ip)');
		$this->db->where('login_date_strtotime between '.$date_less_seven.' and '.  $current);
		$query = $this->db->get('tbl_visitor_log');
		return $managers	=	$query->num_rows();
	}

	function getspecialdeal()
	{
		$this->db->where('added_by','admin');
		$this->db->where('deal_type','special');
		$this->db->where('approve_status','Active');
		$this->db->where('status','Active');
		$this->db->order_by('id','DESC');
	 	return $this->db->get('deals')->row();
	}

	#=================== Common Function for all Controllers =================#
	public function paging($config)
	{
		$total_pages=ceil($config['total_rows']/$config['per_page']);
		$start=max($config['currentpage']-intval($config['per_page']/2), 1);
		$end=$start+$config['per_page']-1;

		if($config['currentpage']==1)
		{
			if($config['total_rows']>$config['per_page'])
			{
				$showing='1-'.$config['per_page'];
			}
			else
			{
				$showing='1-'.$config['total_rows'];
			}
		}
		else
		{
			$showing=((($config['currentpage']-1)*$config['per_page'])+1).'-';
			if(($config['currentpage']*$config['per_page'])>$config['total_rows'])
			{
				$showing.=$config['total_rows'];
			}
			else
			{
				$showing.=($config['currentpage']*$config['per_page']);
			}
		}

		$output='<ul class="pagination">';

		if($config['currentpage']>1)
		{
			$output.='<li><a href="'.$config['base_url'].($config['currentpage']-1).'"> <i class="fa fa-chevron-left"></i> </a></li>';
		}
		else
		{
			$$output.='<li class="prev disabled "><a> <i class="fa fa-chevron-left"></i> </a></li>';
		}

		for ($i=$start;$i<=$end && $i<= $total_pages;$i++) {
			if($i==$config['currentpage']) {
				$output .= '<li class="active"><a>'.$i.'</a></li>';
			} else {
				$output .='<li><a href="'.$config['base_url'].$i.'">'.$i.'</a></li>';
			}
		}
		$$output .='</li>';

		if($total_pages>$config['currentpage'])
		{
			$$output.='<li class="next"><a href="'.$config['base_url'].($config['currentpage']+1).'"> <i class="fa fa-chevron-right"></i></a></li>';
		}
		else
		{
			$$output.='<li class="next disabled"><a > <i class="fa fa-chevron-right"></i> </a></li>';
		}
		$output.='</ul>';
		return $output;
	}

}