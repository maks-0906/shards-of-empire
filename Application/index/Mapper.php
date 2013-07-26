<?php

class index_Mapper extends Mapper {
    public function GetUserList() {
        return $this->query('select * from users');
    }
}