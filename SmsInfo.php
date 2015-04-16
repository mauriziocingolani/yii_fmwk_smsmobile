<?php

class SmsInfo extends CComponent {

    public $id;
    public $timestamp;
    public $dest;
    public $status;
    public $status_text;

    public function __construct(array $data) {
        $this->id = $data[0];
        $this->timestamp = strtotime($data[1]);
        $this->dest = $data[2];
        $this->status = $data[3];
        $this->status_text = $data[4];
    }

}
