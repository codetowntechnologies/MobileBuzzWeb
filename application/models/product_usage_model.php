<?php

class Product_usage_model extends CI_Model
{
    var $tablename = 'product_usage';

    function GetRecordsById($id)
    {
        $this->db->where("id", $id);
        $this->db->where('member_id', $this->parent_user_id);
        $query = $this->db->get($this->tablename);
        $row = $query->row();
        return $row;
    }

    function getAllRecords($product_id, $num, $offset)
    {
        $this->db->where('product_id', $product_id);
        $query = $this->db->get($this->tablename, $num, $offset);
        $record = $query->result();
        return $record;
    }

    function getAllPendingGroupItems($user_id, $client_id, $from_date, $date)
    {
        $this->db->select('product_id,product.product_name,product.product_tax_input,product.product_tax_output,product.mrp_price,product.selling_price,product.discount,COUNT(tbl_product_usage.id) as total');
        $this->db->where('user_id', $user_id);
        $this->db->where('client_id', $client_id);
        $this->db->where('DATE(checkin_date) >=', $from_date);
        $this->db->where('DATE(checkin_date) <=', $date);
        $this->db->where('bill_status', 'Pending');
        $this->db->join('product', 'product.id = product_usage.product_id');
        $this->db->group_by('product_id');
        $query = $this->db->get($this->tablename);
        $record = $query->result();
        return $record;
    }

    function getAllPendingItems($user_id, $client_id, $from_date, $date)
    {
        //$this->db->select('product_id,product.product_name');
        $this->db->where('user_id', $user_id);
        $this->db->where('client_id', $client_id);
        $this->db->where('DATE(checkin_date) >=', $from_date);
        $this->db->where('DATE(checkin_date) <=', $date);
        $this->db->where('bill_status', 'Pending');
        //$this->db->join('product','product.id = product_usage.product_id');
        $query = $this->db->get($this->tablename);
        $record = $query->result();
        return $record;
    }

    function getAllRecordsUsageReport()
    {
        $this->db->select("product_usage.*,member.area,COUNT('product_usage.id') as total_sent");
        $this->db->select("area.name as area_name");

        if ($this->input->get('client_name'))
            $this->db->like('member.name', $this->input->get('client_name'));
        if ($this->input->get('area'))
            $this->db->like('member.area', $this->input->get('area'));

        if ($this->input->get('from_date'))
            $this->db->where('DATE(checkin_date) >=', date('Y-m-d', strtotime($this->input->get('from_date'))));

        if ($this->input->get('to_date'))
            $this->db->where('DATE(checkin_date) <=', date('Y-m-d', strtotime($this->input->get('to_date'))));

        if ($this->input->get('sort_by')) {
            $order_by = $this->input->get('order_by');
            if ($this->input->get('order_by') == '')
                $order_by = 'asc';

            $this->db->order_by('product_usage.' . $this->input->get('sort_by'), $order_by);
        } else {
            $this->db->order_by("product_usage.id", "desc");
        }

        $this->db->where('user_id', $this->parent_user_id);

        $this->db->join('member', 'member.id = product_usage.client_id');
        $this->db->join('area', 'area.id = member.area');
        //$this->db->group_by('product_usage.client_id');
        $this->db->group_by('member.area');

        $query = $this->db->get($this->tablename);
        return $result = $query->result();
        //echo "<pre>"; print_r($result); die;
    }

    function getAllRecordsUsageReportStatus($area_id, $status)
    {
        $this->db->select("COUNT('product_usage.id') as total");

        if ($this->input->get('client_name'))
            $this->db->like('member.name', $this->input->get('client_name'));
        if ($this->input->get('area'))
            $this->db->like('member.area', $this->input->get('area'));

        if ($this->input->get('from_date'))
            $this->db->where('DATE(checkin_date) >=', date('Y-m-d', strtotime($this->input->get('from_date'))));

        if ($this->input->get('to_date'))
            $this->db->where('DATE(checkin_date) <=', date('Y-m-d', strtotime($this->input->get('to_date'))));

        if ($this->input->get('sort_by')) {
            $order_by = $this->input->get('order_by');
            if ($this->input->get('order_by') == '')
                $order_by = 'asc';

            $this->db->order_by('product_usage.' . $this->input->get('sort_by'), $order_by);
        } else {
            $this->db->order_by("product_usage.id", "desc");
        }

        $this->db->where('user_id', $this->parent_user_id);
        $this->db->where('area', $area_id);
        $this->db->where('product_usage.status', $status);

        $this->db->join('member', 'member.id = product_usage.client_id');
        $this->db->join('area', 'area.id = member.area');
        //$this->db->group_by('product_usage.client_id');
        $this->db->group_by('member.area');

        $query = $this->db->get($this->tablename);
        return $result = $query->row()->total;
    }

