<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ScheduleModel extends CI_Model {
    public function get_groups_list()
    {
        $this->load->helper('string');

        $getdata = http_build_query(
            array(
                'query' => "КТ" 
             )
            );
            
        $opts = array('http' =>
                array(
                'method'  => 'GET',
                'content' => $getdata,
                'header'=>"Accept-language: ru\r\n" .
                "Content-Type: text/html; charset=UTF-8"
            )
            );
            
        $context  = stream_context_create($opts);
            
        $response = file_get_contents('http://ictis.sfedu.ru/schedule-api/?'.$getdata, false, $context);
        $groups = json_decode($response);

        foreach ($groups->choices as $choice) {
            $group_name = $choice->name;
            $group_link = $choice->group;
            $dataInsert = array('group_name' => $group_name, 'group_link' => $group_link);
			$this->db->insert('all_group', $dataInsert);
        }

        return "true";
    }

    public function week_schedule($user_id)
    {
        $q = $this->db->select('user_group')->from('users')->where('user_id', $user_id)->get()->row();
        $user_group = $q->user_group;

        $getdata = http_build_query(
            array(
                'query' => $user_group 
             )
            );
            
        $opts = array('http' =>
                array(
                'method'  => 'GET',
                'content' => $getdata,
                'header'=>"Accept-language: ru\r\n" .
                "Content-Type: text/html; charset=UTF-8"
            )
            );
            
        $context  = stream_context_create($opts);
            
        $response = file_get_contents('http://ictis.sfedu.ru/schedule-api/?'.$getdata, false, $context);
        $group_schedule = json_decode($response);

        $week = $group_schedule->table->week;

        $array = array('user_group' => $user_group, 'week' => $week);

        $q = $this->db->select('id')->from('group_schedule')->where($array)->get()->row();
        if($q->id != null){
            $response = 'True';
        } else {
            $q = $this->db->select('group_link')->from('all_group')->where('group_name', $user_group)->get()->row();
            $response = $this->insert_schedule($user_group, $q->group_link, $week);
            // $response = 'True';
        }   

        return $response;
    }

    public function insert_schedule($user_group, $group, $week)
    {
        $getdata = http_build_query(
            array(
                'group' => $group,
                'week' => $week
             )
            );
            
        $opts = array('http' =>
                array(
                'method'  => 'GET',
                'content' => $getdata,
                'header'=>"Accept-language: ru\r\n" .
                "Content-Type: text/html; charset=UTF-8"
            )
            );
            
        $context  = stream_context_create($opts);
            
        $response = file_get_contents('http://ictis.sfedu.ru/schedule-api/?'.$getdata, false, $context);
        $groups = json_decode($response, true);

        foreach (range(2, 7) as $i) {
            for($j = 1; $j <= 7; $j++){
                switch ($j) {
                    case 1:
                        $time = "08:00-09:35";
                        break;
                    case 2:
                        $time = "09:50-11:25";
                        break;
                    case 3:
                        $time = "11:55-13:30";
                        break;
                    case 4:
                        $time = "13:45-15:20";
                        break;
                    case 5:
                        $time = "15:50-17:25";
                        break;
                    case 6:
                        $time = "17:40-19:15";
                        break;
                    case 7:
                        $time = "19:30-21:05";
                        break;
                }
                $schedule_array = array('date' => $groups["table"]["table"][$i][0], 'pair_name' => $groups["table"]["table"][$i][$j], 'user_group' => $user_group, 'week' => $week, 'time' => $time, 'pair_count' => $j);
                $this->db->insert('group_schedule', $schedule_array);
            }
        }

        return 'true';
    }

    public function day_schedule($day_of_week, $user_id){
        $q = $this->db->select('user_group')->from('users')->where('user_id', $user_id)->get()->row();
        $user_group = $q->user_group;

        $this->db->query("DROP TABLE IF EXISTS tmp_schedule");
        $this->db->query("CREATE TEMPORARY TABLE tmp_schedule
                        (PRIMARY KEY (id))
                        SELECT id, date, pair_name, time, pair_count
                        FROM group_schedule
                        WHERE user_group = '$user_group' AND date LIKE '$day_of_week%'");
        $q = $this->db->select('pair_name, time')->from('tmp_schedule')->order_by('pair_count')->get();
        $pairs = $q->result();
        $q1 = $this->db->select('date')->from('tmp_schedule')->limit(1)->get()->row();

        $data = array('day' => $q1->date, 'pairs' => $pairs);

        return $data;
    }
}
?>