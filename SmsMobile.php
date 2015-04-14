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

    public $username;
    public $password;
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

    public function send() {
        
    }

    public function credit($mode = self::CREDIT_MODE_CREDIT) {
        $output = $this->_post('credit', array('type' => $mode));
        switch (substr($output, 0, 2)) :
            case 'OK':
                if ($mode == self::CREDIT_MODE_CREDIT) :
                    return (float) substr($output, 2);
                else :
                    return (int) substr($output, 2);
                endif;
                break;
            case 'KO':
                return $output;
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