    function getAllRecordsUsageReportDetail($area_id, $client_id)
    {
        $this->db->select("product_usage.*,member.name,member.area,member.email,member.phone_number,COUNT('product_usage.id') as total_sent");
        $this->db->select("area.name as area_name");

        if ($this->input->get('client_name'))
            $this->db->like('member.name', $this->input->get('client_name'));
        if ($this->input->get('area'))
            $this->db->like('member.area', $this->input->get('area'));

        if ($this->input->get('from_date'))
            $this->db->where('DATE(checkin_date) >=', date('Y-m-d', strtotime($this->input->get('from_date'))));

        if ($this->input->get('to_date'))
            $this->db->where('DATE(checkin_date) <=', date('Y-m-d', strtotime($this->input->get('to_date'))));

        if ($this->input->get('sort_by')) {
            $order_by = $this->input->get('order_by');
            if ($this->input->get('order_by') == '')
                $order_by = 'asc';

            $this->db->order_by('product_usage.' . $this->input->get('sort_by'), $order_by);
        } else {
            $this->db->order_by("product_usage.id", "desc");
        }

        $this->db->where('user_id', $this->parent_user_id);
        $this->db->where('area', $area_id);
        if($client_id)
        $this->db->where('client_id', $client_id);

        $this->db->join('member', 'member.id = product_usage.client_id');
        $this->db->join('area', 'area.id = member.area');
        //$this->db->group_by('product_usage.client_id');
        $this->db->group_by('member.area');

        $query = $this->db->get($this->tablename);
        return $result = $query->row();
        //echo "<pre>"; print_r($result); die;
    }

    function getAllRecordsUsageReportDetailStatus($area_id, $status, $client_id)
    {
        $this->db->select("COUNT('product_usage.id') as total");

        if ($this->input->get('client_name'))
            $this->db->like('member.name', $this->input->get('client_name'));
        if ($this->input->get('area'))
            $this->db->like('member.area', $this->input->get('area'));

        if ($this->input->get('from_date'))
            $this->db->where('DATE(checkin_date) >=', date('Y-m-d', strtotime($this->input->get('from_date'))));

        if ($this->input->get('to_date'))
            $this->db->where('DATE(checkin_date) <=', date('Y-m-d', strtotime($this->input->get('to_date'))));

        if ($this->input->get('sort_by')) {
            $order_by = $this->input->get('order_by');
            if ($this->input->get('order_by') == '')
                $order_by = 'asc';

            $this->db->order_by('product_usage.' . $this->input->get('sort_by'), $order_by);
        } else {
            $this->db->order_by("product_usage.id", "desc");
        }

        $this->db->where('user_id', $this->parent_user_id);
        $this->db->where('area', $area_id);
        if($client_id)
        $this->db->where('client_id', $client_id);
        $this->db->where('product_usage.status', $status);

        $this->db->join('member', 'member.id = product_usage.client_id');
        $this->db->join('area', 'area.id = member.area');
        $this->db->group_by('member.area');

        return $this->db->get($this->tablename)->row()->total;
    }

    function getAllRecordsUsageAreaReport($area_id)
    {
        $this->db->select("product_usage.*,member.name,member.area,member.phone_number,COUNT('product_usage.id') as total_sent");
        $this->db->select("area.name as area_name");

        if ($this->input->get('client_name'))
            $this->db->like('member.name', $this->input->get('client_name'));
        if ($this->input->get('area'))
            $this->db->like('member.area', $this->input->get('area'));

        if ($this->input->get('from_date'))
            $this->db->where('DATE(checkin_date) >=', date('Y-m-d', strtotime($this->input->get('from_date'))));

        if ($this->input->get('to_date'))
            $this->db->where('DATE(checkin_date) <=', date('Y-m-d', strtotime($this->input->get('to_date'))));

        if ($this->input->get('sort_by')) {
            $order_by = $this->input->get('order_by');
            if ($this->input->get('order_by') == '')
                $order_by = 'asc';

            $this->db->order_by('product_usage.' . $this->input->get('sort_by'), $order_by);
        } else {
            $this->db->order_by("product_usage.id", "desc");
        }

        $this->db->where('user_id', $this->parent_user_id);
        $this->db->where('area', $area_id);

        $this->db->join('member', 'member.id = product_usage.client_id');
        $this->db->join('area', 'area.id = member.area');
        $this->db->group_by('product_usage.client_id');
        //$this->db->group_by('member.area');

        $query = $this->db->get($this->tablename);
        return $query->result();
    }

