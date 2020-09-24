<?php
defined('BASEPATH') OR exit('No direct scriot access allowed');

class api extends CI_Controller {

    public function __construct() {
        parent::__construct();
    } 

	public function get_all_groups()
	{
        $this->load->model('ScheduleModel');
        
		$response = $this->ScheduleModel->get_groups_list();
		echo $response;
    }
    
    public function reg_user()
    {   
        $this->load->model('UsersModel');

        $user_id = $this->input->get('user_id', TRUE);
        $user_group = $this->input->get('user_group', TRUE);

        $response = $this->UsersModel->registration($user_id, $user_group);
        echo $response;
    }

    public function get_week_schedule()
    {
        $this->load->model('ScheduleModel');

        $user_id = $this->input->get('user_id', TRUE);

        $response = $this->ScheduleModel->week_schedule($user_id);
		echo json_encode($response);
    }    

    public function get_day_schedule(){
        $this->load->model('ScheduleModel');

        $day_of_week = $this->input->get('day', TRUE);
        $user_id = $this->input->get('user_id', TRUE);

        $response = $this->ScheduleModel->day_schedule($day_of_week, $user_id);
		echo json_encode($response);
    }

    public function index()
	{
		$this->load->view('welcome_message');
	}
}
?>