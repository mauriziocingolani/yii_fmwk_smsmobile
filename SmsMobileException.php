<?php
/**
 * 
 */
class SmsMobileException extends CException {

    public function __construct($message) {
        parent::__construct(trim($message));
    }

}
