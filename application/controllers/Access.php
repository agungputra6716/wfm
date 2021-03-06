<?php

if ( ! defined('BASEPATH')) exit ('No direct script access allowed');

class Access extends CI_Controller {
	public function __construct() {
        parent::__construct();
        $this->load->model('M_access');
        $this->load->model('M_user');
    }
	public function index() {
		$is_logged_in = $this->session->userdata('is_logged_in');
		if($is_logged_in) {
			redirect('maps');
		}
		else {
			$this->form_validation->set_rules('username','Username','required|trim');
			$this->form_validation->set_rules('password','Password','required|trim');
			if($this->form_validation->run() == false)
		    {
		        $this->load->view('login');
		    }
		    else
		    {
		    $username_temp = $this->input->post('username');
				$password_temp = $this->input->post('password');
				$password_md5 = md5($password_temp);
				$query	= $this->M_access->validate($username_temp,$password_md5);

				if($query) {
					$query1	= $this->M_user->get_user_data($username_temp);
					foreach ($query1 as $object) {
						if($object->USERNAME == $username_temp) {
							$nama		= $object->NAME;
							$role		= $object->ROLE;
							$contact= $object->CONTACT;
						}
					}
					$data = array(
						'username' 		=> $username_temp,
						'is_logged_in' 	=> TRUE,
						'role'			=> $role,
						'nama'			=> $nama,
						'contact' => $contact,
					);
					$this->session->set_userdata($data);
					redirect('maps');
				}
				else {
					$data['error']="Invalid User Id and Password combination";
		            $this->load->view('login', $data);
		            //return false;
				}
		    }
		}
	}

	public function LogOut() {
		$data = array(
			'username' => $this->session->userdata('username'),
			'is_logged_in' => FALSE
		);
		$this->session->set_userdata($data);
		redirect('Access/index');
	}/*
	public function get_row(){
		$this->db->select_sum('login_count');
		echo json_encode($this->db->get('user')->result());
	}*/
	public function ajax_user()
    {
      $list = $this->M_access->get_datatables();
      $data = array();
      $no = $_POST['start'];
			if ($this->input->post('type')!='ALL') {
				foreach ($list as $key) {
					$no++;
					$row = array();
					$row[] = $no;
					$row[] = $key->STO;
					$row[] = $key->CLUSTER;
					$row[] = $key->USERNAME;
					$row[] = $key->NAME;
					$data[] = $row;
				}
			}
			else {
				foreach ($list as $key) {
					$no++;
					$row = array();
					$row[] = $no;
					$row[] = $key->USERNAME;
					$row[] = $key->NAME;
					$row[] = $key->ROLE;
					$row[] = $key->STO;
					$row[] = $key->CLUSTER;
					$row[] = $row[] = '<a class="btn btn-sm btn-success" href="javascript:void(0)" title="Edit" onclick="edit_user('."'".$key->USERNAME."'".')">Edit</a>
                  <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Delete" onclick="delete_user('."'".$key->USERNAME."'".')">Delete</a>';;

					$data[] = $row;
				}
			}

      $output = array(
                      "draw" => $_POST['draw'],
                      "recordsTotal" => $this->M_access->count_all(),
                      "recordsFiltered" => $this->M_access->count_filtered(),
                      "data" => $data,
                    );
      //output to json format
      echo json_encode($output);
    }
}
