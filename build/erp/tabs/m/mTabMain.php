<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 18.12.2016
 *
 **/
namespace build\erp\tabs\m;
use build\erp\inc\Project;

class mTabMain extends Project
{
    /**
     * @param array $params
     */
    public function save($params){
        $q = '';
        foreach ($params as $id=>$v){
            if(!empty($q))
                $q.=',';
            $q.= "$id = $v";
        }

        if(!empty($q)){
            $this->db->exec("UPDATE tbl_project SET $q WHERE col_projectID=".$this['col_projectID']);
        }
    }

}