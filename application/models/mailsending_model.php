<?php
class Mailsending_Model extends CI_Model
{
    var $from="";
	var $sendermail="ehimanshugautam@gmail.com";
	var $sitelogo='';
	var $site_title='';
	var $contact_email='';
	var $mailtype='html';

	function __construct()
	{
		$this->db->select('email_from_name,sender_from_email,contact_email,site_title,logo,copyright');
		$query = $this->db->get('website_config');
		$row=$query->row();
		$this->sitelogo='<img src="'.base_url().'assets/uploads/site_logo/'.$row->logo.'">';
		$this->from=$row->email_from_name;
		$this->senderemail=$row->sender_from_email;
		$this->site_title=$row->site_title;
		$this->contact_email=$row->contact_email;
		$this->copyright=$row->copyright;
	}

	function templatechoose($id)
	{
		$this->db->where('id',$id);
		return $this->db->get('email_template')->row();
	}

	function memberdata($id)
	{
		$this->db->select('id,name,email');
		$this->db->where('id',$id);
		return $this->db->get('member')->row();
	}
	function tempmemberdata($id)
	{
		$this->db->select('id,name,email,verification_code');
		$this->db->where('id',$id);
		return $this->db->get('temp_member')->row();
	}

	function registrationEmail($member_id)
	{
		$memberRec	=	$this->memberdata($member_id);
		$template	=	$this->templatechoose(1);

		$str=str_replace("{#logo}",$this->sitelogo,$template->template_description);
		$str=str_replace("{#name}",$memberRec->name,$str);
		$str=str_replace("{#email}",$memberRec->email,$str);
		$str=str_replace("{#site_title}",$this->site_title,$str);
		$str=str_replace("{#copyright_text}",$this->copyright,$str);
		$this->email->from($this->senderemail,$this->from);
		$this->email->to($memberRec->email);
		$this->email->mailtype=$this->mailtype;
		$this->email->subject($template->email_subject);
		$this->email->message($str);
		return $this->email->send();
	}

	function send_verification_code($member_id)
	{
		$memberRec	=	$this->tempmemberdata($member_id);
		$template	=	$this->templatechoose(2);

		$str=str_replace("{#logo}",$this->sitelogo,$template->template_description);
		$str=str_replace("{#name}",$memberRec->name,$str);
		$str=str_replace("{#verification_code}",$memberRec->verification_code,$str);
		$str=str_replace("{#site_title}",$this->site_title,$str);
		$str=str_replace("{#copyright_text}",$this->copyright,$str);
		$this->email->from($this->senderemail,$this->from);
		$this->email->to($memberRec->email);
		$this->email->mailtype=$this->mailtype;
		$this->email->subject($template->email_subject);
		$this->email->message($str);
		return $this->email->send();
	}

	function forgotPasswordEmail($memberRec)
	{
		$template	=$this->templatechoose(3);
		$newPassword=$this->session->userdata('NEWPASSWORD');
		$this->session->unset_userdata('NEWPASSWORD');
		$str=str_replace("{#logo}",$this->sitelogo,$template->template_description);
		$str=str_replace("{#name}",$memberRec->name,$str);
		$str=str_replace("{#email}",$memberRec->email,$str);
		$str=str_replace("{#password}",$newPassword,$str);
		$str=str_replace("{#site_title}",$this->site_title,$str);
		$str=str_replace("{#copyright_text}",$this->copyright,$str);
		$this->email->from($this->contact_email, $this->from);
		$this->email->to($memberRec->email);
		$this->email->mailtype=$this->mailtype;
		$this->email->subject($template->email_subject);
		$this->email->message($str);
		return $this->email->send();
	}

	function forgotPasswordEmailAdmin($memberRec)
	{
		$template	=$this->templatechoose(5);
		$newPassword=$this->session->userdata('NEWPASSWORD');
		$this->session->unset_userdata('NEWPASSWORD');
		$str=str_replace("{#logo}",$this->sitelogo,$template->template_description);
		$str=str_replace("{#name}",$memberRec->username,$str);
		$str=str_replace("{#username}",$memberRec->username,$str);
		$str=str_replace("{#email}",$memberRec->email,$str);
		$str=str_replace("{#password}",$newPassword,$str);
		$str=str_replace("{#site_title}",$this->site_title,$str);
		$str=str_replace("{#copyright_text}",$this->copyright,$str);
		$this->email->from($this->contact_email, $this->from);
		$this->email->to($memberRec->email);
		$this->email->mailtype=$this->mailtype;
		$this->email->subject($template->email_subject);
		$this->email->message($str);
		return $this->email->send();
	}

	function mailToMemberForPaymentSuccess($invoiceDetail)
	{
		$memberRec	=	$this->memberdata($invoiceDetail->member_id);
		$template	=	$this->templatechoose(4);

		$str=str_replace("{#logo}",$this->sitelogo,$template->template_description);
		$str=str_replace("{#name}",$memberRec->name,$str);
		$str=str_replace("{#email}",$memberRec->email,$str);

		$products = $invoiceDetail->products;
		foreach($products as $value)
		{
			$this->db->select('promocode,end');
			$this->db->where('id',$value->product_id);
			$det = $this->db->get('tbl_deals')->row();

			$value->code		=	$det->promocode;
			$value->expire_date	=	$det->end;
		}

		$order_raw = $this->load->view($this->config->item('template').'/order_summary_email',['products'=>$products],true);

		$str=str_replace("{#order_raw}",$order_raw,$str);
		$str=str_replace("{#site_title}",$this->site_title,$str);
		$str=str_replace("{#copyright_text}",$this->copyright,$str);
		$this->email->from($this->senderemail,$this->from);
		$this->email->to($memberRec->email);
		$this->email->mailtype=$this->mailtype;
		$this->email->subject($template->email_subject);
		$this->email->message($str);
		return $this->email->send();
	}

	function sendNewsletterforUser($email)
	{
		$this->db->where('nwsltr_id',$this->input->post('newsletterId'));
		$row = $this->db->get('tbl_newsletter')->row();

		$str=$row->description;
		$this->email->from($this->senderemail, $this->from);
		$this->email->to($email);
		$this->email->mailtype=$this->mailtype;
		$this->email->subject($row->title);
		$this->email->message($str);
		$this->email->send();
		$this->email->clear();
   }


}