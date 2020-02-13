<?php
class Admin_Model extends CI_Model
{
	var $tablename		='member';
	var $tablenameLog 	='admin_log';

	function do_login(){
		$this->db->select('id,username,password,privileges');
	 	$this->db->where('username',$this->input->post('login-email'));
		$this->db->where('password',get_encrypted_pass($this->input->post('login-password')));
		$this->db->where('role','administrator');
		$this->db->where('status','Active');

	 	$query = $this->db->get($this->tablename);
	 	$adminRow = $query->row();

		if($adminRow->username)
		{
			$this->session->set_userdata('ADM_ID',$adminRow->id);
			$this->session->set_userdata('PRIVILEDGES',$adminRow->privileges);
			$this->session->set_userdata('adminName',$adminRow->username);
			$this->session->set_userdata('adminAL',1);

			//$sSql = "SELECT parent_id FROM tbl_manager WHERE mng_id IN ($adminRow->privileges)";
			$sSql = "SELECT mng_id FROM tbl_manager WHERE parent_id=0 and mng_id IN ($adminRow->privileges)";
			$query = $this->db->query($sSql);
			$adminPri = $query->result();
			for($j=0; $j<count($adminPri);$j++)
				$parentArr[] = $adminPri[$j]->mng_id;
			$parentArr	=	array_unique($parentArr);
			$this->session->set_userdata('parentArr',$parentArr);
			return '1';
		}
		else
		{
			return '0';
	 	}
	}

	function do_forgot(){
		$this->db->select('username,id,email');
	 	$this->db->where('email',$this->input->post('email'));
	 	$this->db->where('role','administrator');
		$this->db->where('status','Active');
	 	return $this->db->get($this->tablename)->row();
	}

	function do_logout(){
		$this->session->unset_userdata('ADM_ID');
		$this->session->unset_userdata('adminName');
		$this->session->unset_userdata('adminAL');
	}

//=================Update Admin======================================================//

	function updateAdminPersonalProfile($id)
	{
		$this->db->set('display_name', $this->input->post('admin_display_name'));
		$this->db->set('email', $this->input->post('admin_email'));
		$this->db->where('id',$id);
		$this->db->update($this->tablename);
	}

	function updateAdminAvatar($id,$image)
	{
		$rec = $this->getAdminrecordById($id);
		unlink('./assets/uploads/member/'.$id.'/profile_image/'.$rec->profile_image);
		$this->db->set('profile_image',$image);
		$this->db->where('id',$id);
		$this->db->update($this->tablename);
	}

	function updateAdminPasswords($id)
	{
		$this->db->set('password',get_encrypted_pass($this->input->post('new_password')));
		$this->db->where('id',$id);
		$this->db->update($this->tablename);
	}

	function getAdminLogsData($id)
	{
		$this->db->order_by("log_id","desc");
		$this->db->where("admin_id",$id);
	 	$query=$this->db->get($this->tablenameLog);
		$record	=	$query->result();
		return $record;
	}

	function getAdminSomeDetail($id)
	{
	 	$this->db->select('profile_image,display_name');
		$this->db->where('id',$id);
	 	$query = $this->db->get($this->tablename);
	 	return $record	=	 $query->row();
	}

//=====================================================================================//

	function getAdminrecordById($id)
	{
	 	$this->db->where('id',$id);
	 	$query=$this->db->get($this->tablename);
		return $row= $query->row();
	}
	
	function update_settings($member_id,$filename)
	{
		$this->db->set('company_name',$this->input->post('company_name'));
		$this->db->set('email',$this->input->post('email'));
		$this->db->set('website',$this->input->post('website'));
		$this->db->set('address',$this->input->post('address'));
		$this->db->set('region',$this->input->post('state_id'));
		$this->db->set('city',$this->input->post('city_id'));
		$this->db->set('pincode',$this->input->post('pincode'));
		$this->db->set('phone_number',$this->input->post('phone_number'));
		$this->db->set('phone_number_2',$this->input->post('phone_number_2'));
		$this->db->set('gst_number',$this->input->post('gst_number'));
		$this->db->set('pan_number',$this->input->post('pan_number'));
		$this->db->set('food_licence_number',$this->input->post('food_licence_number'));
		$this->db->set('pf_number',$this->input->post('pf_number'));
		$this->db->set('tan_number',$this->input->post('tan_number'));
		
		if($filename!='')
		{
			$rec = $this->getAdminrecordById($member_id);
			@unlink('./assets/uploads/company/'.$rec->profile_image);
			$this->db->set('profile_image', $filename);
		}
		
		$this->db->where('id',$member_id);
		$this->db->update($this->tablename);
	}

	#================================== Insert Log In Detail When Admin Login=============================================#
	function create_log($browser,$version,$platform,$mobile){
		$operating_system=($mobile!="")?$platform.'-'.$mobile:$platform;
		$data = array(
		   'admin_id' => $this->session->userdata('ADM_ID'),
		   'admin_name' => $this->input->post('login-email'),
		   'login_date' => date("Y-m-d H:i:s"),
		   'login_ip' => $_SERVER['REMOTE_ADDR'],
		   'operating_system' => $operating_system,
		   'browser' => $browser."-Version-".$version
		);
		$this->db->insert($this->tablenameLog, $data);
	}

	public function forgotPassword(){
		$strnew 		= 'abcXdefgRhijklmnoYpqrstGuvwxJyz012345A6789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$shuff 			= str_shuffle($strnew);
		$newPassword	= substr($shuff,0,8) ;
		$this->session->set_userdata('NEWPASSWORD',$newPassword);

		$this->db->set('password',get_encrypted_pass($newPassword));
		$this->db->where('email',$this->input->post('email'));
		$this->db->update($this->tablename);
	}

	public function get_company_info($id){
		
		return $this->db->select('id, company_name, email, website, address, region, city, pincode, phone_number, phone_number_2,tbl_region.region_name, tbl_city.name as city_name')
			->select("IF(STRCMP(tbl_member.profile_image,''),CONCAT('" . base_url() . "uploads/company/',tbl_member.profile_image),CONCAT('" . base_url() . config_item('path_media_noimage') . config_item('noimage_user') . "')) as company_logo", false)
			->join('tbl_region','tbl_region.region_id = tbl_member.region','left')
			->join('tbl_city','tbl_city.cty_id = tbl_member.city','left')
			->where('id', $id)
			->get('member')->row();
	}

}