    function getAllRecordsUsageAreaReportStatus($area_id, $client_id, $status)
    {
        $this->db->select("COUNT('product_usage.id') as total");

        if ($this->input->get('client_name'))
            $this->db->like('member.name', $this->input->get('client_name'));
        if ($this->input->get('area'))
            $this->db->like('member.area', $this->input->get('area'));

        if ($this->input->get('from_date'))
            $this->db->where('DATE(checkin_date) >=', date('Y-m-d', strtotime($this->input->get('from_date'))));

        if ($this->input->get('to_date'))
            $this->db->where('DATE(checkin_date) <=', date('Y-m-d', strtotime($this->input->get('to_date'))));

        if ($this->input->get('sort_by')) {
            $order_by = $this->input->get('order_by');
            if ($this->input->get('order_by') == '')
                $order_by = 'asc';

            $this->db->order_by('product_usage.' . $this->input->get('sort_by'), $order_by);
        } else {
            $this->db->order_by("product_usage.id", "desc");
        }

        $this->db->where('user_id', $this->parent_user_id);
        $this->db->where('area', $area_id);
        $this->db->where('client_id', $client_id);
        $this->db->where('product_usage.status', $status);

        $this->db->join('member', 'member.id = product_usage.client_id');
        $this->db->join('area', 'area.id = member.area');
        $this->db->group_by('product_usage.client_id');
        //$this->db->group_by('member.area');

        $query = $this->db->get($this->tablename);
        return $result = $query->row()->total;
    }

    function getAllRecordsUsageProductReport($area_id, $client_id)
    {
        $this->db->select("product_usage.*,member.name,member.area,member.phone_number,COUNT('product_usage.id') as total_sent");
        $this->db->select("area.name as area_name");

        if ($this->input->get('client_name'))
            $this->db->like('member.name', $this->input->get('client_name'));
        if ($this->input->get('area'))
            $this->db->like('member.area', $this->input->get('area'));

        if ($this->input->get('from_date'))
            $this->db->where('DATE(checkin_date) >=', date('Y-m-d', strtotime($this->input->get('from_date'))));

        if ($this->input->get('to_date'))
            $this->db->where('DATE(checkin_date) <=', date('Y-m-d', strtotime($this->input->get('to_date'))));

        if ($this->input->get('sort_by')) {
            $order_by = $this->input->get('order_by');
            if ($this->input->get('order_by') == '')
                $order_by = 'asc';

            $this->db->order_by('product_usage.' . $this->input->get('sort_by'), $order_by);
        } else {
            $this->db->order_by("product_usage.id", "desc");
        }

        $this->db->where('user_id', $this->parent_user_id);
        $this->db->where('area', $area_id);
        $this->db->where('client_id', $client_id);

        $this->db->join('member', 'member.id = product_usage.client_id');
        $this->db->join('area', 'area.id = member.area');
        //$this->db->group_by('product_usage.client_id');
        $this->db->group_by('DATE(checkin_date)');

        $query = $this->db->get($this->tablename);
        return $query->result();
    }

    function getAllRecordsUsageProductReportStatus($area_id, $client_id, $date, $status)
    {
        $this->db->select("COUNT('product_usage.id') as total");

        if ($this->input->get('client_name'))
            $this->db->like('member.name', $this->input->get('client_name'));
        if ($this->input->get('area'))
            $this->db->like('member.area', $this->input->get('area'));

        if ($this->input->get('from_date'))
            $this->db->where('DATE(checkin_date) >=', date('Y-m-d', strtotime($this->input->get('from_date'))));

        if ($this->input->get('to_date'))
            $this->db->where('DATE(checkin_date) <=', date('Y-m-d', strtotime($this->input->get('to_date'))));

        if ($this->input->get('sort_by')) {
            $order_by = $this->input->get('order_by');
            if ($this->input->get('order_by') == '')
                $order_by = 'asc';

            $this->db->order_by('product_usage.' . $this->input->get('sort_by'), $order_by);
        } else {
            $this->db->order_by("product_usage.id", "desc");
        }

        $this->db->where('user_id', $this->parent_user_id);
        $this->db->where('area', $area_id);
        $this->db->where('client_id', $client_id);
        $this->db->where('DATE(checkin_date)', $date);
        $this->db->where('product_usage.status', $status);

        $this->db->join('member', 'member.id = product_usage.client_id');
        $this->db->join('area', 'area.id = member.area');
        //$this->db->group_by('product_usage.client_id');
        $this->db->group_by('DATE(checkin_date)');

        return $this->db->get($this->tablename)->row()->total;
    }

