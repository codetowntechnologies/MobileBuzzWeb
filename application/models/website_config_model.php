<?php
class Website_Config_Model extends CI_Model
{
	var $tablename='website_config';
	var $tablenameLog ='admin_log';
	var $tablenameBanIp='ban_ip';


//===== For Update Website settigns =====================//
	function update_website_settings($filename)
	{
		$this->db->set('site_title', $this->input->post('site_title'));
		//$this->db->set('google_analytic_code', $this->input->post('google_analytic'));
		if($this->input->post('google_indexing'))
		$this->db->set('google_indexing', $this->input->post('google_indexing'));
		$this->db->set('page_size_admin', $this->input->post('page_size_admin'));
		$this->db->set('page_size_front', $this->input->post('page_size_front'));

		$this->db->set('contact_phone', $this->input->post('contact_phone'));
		$this->db->set('contact_address', $this->input->post('contact_address'));

		$this->db->set('site_info', $this->input->post('site_info'));

		if($this->input->post('copyright'))
		$this->db->set('copyright', $this->input->post('copyright'));
		if($this->input->post('site_status'))
		$this->db->set('siteon', $this->input->post('site_status'));

		if($filename!='')
		{
			$rec = $this->getAdminrecordById(1);
			@unlink('./assets/uploads/site_logo/'.$rec->logo);
			$this->db->set('logo', $filename);
		}

		$this->db->where('id',1);
		$this->db->update($this->tablename);
	}

	#===============================================================================================================================#

	#======================================Update Paypal Settings========================================================#
	function update_paypal_settings()
	{
		$this->db->set('paypal_email',$this->input->post('paypal_email'));
		$this->db->set('paypal_api_key',$this->input->post('paypal_api_key'));
		$this->db->set('paypal_api_password',$this->input->post('paypal_api_password'));
		$this->db->where('id',1);
		$this->db->update($this->tablename);
	}

	#===============================================================================================================================#

	#===============================================Update Email Settings===========================================================+#
	function update_emails_settings()
	{
		$this->db->set('contact_email', $this->input->post('contact_email'));
		$this->db->set('support_email', $this->input->post('support_email'));
		$this->db->set('email_from_name', $this->input->post('email_from_name'));
		$this->db->set('sender_from_email', $this->input->post('sender_from_email'));
		$this->db->where('id',1);
		$this->db->update($this->tablename);
	}

#=================================================================================================================================#


#===================================Update Social Media Settings===================================================================#
  function update_social_settings()
	{
		if($this->input->post('site_title'))
		$this->db->set('site_title', $this->input->post('site_title'));
		if($this->input->post('meta_keywords'))
		$this->db->set('meta_keywords', $this->input->post('meta_keywords'));
		if($this->input->post('meta_description'))
		$this->db->set('meta_description', $this->input->post('meta_description'));
		if($this->input->post('facebook_share_url'))
		$this->db->set('facebook_url', $this->input->post('facebook_share_url'));
		if($this->input->post('fb_api_key'))
		$this->db->set('fb_api_key', $this->input->post('fb_api_key'));
		if($this->input->post('fb_api_secret'))
		$this->db->set('fb_api_secret', $this->input->post('fb_api_secret'));
		if($this->input->post('twitter_share_url'))
		$this->db->set('twitter_url', $this->input->post('twitter_share_url'));
		if($this->input->post('tw_api_key'))
		$this->db->set('tw_api_key', $this->input->post('tw_api_key'));
		if($this->input->post('tw_api_secret'))
		$this->db->set('tw_api_secret', $this->input->post('tw_api_secret'));
		if($this->input->post('google'))
		$this->db->set('google_url', $this->input->post('google'));
		if($this->input->post('pinterest_url'))
		$this->db->set('pinterest_url', $this->input->post('pinterest_url'));
		if($this->input->post('google_plus_share_url'))
		$this->db->set('google_plus_share_url', $this->input->post('google_plus_share_url'));
		if($this->input->post('youtube'))
		$this->db->set('youtube', $this->input->post('youtube'));

		$this->db->where('id',1);
		$this->db->update($this->tablename);
	}
	#===================================================================================================================================#


	//=============== Bane Ip functions starts here ======================================//
	function getAllBanIpCount()
	{
		$this->db->select('COUNT(*) AS numrows');
	 	$query=$this->db->get($this->tablenameBanIp);
		$row= $query->row();
	 	return $row->numrows;
	}
	//=============== Bane Ip functions starts here ======================================//
	function getAllAllowIpCount(){
		$this->db->select('COUNT(*) AS numrows');
	 	$query=$this->db->get($this->tablenameAllowIp);
		$row= $query->row();
	 	return $row->numrows;
	}
	//=============== Get All Member Count By Type======================================//
	function countMemberRecords($graph_start_date,$graph_end_date,$member_type){
		$this->db->select('COUNT(*) AS numrows');
		if($graph_end_date)
		$this->db->where('add_date <=',$graph_end_date);
		if($graph_start_date)
		$this->db->where('add_date >=',$graph_start_date);
		$this->db->where('member_type',$member_type);
	 	$query=$this->db->get('member');
		$row= $query->row();
	 	return $row->numrows;
	}

