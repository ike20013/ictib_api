<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UsersModel extends CI_Model {
    public function registration($user_id, $user_group)
    {
        $q = $this->db->select('id')->from('all_group')->where('group_name', $user_group)->get()->row();
        $w = $this->db->select('id')->from('users')->where('user_id', $user_id)->get()->row();
        if ($w->id != null) {
            return 'user ushe est';
        } else if($q->id != null) {
            $dataInsert = array('user_id' => $user_id, 'user_group' => $user_group);
            $this->db->insert('users', $dataInsert);
            return 'True';
        } else {
            return 'false';
        }
    }
}
?>