<?php
class M_access extends CI_Model {
    public function __construct() {
        parent::__construct();
    }
    var $table = 'user';
    var $table1 = 'jadwal';
    var $column_order = array();
    var $column_search = array();
    var $order = array('STO' => 'asc'); // default order

    function validate($value1,$value2) {
        $this->db->where('USERNAME',$value1);
        $this->db->where('PASSWORD',$value2);
        $query = $this->db->get('user');
        if($query->num_rows() == 1) {
            return TRUE;
        }
        else {
            return FALSE;
        }
    }
    public function count_all(){
        $this->db->from($this->table);
        return $this->db->count_all_results();
      }
    function get_datatables(){
      $this->_get_datatables_query();
      if($_POST['length'] != -1)
      $this->db->limit($_POST['length'], $_POST['start']);
      $query = $this->db->get();
      return $query->result();
    }
    function count_filtered(){
        $this->_get_datatables_query();
        $query = $this->db->get();
        return $query->num_rows();
    }
    private function _get_datatables_query(){
      if ($this->input->post('type')=='TEKNISI') {
        $this->column_order = array(null,'STO','CLUSTER','USERNAME','NAME'); //set column field database for datatable orderable
        $this->column_search = array('STO','CLUSTER','USERNAME','NAME'); //set column field database for datatable searchable
      }
      else {
        $this->column_order = array(null, 'USERNAME','NAME','ROLE','STO','CLUSTER','CLUSTER_HELP','WORK_FINISHED'); //set column field database for datatable orderable
        $this->column_search = array('USERNAME','NAME','ROLE','STO','CLUSTER','CLUSTER_HELP','WORK_FINISHED'); //set column field database for datatable searchable
      }
      if ($this->input->post('type')!='ALL') {
        $this->db->where('TGL_KERJA', date('d/m/Y'));
        $this->db->from($this->table1);
      }
      else {
        $this->db->from($this->table);
      }

      $i = 0;

      foreach ($this->column_search as $item) // loop column
      {
          if($_POST['search']['value']) // if datatable send POST for search
          {

              if($i===0) // first loop
              {
                  $this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
                  $this->db->like($item, $_POST['search']['value']);
              }
              else
              {
                  $this->db->or_like($item, $_POST['search']['value']);
              }

              if(count($this->column_search) - 1 == $i) //last loop
                  $this->db->group_end(); //close bracket
          }
          $i++;
      }

      if(isset($_POST['order'])) // here order processing
      {
          $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
      }
      else if(isset($this->order))
      {
          $order = $this->order;
          $this->db->order_by(key($order), $order[key($order)]);
      }
    }
}
