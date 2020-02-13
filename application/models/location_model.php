<?php
class Location_Model extends CI_Model
{
	var $tablename		='country';
	var $tablenameregion='region';
	var $tablenamecity	='city';
	var $tablenamearea	='area';

	function GetRecordsByIdAndType($id,$type)
	{
		if($type=='Country')
		{
			$this->db->where("id",$id);
			$query=$this->db->get($this->tablename);
		}
		if($type=='Region')
		{
			$this->db->where("region_id",$id);
			$query=$this->db->get($this->tablenameregion);
		}
		if($type=='City')
		{
			$this->db->where('cty_id',$id);
			$query=$this->db->get($this->tablenamecity);
	 	}
	 	if($type=='Area')
		{
			$this->db->where('id',$id);
			$query=$this->db->get($this->tablenamearea);
	 	}
		$row	=	$query->row();
		return $row;
	}

	function performMultipleOperations($ids,$task,$type)
	{
		if($task=='delete')
			{
				for($i=0;isset($ids[$i]);$i++)
					{
						if($type == 'Country')
						{
							$this->db->where('id',$ids[$i]);
							$this->db->delete($this->tablename);
						}
						if($type == 'Region')
						{
							$this->db->where('region_id',$ids[$i]);
							$this->db->delete($this->tablenameregion);
						}
						if($type == 'City')
						{
							$this->db->where('cty_id',$ids[$i]);
							$this->db->delete($this->tablenamecity);
						}
						if($type == 'Area')
						{
							$this->db->where('id',$ids[$i]);
							$this->db->delete($this->tablenamearea);
						}
					}
				$message	=	"Selected records has been deleted successfully.";
			}
		if($task=='Active' || $task=='Inactive' )
			{
				for($i=0;isset($ids[$i]);$i++)
					{
						if($type == 'Country')
						{
							$this->db->set('status', $task);
							$this->db->where('id',$ids[$i]);
							$this->db->update($this->tablename);
						}
						if($type == 'Region')
						{
							$this->db->set('status', $task);
							$this->db->where('region_id',$ids[$i]);
							$this->db->update($this->tablenameregion);
						}
						if($type == 'City')
						{
							$this->db->set('status', $task);
							$this->db->where('cty_id',$ids[$i]);
							$this->db->update($this->tablenamecity);
						}
						if($type == 'Area')
						{
							$this->db->set('status', $task);
							$this->db->where('id',$ids[$i]);
							$this->db->update($this->tablenamearea);
						}
					}
				$message	=	"Selected records has been ".$task." successfully.";
			}
		return $message;
	}

	function perform_task($task,$type,$id)
	{
		if($task=='Delete')
			{
				if($type == 'Country')
				{
					$this->db->where('id',$id);
					$this->db->delete($this->tablename);
				}
				if($type == 'Region')
				{
					$this->db->where('region_id',$id);
					$this->db->delete($this->tablenameregion);
				}
				if($type == 'City')
				{
					$this->db->where('cty_id',$id);
					$this->db->delete($this->tablenamecity);
				}
				if($type == 'Area')
				{
					$this->db->where('id',$id);
					$this->db->delete($this->tablenamearea);
				}
			}
		else
			{
				if($type == 'Country')
				{
					$this->db->set('status', $task);
					$this->db->where('id',$id);
					$this->db->update($this->tablename);
				}
				if($type == 'Region')
				{
					$this->db->set('status', $task);
					$this->db->where('region_id',$id);
					$this->db->update($this->tablenameregion);
				}
				if($type == 'City')
				{
					$this->db->set('status', $task);
					$this->db->where('cty_id',$id);
					$this->db->update($this->tablenamecity);
				}
				if($type == 'Area')
				{
					$this->db->set('status', $task);
					$this->db->where('id',$id);
					$this->db->update($this->tablenamearea);
				}
			}
	}