	#=====================================BAn IP Functions==============================#
	function performMultipleOperations($ids,$task)
		{
			if($task=='delete')
				{
					for($i=0;isset($ids[$i]);$i++)
						{
							$this->db->where('pg_id',$ids[$i]);
							$this->db->delete($this->tablenameBanIp);

						}
					$message	=	"Selected records has been deleted successfully.";
				}
			if($task=='Active' || $task=='Inactive' )
				{
					for($i=0;isset($ids[$i]);$i++)
						{
							$this->db->set('status',$task);
							$this->db->where('pg_id',$ids[$i]);
							$this->db->update($this->tablenameBanIp);
						}
					$message	=	"Selected records has been ".$task." successfully.";
				}
			return $message;
		}

	function getAllBanIP($num,$offset)
	{
		$this->db->select();
		$this->db->order_by("id","desc");
	 	$query=$this->db->get($this->tablenameBanIp,$num,$offset);
		$record	=$query->result();
		return $record;
	}

	function getSingleBanIPBYID($id)
	{
		$this->db->select();
		$this->db->where('id',$id);
	 	$query=$this->db->get($this->tablenameBanIp);
		$row	=	$query->row();
		return $row;
	}

	function ban_ip(){
		$this->db->set('ip', $this->input->post('ip'));
		$this->db->set('add_date',time());
		$this->db->insert($this->tablenameBanIp);
	}

	function editBan_ip($id)
	{
		$this->db->set('ip', $this->input->post('ip'));
		$this->db->where('id',$id);
		$this->db->update($this->tablenameBanIp);
	}
	//=============== Bane Ip functions Ends here ======================================//


	//=============== Allow Ip Start==================================//
	function getAllAllowIP($num,$offset){
		$this->db->select();
		$this->db->order_by("id","desc");
	 	$query=$this->db->get($this->tablenameAllowIp,$num,$offset);
		$record	=	$query->result();
		return $record;
	}
	function allow_ip(){
		$this->db->set('ip', $this->input->post('ip'));
		$this->db->insert($this->tablenameAllowIp);
	}
	//=============== Allow Ip End==================================//



	//=================== For Subadmin Functions Starts Here========================================================//
	function getAdminsWithTaskCount($task=NULL)
	{
		$this->db->select('COUNT(*) AS numrows');
		if($task!='')
		$this->db->where('status',$task);

	 	$query=$this->db->get($this->tablename);
		$row= $query->row();

	 	return $row->numrows;
	}
    function getAdminsWithTask($num,$offset)
	{
		$this->db->order_by('id','ASC');
	 	$query=$this->db->get($this->tablename,$num,$offset);
		return $row= $query->result();
	}
	function getAllParentManagers()
	{
		$this->db->order_by('display_order','ASC');
		$this->db->where('parent_id',0);
		$this->db->where('status','Active');
	 	$query=$this->db->get('manager');
		return $row= $query->result();
	}
	function getAllParentSubManagers($id)
	{
		$this->db->where('parent_id',$id);
	 	$query=$this->db->get('manager');
		return $row= $query->result();
	}
	function getAdminrecordById($id)
	{
	 	$this->db->where('id',$id);
	 	$query=$this->db->get('website_config');
		return $row= $query->row();
	}

	function countAllMemberByTypeAndStatus($status,$type)//escort only
	{
		if($status=='Active')
		{
			$this->db->where('account_close_by_member','No');
			$this->db->where('account_delete_by_member','No');
			$this->db->where('status','Active');
		}
		$this->db->where('member_type',$type);
		$query=$this->db->get('member');
		return $query->num_rows();
	}
	function getLastMonthRegistredMemberByType($date,$type)
	{
		if($type)
		$this->db->where('member_type',$type);
		$this->db->select('COUNT(*) AS numrows');
		$this->db->where('add_date >',$date);
	 	$query=$this->db->get('member');
		$row= $query->row();
	 	return $row->numrows;
	}


	function sendContactEmailToAdmin($insertArray){
		$this->db->select('contact_email,contact_address,contact_emails,phone_numbers,google_map_iframe');
	 	$this->db->where('id',1);
	 	$query = $this->db->get($this->tablename);
	 	return $query->row();
	}

	function get_website_settings(){
		$this->db->select('');
	 	$this->db->where('id',1);
	 	$query = $this->db->get($this->tablename);
	 	return $query->row();
	}

}