    function getAllRecordsReportFilter()
    {
        $this->db->select("product_usage.*,member.name,COUNT('product_usage.id') as total_sent");
        //$this->db->select('(Select COUNT(product_usage.id) from tbl_product_usage as product_usage where product_usage.client_id = tbl_member.id and product_usage.status="Complete") as total_receive');
        //$this->db->select('(Select COUNT(product_usage.id) from tbl_product_usage as product_usage where product_usage.client_id = tbl_member.id and product_usage.status="Pending") as total_pending');

        if ($this->input->get('client_name'))
            $this->db->like('member.name', $this->input->get('client_name'));
        if ($this->input->get('area'))
            $this->db->like('member.area', $this->input->get('area'));

        if ($this->input->get('from_date'))
            $this->db->where('DATE(checkin_date) >=', date('Y-m-d', strtotime($this->input->get('from_date'))));

        if ($this->input->get('to_date'))
            $this->db->where('DATE(checkin_date) <=', date('Y-m-d', strtotime($this->input->get('to_date'))));

        if ($this->input->get('sort_by')) {
            $order_by = $this->input->get('order_by');
            if ($this->input->get('order_by') == '')
                $order_by = 'asc';

            $this->db->order_by('product_usage.' . $this->input->get('sort_by'), $order_by);
        } else {
            $this->db->order_by("product_usage.id", "desc");
        }

        $this->db->where('user_id', $this->parent_user_id);

        $this->db->join('member', 'member.id = product_usage.client_id');
        $this->db->group_by('product_usage.client_id');

        $query = $this->db->get($this->tablename);
        return $result = $query->result();
    }

    function getAllRecordsReportFilterDetail($client_id)
    {
        $this->db->select("product_usage.*,member.name,phone_number,email,address,COUNT('product_usage.id') as total_sent");

        if ($this->input->get('client_name'))
            $this->db->like('member.name', $this->input->get('client_name'));
        if ($this->input->get('area'))
            $this->db->like('member.area', $this->input->get('area'));

        if ($this->input->get('from_date'))
            $this->db->where('DATE(checkin_date) >=', date('Y-m-d', strtotime($this->input->get('from_date'))));

        if ($this->input->get('to_date'))
            $this->db->where('DATE(checkin_date) <=', date('Y-m-d', strtotime($this->input->get('to_date'))));

        if ($this->input->get('sort_by')) {
            $order_by = $this->input->get('order_by');
            if ($this->input->get('order_by') == '')
                $order_by = 'asc';

            $this->db->order_by('product_usage.' . $this->input->get('sort_by'), $order_by);
        } else {
            $this->db->order_by("product_usage.id", "desc");
        }

        $this->db->where('user_id', $this->parent_user_id);
        $this->db->where('client_id', $client_id);

        $this->db->join('member', 'member.id = product_usage.client_id');
        $this->db->group_by('product_usage.client_id');

        $query = $this->db->get($this->tablename);
        return $result = $query->row();
    }

    function getAllRecordsReportFilterStatus($client_id, $status)
    {
        $this->db->select("COUNT('product_usage.id') as total");

        if ($this->input->get('client_name'))
            $this->db->like('member.name', $this->input->get('client_name'));
        if ($this->input->get('area'))
            $this->db->like('member.area', $this->input->get('area'));

        if ($this->input->get('from_date'))
            $this->db->where('DATE(checkin_date) >=', date('Y-m-d', strtotime($this->input->get('from_date'))));

        if ($this->input->get('to_date'))
            $this->db->where('DATE(checkin_date) <=', date('Y-m-d', strtotime($this->input->get('to_date'))));

        $this->db->where('user_id', $this->parent_user_id);
        $this->db->where('client_id', $client_id);
        $this->db->where('product_usage.status', $status);

        $this->db->join('member', 'member.id = product_usage.client_id');
        $this->db->group_by('product_usage.client_id');
        $query = $this->db->get($this->tablename);
        return $result = $query->row()->total;
    }