	function update_locationByIdAndType($type,$id)
	{
		if($type == 'Country')
		{
			$this->db->set('country_name',$this->input->post('country_name'));
			$this->db->set('code',$this->input->post('country_code'));
			$this->db->where('id',$id);
			$this->db->update($this->tablename);
		}

		if($type == 'Region')
		{
			$region = $this->input->post('region_name');
			$this->db->set('region_name', $region);
			$con_id = $this->input->post('country_id');
			$this->db->set('country_id', $con_id);
			$this->db->where('region_id',$id);
			$this->db->update($this->tablenameregion);
		}

		if($type == 'City')
		{
			$slug_old=$this->common_model->getSingleFieldFromAnyTable('slug','cty_id',$id,'city');
			$slug = $this->common_model->create_unique_slug_for_common($this->input->post('city_name'),'city');

			if($slug != $slug_old)
				$this->db->set('slug',$slug);
			$this->db->set('name', $this->input->post('city_name'));
			$this->db->set('con_id', $this->input->post('country_id'));
			$this->db->set('sta_id', $this->input->post('region'));
			$this->db->where('cty_id',$id);
			$this->db->update($this->tablenamecity);
		}
	}

#==============================COuntry Controller Methods================================#



	function getAdminAllCountryCount($task=NULL)
	{
		if($task)
			$this->db->where('status',$task);

	 	$query=$this->db->get($this->tablename);
		$row= $query->num_rows();
	 	return $row;
	}

	function getAdminAllCountry($num,$offset)
	{
		$this->db->select();
		$this->db->order_by("id","desc");
	 	$query=$this->db->get($this->tablename,$num,$offset);
		$record	=	$query->result();
		return $record;
	}

	function addCountry()
	{
	    $name = $this->input->post('country_name');
	    $this->db->set('country_name', $name);
		$code = $this->input->post('country_code');
		$this->db->set('code', $code);
		$this->db->set('status','Active');
		$this->db->insert($this->tablename);
		return $this->db->insert_id();

	}

	function getAllCountry()
	{
	    $this->db->order_by('country_name','asc');
		$query=$this->db->get($this->tablename);
		$result= $query->result();
		return $result;
	}

	function GetRecordsByCountryId($id)
	{
		$this->db->where('id',$id);
		$query=$this->db->get($this->tablename);
		$row	=	$query->row();
		return $row;
	}



	#================== To get All Filter Count 'START'=========================

	function getAllCountryFilterCount($filter=NULL,$show_me=NULL,$sort_by=NULL)
	{
		if($filter!='NULL')
		{
			$where="(country_name like '%".$filter."%' or code like '%".$filter."%' )";
			$this->db->where($where);
		}
		if($show_me!='NULL')
		{
			if($show_me=='Active' || $show_me=='Inactive')
			$this->db->where('status',$show_me);
		}
		if($sort_by!='NULL')
		{
			if($sort_by=='New')
			$this->db->order_by("id","desc");
			if($sort_by=='Old')
			$this->db->order_by("id","asc");
			if($sort_by=='Asc')
			$this->db->order_by("id","asc");
			if($sort_by=='Desc')
			$this->db->order_by("id","desc");

	 	}
	 	$query=$this->db->get($this->tablename);
		$row= $query->num_rows();
	 	return $row;
	}

	function getAllCountryFilter($filter=NULL,$show_me=NULL,$sort_by=NULL,$num,$offset)
	{
		if($filter!='NULL')
		{
			$where="(country_name like '%".$filter."%' or code like '%".$filter."%' )";
			$this->db->where($where);
		}

		if($show_me!='NULL')
		{
			if($show_me=='Active' || $show_me=='Inactive')
			$this->db->where('status',$show_me);
		}
		if($sort_by!='NULL')
		{
			if($sort_by=='New')
			$this->db->order_by("id","desc");
			if($sort_by=='Old')
			$this->db->order_by("id","asc");
			if($sort_by=='Asc')
			$this->db->order_by("id","asc");
			if($sort_by=='Desc')
			$this->db->order_by("id","desc");

	 	}
	 	$query=$this->db->get($this->tablename,$num,$offset);
		$record	=	$query->result();
		return $record;
	}

#========Country Controller MEthods Ends here===============================================#


#======================+Region controller methods==================================================#

