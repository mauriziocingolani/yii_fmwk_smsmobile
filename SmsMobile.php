<?php

/**
 * Handles POST/GET requests to SmsMobile.it online SMS service.
 * --------------------------------------------------------------------------------
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------------
 * 
 * @copyright (c) 2015, Maurizio Cingolani
 * @license https://www.gnu.org/copyleft/gpl.html
 * @author Maurizio Cingolani <mauriziocingolani74@gmail.com>
 * @version 1.0
 */
class SmsMobile extends CApplicationComponent {

    const URL = 'http://sms.smsmobile-ba.com/sms';
    const CREDIT_MODE_CREDIT = 'credit';
    const CREDIT_MODE_LOW_QUALITY_MESSAGES = 'll';
    const CREDIT_MODE_HIGH_QUALITY_MESSAGES = 'a';
    const QUALITY_LOW = 'll';
    const QUALITY_AUTO = 'a';
    const QUALITY_NOTIFY = 'n';
    const OPERATION_TEXT = 'TEXT';
    const OPERATION_MULTITEXT = 'MULTITEXT';

    public $username;
    public $password;
    public $sender;
    private static $_config;

    public function init() {
        parent::init();
        self::$_config = array(
            CURLOPT_AUTOREFERER => true,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER => false,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            CURLOPT_VERBOSE => true,
        );
    }

    public function send($rcpt, $data, $sender) {
        $output = $this->_post('send', array(
            'rcpt' => $rcpt,
            'data' => $data,
            'sender' => $sender,
            'qty' => self::QUALITY_NOTIFY,
            'operation' => strlen($data) > 160 ? self::OPERATION_MULTITEXT : self::OPERATION_TEXT,
            'return_id' => 1,
        ));
        switch (substr($output, 0, 2)) :
            case 'OK':
                return substr($output, 3); # id del sms
            case 'KO':
                throw new SmsMobileException(substr($output, 2));
        endswitch;
    }

    public function credit($mode = self::CREDIT_MODE_CREDIT) {
        $output = $this->_post('credit', array('type' => $mode));
        switch (substr($output, 0, 2)) :
            case 'OK':
                if ($mode == self::CREDIT_MODE_CREDIT) :
                    return (float) substr($output, 2); # credito o sms rimanenti
                else :
                    return (int) substr($output, 3);
            endif;
            case 'KO':
                throw new SmsMobileException(substr($output, 2));
        endswitch;
    }

    public function batchStatus($id) {
        $output = $this->_post('batch-status', array(
            'id' => $id,
            'type' => 'notify',
            'schema' => 1,
        ));
        switch (substr($output, 0, 2)) :
            case 'KO':
                throw new SmsMobileException(substr($output, 2));
            default:
                $lines = preg_split("/[\r\n]/", $output);
                $data = preg_split('/[,]/', $lines[1]);
                return new SmsInfo($data);
        endswitch;
    }

    public function batchStatusInterval($start, $end) {
        $output = $this->_post('batch-status-interval', array(
            'from' => $start,
            'to' => $end,
            'type' => 'notify',
            'schema' => 1,
        ));
        switch (substr($output, 0, 2)) :
            case 'KO':
                throw new SmsMobileException(substr($output, 2));
            default:
//                $splt = preg_split('/[\r\n]/', $output);
//                $headers = preg_split('/,/', array_shift($splt));
//                $data = new CList;
//                foreach ($splt as $line) :
//                    $d = preg_split('/,/', array_shift($splt));
//                    $o = new stdClass();
//                    for ($i = 0, $n = count($d); $i < $n; $i++) :
//$o
//                    endfor;
//                endforeach;
                CVarDumper::dump($output, 10, true);
                return true;
        endswitch;
    }

    private function _post($function, array $params = null) {
        # options
        $options = self::$_config;
        $options[CURLOPT_POST] = false;
        $options[CURLOPT_POSTFIELDS] = array('user' => $this->username, 'pass' => $this->password);
        if ($params && count($params) > 0) :
            foreach ($params as $key => $value) :
                $options[CURLOPT_POSTFIELDS][$key] = $value;
            endforeach;
        endif;
        # curl
        $ch = curl_init(self::URL . '/' . $function . '.php');
        curl_setopt_array($ch, $options);
        $output = curl_exec($ch);
        if ($output === false) :
            return curl_error($ch);
        else :
            return $output;
        endif;
    }

}