    function getAllRecordsProductReportFilter($client_id)
    {
        $this->db->select("product_usage.*,member.name,COUNT('product_usage.id') as total_sent,product.product_code,product.product_name");
        //$this->db->select('(Select COUNT(product_usage.id) from tbl_product_usage as product_usage where product_usage.client_id = tbl_member.id and product_usage.product_id = tbl_product.id and product_usage.status="Complete") as total_receive');
        //$this->db->select('(Select COUNT(product_usage.id) from tbl_product_usage as product_usage where product_usage.client_id = tbl_member.id and product_usage.product_id = tbl_product.id and product_usage.status="Pending") as total_pending');

        if ($this->input->get('product_name'))
            $this->db->like('product.product_name', $this->input->get('product_name'));

        if ($this->input->get('from_date'))
            $this->db->where('DATE(checkin_date) >=', date('Y-m-d', strtotime($this->input->get('from_date'))));

        if ($this->input->get('to_date'))
            $this->db->where('DATE(checkin_date) <=', date('Y-m-d', strtotime($this->input->get('to_date'))));

        if ($this->input->get('sort_by')) {
            $order_by = $this->input->get('order_by');
            if ($this->input->get('order_by') == '')
                $order_by = 'asc';

            $this->db->order_by('product_usage.' . $this->input->get('sort_by'), $order_by);
        } else {
            $this->db->order_by("product_usage.id", "desc");
        }

        $this->db->where('user_id', $this->parent_user_id);
        $this->db->where('product_usage.client_id', $client_id);

        $this->db->join('member', 'member.id = product_usage.client_id');
        $this->db->join('product', 'product.id = product_usage.product_id');
        $this->db->group_by('product_usage.product_id');

        $query = $this->db->get($this->tablename);
        return $result = $query->result();
    }

    function getAllRecordsProductReportFilterStatus($client_id, $product_id, $status)
    {
        $this->db->select("COUNT('product_usage.id') as total");
        //$this->db->select('(Select COUNT(product_usage.id) from tbl_product_usage as product_usage where product_usage.client_id = tbl_member.id and product_usage.product_id = tbl_product.id and product_usage.status="Complete") as total_receive');
        //$this->db->select('(Select COUNT(product_usage.id) from tbl_product_usage as product_usage where product_usage.client_id = tbl_member.id and product_usage.product_id = tbl_product.id and product_usage.status="Pending") as total_pending');

        if ($this->input->get('product_name'))
            $this->db->like('product.product_name', $this->input->get('product_name'));

        if ($this->input->get('from_date'))
            $this->db->where('DATE(checkin_date) >=', date('Y-m-d', strtotime($this->input->get('from_date'))));

        if ($this->input->get('to_date'))
            $this->db->where('DATE(checkin_date) <=', date('Y-m-d', strtotime($this->input->get('to_date'))));

        $this->db->where('user_id', $this->parent_user_id);
        $this->db->where('product_usage.client_id', $client_id);
        $this->db->where('product_usage.product_id', $product_id);

        $this->db->join('member', 'member.id = product_usage.client_id');
        $this->db->join('product', 'product.id = product_usage.product_id');
        $this->db->group_by('product_usage.product_id');
        $this->db->where('product_usage.status', $status);

        $query = $this->db->get($this->tablename);
        return $result = $query->row()->total;
    }

    function getCountProductsReportFilterStatus($product_id, $status)
    {
        $this->db->select("COUNT('product_usage.id') as total");

        if ($this->input->get('product_name'))
            $this->db->like('product.product_name', $this->input->get('product_name'));

        $this->db->where('user_id', $this->parent_user_id);
        $this->db->where('product_usage.product_id', $product_id);

        $this->db->join('product', 'product.id = product_usage.product_id');
        $this->db->group_by('product_usage.product_id');
        $this->db->where('product_usage.status', $status);

        $query = $this->db->get($this->tablename);
        return $result = $query->row()->total;
    }

    function getAllRecordsProductHistoryReportFilter($client_id, $product_id)
    {
        $this->db->select("product_usage.*");
        if ($this->input->get('from_date'))
            $this->db->where('DATE(checkin_date) >=', date('Y-m-d', strtotime($this->input->get('from_date'))));

        if ($this->input->get('to_date'))
            $this->db->where('DATE(checkin_date) <=', date('Y-m-d', strtotime($this->input->get('to_date'))));

        $this->db->where('user_id', $this->parent_user_id);
        $this->db->where('product_usage.client_id', $client_id);
        $this->db->where('product_usage.product_id', $product_id);

        $query = $this->db->get($this->tablename);
        return $result = $query->result();
    }

}