	function getAdminAllRegionCount($task=NULL)
	{
		if($task)
			$this->db->where('status',$task);

	 	$query=$this->db->get($this->tablenameregion);
		$row= $query->num_rows();
	 	return $row;
	}

	function getAdminAllRegion($num,$offset)
	{
		$this->db->select();
		$this->db->order_by("region_id","desc");
	 	$query=$this->db->get($this->tablenameregion,$num,$offset);
		$record	=	$query->result();
		return $record;
	}

	function addRegion()
	{
	    $name = $this->input->post('region_name');
	    $this->db->set('region_name', $name);
		$code = $this->input->post('country_id');
		$this->db->set('country_id', $code);
		$this->db->set('status','Active');
		$this->db->insert($this->tablenameregion);
		return $this->db->insert_id();

	}

	function GetRecordsByRegionId($id)
	{
		$this->db->where("region_id",$id);
		$query=$this->db->get($this->tablenameregion);
		$row	=	$query->row();
		return $row;
	}

	#================== To get All faq Filter Count 'START'=========================

	function getAllRegionFilterCount($filter=NULL,$show_me=NULL,$sort_by=NULL)
	{
		if($filter!='NULL')
		{
			$where="(region_name like '%".$filter."%' )";
			$this->db->where($where);
		}
		if($show_me!='NULL')
		{
			if($show_me=='Active' || $show_me=='Inactive')
			$this->db->where('status',$show_me);
		}
		if($sort_by!='NULL')
		{
			if($sort_by=='New')
			$this->db->order_by("region_id","desc");
			if($sort_by=='Old')
			$this->db->order_by("region_id","asc");
			if($sort_by=='Asc')
			$this->db->order_by("region_id","asc");
			if($sort_by=='Desc')
			$this->db->order_by("region_id","desc");

	 	}
	 	$query=$this->db->get($this->tablenameregion);
		$row= $query->num_rows();
	 	return $row;
	}

	function getAllRegionFilter($filter=NULL,$show_me=NULL,$sort_by=NULL,$num,$offset)
	{
		if($filter!='NULL')
		{
			$where="(region_name like '%".$filter."%' )";
			$this->db->where($where);
		}

		if($show_me!='NULL')
		{
			if($show_me=='Active' || $show_me=='Inactive')
			$this->db->where('status',$show_me);
		}
		if($sort_by!='NULL')
		{
			if($sort_by=='New')
			$this->db->order_by("region_id","desc");
			if($sort_by=='Old')
			$this->db->order_by("region_id","asc");
			if($sort_by=='Asc')
			$this->db->order_by("region_id","asc");
			if($sort_by=='Desc')
			$this->db->order_by("region_id","desc");

	 	}
	 	$query=$this->db->get($this->tablenameregion,$num,$offset);
		$record	=	$query->result();
		return $record;
	}

	function getAllRegionbyCountry($id)
	{
		$this->db->where('country_id',$id);
		$this->db->where('status','Active');
		$this->db->order_by('region_name','ASC');
	 	$query=$this->db->get($this->tablenameregion);
		$record	=	$query->result();
		return $record;
	}
#===========================+Region controller Method ends here=======================+#

#=======================================City Controller Methods========================#

	function getAdminAllCityCount($task=NULL)
	{
		if($task)
			$this->db->where('status',$task);

	 	$query=$this->db->get($this->tablenamecity);
		$row= $query->num_rows();
	 	return $row;
	}

	function getAdminAllCity($num,$offset)
	{
		$this->db->select();
		$this->db->order_by('cty_id',"desc");
	 	$query=$this->db->get($this->tablenamecity,$num,$offset);
		$record	=	$query->result();
		return $record;
	}

	function addCity()
	{
		$slug = $this->common_model->create_unique_slug_for_common($this->input->post('city_name'),'city');
		$this->db->set('slug',$slug);
	    $this->db->set('name', $this->input->post('city_name'));
		$this->db->set('con_id', $this->input->post('country_id'));
		$this->db->set('sta_id', $this->input->post('region'));
		$this->db->set('status','Active');
		$this->db->insert($this->tablenamecity);
		return $this->db->insert_id();
	}

	function getAllCityFilterCount($filter=NULL,$show_me=NULL,$sort_by=NULL)
	{
		if($filter!='NULL')
		{
			$where="(name like '%".$filter."%' )";
			$this->db->where($where);
		}
		if($show_me!='NULL')
		{
			if($show_me=='Active' || $show_me=='Inactive')
			$this->db->where('status',$show_me);
		}
		if($sort_by!='NULL')
		{
			if($sort_by=='New')
			$this->db->order_by("cty_id","desc");
			if($sort_by=='Old')
			$this->db->order_by("cty_id","asc");
			if($sort_by=='Asc')
			$this->db->order_by("cty_id","asc");
			if($sort_by=='Desc')
			$this->db->order_by("cty_id","desc");

	 	}
	 	$query=$this->db->get($this->tablenamecity);
		$row= $query->num_rows();
	 	return $row;
	}

	function getAllCityFilter($filter=NULL,$show_me=NULL,$sort_by=NULL,$num,$offset)
	{
		if($filter!='NULL')
		{
			$where="(name like '%".$filter."%' )";
			$this->db->where($where);
		}

		if($show_me!='NULL')
		{
			if($show_me=='Active' || $show_me=='Inactive')
			$this->db->where('status',$show_me);
		}
		if($sort_by!='NULL')
		{
			if($sort_by=='New')
			$this->db->order_by("cty_id","desc");
			if($sort_by=='Old')
			$this->db->order_by("cty_id","asc");
			if($sort_by=='Asc')
			$this->db->order_by("cty_id","asc");
			if($sort_by=='Desc')
			$this->db->order_by("cty_id","desc");

	 	}
	 	$query=$this->db->get($this->tablenamecity,$num,$offset);
		$record	=	$query->result();
		return $record;
	}

	function getAllCityByStateId($id)
	{
		$this->db->where('sta_id',$id);
		$this->db->where('status','Active');
		$this->db->order_by('name','ASC');
	 	$query=$this->db->get($this->tablenamecity);
		$record	=	$query->result();
		return $record;
	}

	function GetRecordsByCityId($id)
	{
		$this->db->where('cty_id',$id);
		$query=$this->db->get($this->tablenamecity);
		$row	=	$query->row();
		return $row;
	}
	
	
	#=======================================City Controller Methods========================#
	
	function getAdminAllAreaCount($task=NULL)
	{
		if($task)
			$this->db->where('status',$task);
		$this->db->set('member_id', $this->session->userdata('ADM_ID'));
		$query=$this->db->get($this->tablenamearea);
		$row= $query->num_rows();
		return $row;
	}
	
	function getAdminAllArea($num,$offset)
	{
		$this->db->select('tbl_area.*');
		$this->db->select('tbl_city.name as city');
		$this->db->select('tbl_region.region_name as region');
		$this->db->join('tbl_city','tbl_city.cty_id = tbl_area.city_id','left');
		$this->db->join('tbl_region','tbl_region.region_id = tbl_area.state_id','left');
		$this->db->set('member_id', $this->session->userdata('ADM_ID'));
		$this->db->order_by('id',"desc");
		$query=$this->db->get($this->tablenamearea,$num,$offset);
		$record	=	$query->result();
		return $record;
	}
	
	function addArea()
	{
		$slug = $this->common_model->create_unique_slug_for_common($this->input->post('name'),'area');
		$this->db->set('slug',$slug);
		$this->db->set('name', $this->input->post('name'));
		$this->db->set('country_id', 88);
		$this->db->set('member_id', $this->session->userdata('ADM_ID'));
		$this->db->set('state_id', $this->input->post('state_id'));
		$this->db->set('city_id', $this->input->post('city_id'));
		$this->db->set('status','Active');
		$this->db->insert($this->tablenamearea);
		return $this->db->insert_id();
	}
	
	function updateArea($id)
	{
		$this->db->set('name', $this->input->post('name'));
		$this->db->set('state_id', $this->input->post('state_id'));
		$this->db->set('city_id', $this->input->post('city_id'));
		$this->db->set('status','Active');
		$this->db->where('id',$id);
		$this->db->update($this->tablenamearea);
		return $this->db->insert_id();
	}
	
	function getAllAreaFilterCount($filter=NULL,$show_me=NULL,$sort_by=NULL)
	{
		if($filter!='NULL')
		{
			$where="(name like '%".$filter."%' )";
			$this->db->where($where);
		}
		if($show_me!='NULL')
		{
			if($show_me=='Active' || $show_me=='Inactive')
				$this->db->where('status',$show_me);
		}
		if($sort_by!='NULL')
		{
			if($sort_by=='New')
				$this->db->order_by("cty_id","desc");
			if($sort_by=='Old')
				$this->db->order_by("cty_id","asc");
			if($sort_by=='Asc')
				$this->db->order_by("cty_id","asc");
			if($sort_by=='Desc')
				$this->db->order_by("cty_id","desc");
			
		}
		$this->db->set('member_id', $this->session->userdata('ADM_ID'));
		$query=$this->db->get($this->tablenamearea);
		$row= $query->num_rows();
		return $row;
	}
	
	function getAllAreaFilter($filter=NULL,$show_me=NULL,$sort_by=NULL,$num,$offset)
	{
		$this->db->select('tbl_area.*');
		$this->db->select('tbl_city.name as city');
		$this->db->select('tbl_region.region_name as region');
		
		if($filter!='NULL')
		{
			$where="(name like '%".$filter."%' )";
			$this->db->where($where);
		}
		
		if($show_me!='NULL')
		{
			if($show_me=='Active' || $show_me=='Inactive')
				$this->db->where('tbl_area.status',$show_me);
		}
		if($sort_by!='NULL')
		{
			if($sort_by=='New')
				$this->db->order_by("cty_id","desc");
			if($sort_by=='Old')
				$this->db->order_by("cty_id","asc");
			if($sort_by=='Asc')
				$this->db->order_by("cty_id","asc");
			if($sort_by=='Desc')
				$this->db->order_by("cty_id","desc");
			
		}
		
		$this->db->join('tbl_city','tbl_city.cty_id = tbl_area.city_id','left');
		$this->db->join('tbl_region','tbl_region.region_id = tbl_area.state_id','left');
		
		$this->db->set('member_id', $this->session->userdata('ADM_ID'));
		$query=$this->db->get($this->tablenamearea,$num,$offset);
		$record	=	$query->result();
		return $record;
	}
	
	function getAreasBySearchKeyword($keyword,$select=NULL){
		if($select)
			$this->db->select($select);
		$this->db->like('name',$keyword);
		$this->db->where('status','Active');
		$this->db->order_by('name','ASC');
		$this->db->set('member_id', $this->session->userdata('ADM_ID'));
		$query=$this->db->get($this->tablenamearea);
		$record	=	$query->result();
		return $record;
	}
	function getAllAreaByCityId($id,$member_id)
	{
		$this->db->set('member_id', $this->session->userdata('ADM_ID'));
		$this->db->where('city_id',$id);
		$this->db->where('status','Active');
		$this->db->order_by('name','ASC');
		$query=$this->db->get($this->tablenamearea);
		$record	=	$query->result();
		return $record;
	}
	
	function getAllAreaList($member_id)
	{
		$this->db->where('member_id',$member_id);
		$this->db->where('status','Active');
		$this->db->order_by('name','ASC');
		$query=$this->db->get($this->tablenamearea);
		$record	=	$query->result();
		return $record;
	}
	
	function GetRecordsByAreaId($id)
	{
		$this->db->set('member_id', $this->session->userdata('ADM_ID'));
		$this->db->where('id',$id);
		$query=$this->db->get($this->tablenamearea);
		$row	=	$query->row();
		return $row;
	}
	
	
}