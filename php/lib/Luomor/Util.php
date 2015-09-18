<?php
/**
 * Created by PhpStorm.
 * User: peterzhang
 * Date: 9/18/15
 * Time: 8:42 PM
 */
namespace Luomor;

class Util {
    const INVITE_CODE_LEN = 6;
    const LONG_INVITE_CODE_LEN = 8;
    const BIG_INVITE_CODE_LEN = 10;
    const USER_INVITE_CODE_LEN = 7;
    const USER_INVITE_CODE_PREFIX = 'D';
    const VALID_CHAR_LIST = "0123456789ABCDEFGHJKMNPQRSTVWXYZ";


    // 注册来源的Salt
    private static $_salt = "4ff350bde75104703989e2139e9ed1c4";
    // 用户邀请码以及为第三方提供的邀请码的salt
    private static $_inviteCodeSalt = "696bf2587c53bb5a";


    // 生成来源
    public static function encryptSource($source) {
        $sign = substr(sprintf("%u", crc32($source . self::$_salt)), -2);
        return $source . $sign;
    }

    // 校验来源，并且返回来源
    public static function decryptSource($sourceWithSign) {
        $source = substr($sourceWithSign, 0, -2);

        return self::encryptSource($source) == $sourceWithSign ? $source : '';
    }


    /**
     * 为第三方生成邀请码, 目前是去哪儿
     *  生成规则 prefix + alphaIdI($id) + sign
     *  prefix 长度为一个字符
     *  sign 为 签名, 长度也是一个字符
     *
     * @param int $id
     * @param string $prefix
     * @param int $len
     *
     * @return string
     **/
    public static function generateCode($id, $prefix, $len = self::INVITE_CODE_LEN) {
        $chars = self::VALID_CHAR_LIST;
        if($len == self::INVITE_CODE_LEN) {
            $alphaId = self::alphaID($id, false, $len - 2);
            $s = strtoupper($prefix . $alphaId);
            $sign = abs(crc32($s . self::$_inviteCodeSalt)) % 32;
            return $s . $chars[$sign];
        } elseif($len == self::LONG_INVITE_CODE_LEN) {
            $alphaId = self::alphaID($id, false, $len - 4);
            $s = strtoupper($prefix . $alphaId);
            $sign1= abs(crc32($s . self::$_inviteCodeSalt)) % 32;
            $sign2 = abs(crc32(self::$_inviteCodeSalt . $s)) % 32;
            $sign3 = abs(crc32(self::$_inviteCodeSalt . $s . self::$_inviteCodeSalt)) % 32;
            return substr($s, 0, 3) . $chars[$sign1] . $s[3] . $chars[$sign2] . $s[4] . $chars[$sign3];
        } elseif($len == self::BIG_INVITE_CODE_LEN) {
            $alphaId = self::alphaID($id, false, $len - 4);
            $s = strtoupper($prefix . $alphaId);
            $sign1= abs(crc32($s . self::$_inviteCodeSalt)) % 32;
            $sign2 = abs(crc32(self::$_inviteCodeSalt . $s)) % 32;
            $sign3 = abs(crc32(self::$_inviteCodeSalt . $s . self::$_inviteCodeSalt)) % 32;
            return substr($s, 0, 5) . $chars[$sign1] . $s[5] . $chars[$sign2] . $s[6] . $chars[$sign3];
        }
        return '';
    }

    /**
     * 校验为第三方生成的邀请码的签名是否正确
     * @param $str
     * @return bool
     **/
    public static function verifyCode($str) {
        $len = strlen($str);
        $chars = self::VALID_CHAR_LIST;
        $str = str_replace(array("I", "L", "O", "U"), array("1", "1", "0", "V"), strtoupper($str));
        if($len == self::INVITE_CODE_LEN) {
            $sign = abs(crc32(substr($str, 0, -1) . self::$_inviteCodeSalt)) % 32;
            return $str[$len - 1] == $chars[$sign];
        } else if($len == self::LONG_INVITE_CODE_LEN) {
            $origin = substr($str, 0, 3) . $str[4] . $str[6];
            $sign1 = abs(crc32($origin . self::$_inviteCodeSalt)) % 32;
            $sign2 = abs(crc32(self::$_inviteCodeSalt . $origin)) % 32;
            $sign3 = abs(crc32(self::$_inviteCodeSalt . $origin . self::$_inviteCodeSalt)) % 32;
            return $str[3] == $chars[$sign1] && $str[5] == $chars[$sign2] && $str[7] == $chars[$sign3];
        }
        return false;
    }

    /**
     * 校验为第三方生成的邀请码，如果正确则返回邀请码，否则返回false
     * @param $str
     * @return bool
     **/
    public static function getCode($str) {
        $len = strlen($str);
        $str = str_replace(array("I", "L", "O", "U"), array("1", "1", "0", "V"), strtoupper($str));
        $chars = self::VALID_CHAR_LIST;
        if($len == self::INVITE_CODE_LEN) {
            $sign = abs(crc32(substr($str, 0, -1) . self::$_inviteCodeSalt)) % 32;
            return $str[$len - 1] == $chars[$sign] ? $str : false;
        } else if($len == self::LONG_INVITE_CODE_LEN) {
            $origin = substr($str, 0, 3) . $str[4] . $str[6];
            $sign1 = abs(crc32($origin . self::$_inviteCodeSalt)) % 32;
            $sign2 = abs(crc32(self::$_inviteCodeSalt . $origin)) % 32;
            $sign3 = abs(crc32(self::$_inviteCodeSalt . $origin . self::$_inviteCodeSalt)) % 32;
            if($str[3] == $chars[$sign1] && $str[5] == $chars[$sign2] && $str[7] == $chars[$sign3]) {
                return $str;
            }
        }
        return '';
    }

    /**
     * 获取到用户自己的邀请码
     * 生成规则: 'Y' + alphdId + sign
     *
     * @param $userId
     * @return string
     * @throws Exception
     */
    public static function generateUserCode($userId) {
        if($userId > 33554432) { // 目前会员转介绍的邀请码长度为7位 pow(32, 5)
            throw new Exception ("Cann't get the invite code");
        }
        $t = self::alphaID($userId, false, self::USER_INVITE_CODE_LEN - 2);
        $s = strtoupper(self::USER_INVITE_CODE_PREFIX . $t);
        $sign = abs(crc32($s . self::$_inviteCodeSalt)) % 32;
        return $s . substr(self::VALID_CHAR_LIST, $sign, 1);
    }

    /**
     * 根据用户提供的邀请码获取用户的user_id
     *
     * @param $code
     * @return bool|int
     */
    public static function getUserIdByUserCode($code) {
        $code = str_replace(array("I", "L", "O", "U"), array("1", "1", "0", "V"), strtoupper($code));
        if(strlen($code) != self::USER_INVITE_CODE_LEN &&
            strncmp($code, self::USER_INVITE_CODE_PREFIX, 1) === 0) {
            return false;
        }
        $s = substr($code, 0, self::USER_INVITE_CODE_LEN - 1);
        if(abs(crc32($s . self::$_inviteCodeSalt)) % 32 === strpos(self::VALID_CHAR_LIST, substr($code, -1))) {
            return (int)self::alphaID(substr($s, 1), true, self::USER_INVITE_CODE_LEN - 2);
        }
        return false;
    }

    /**
     * 验证第三方邀请码是否有效，并且返回该邀请码id
     * @param string $inviteCode 邀请码
     * @return int 成功返回id 否则返回 FALSE
     */
    public static function getIdByCodeThirdparty($inviteCode) {
        $len = strlen($inviteCode);
        if($len != self::BIG_INVITE_CODE_LEN) {
            return FALSE;
        }
        $inviteCode = str_replace(array("I", "L", "O", "U"), array("1", "1", "0", "V"), strtoupper($inviteCode));
        $chars = self::VALID_CHAR_LIST;
        $origin = substr($inviteCode, 0, 5) . $inviteCode[6] . $inviteCode[8];
        $sign1 = abs(crc32($origin . self::$_inviteCodeSalt)) % 32;
        $sign2 = abs(crc32(self::$_inviteCodeSalt . $origin)) % 32;
        $sign3 = abs(crc32(self::$_inviteCodeSalt . $origin . self::$_inviteCodeSalt)) % 32;
        if($inviteCode[5] != $chars[$sign1] || $inviteCode[7] != $chars[$sign2] || $inviteCode[9] != $chars[$sign3]) {
            return FALSE;
        }

        $alpha = substr($inviteCode, 1, 4) . $inviteCode[6] . $inviteCode[8];
        return self::alphaID($alpha, TRUE, self::BIG_INVITE_CODE_LEN - 4);
    }

    /**
     * decimal and 32 hex transform
     * @see http://en.wikipedia.org/wiki/Base32
     *
     * @param mixed $in  string or int
     * @param bool $to_num  string => int
     * @param bool $pad_up alphaId length
     * @param string $passKey the key to reorder index
     *
     * @return int|number|string
     */
    public static function alphaID($in, $to_num = false, $pad_up = false, $passKey = null) {
        //excludes I, L, O, U
        $index = "0123456789ABCDEFGHJKMNPQRSTVWXYZ";
        if($to_num) {
            $in = str_replace(array("I", "L", "O", "U"), array("1", "1", "0", "V"), strtoupper($in));
        }
        if ($passKey !== null) {
            // Although this function's purpose is to just make the
            // ID short - and not so much secure,
            // with this patch by Simon Franz (http://blog.snaky.org/)
            // you can optionally supply a password to make it harder
            // to calculate the corresponding numeric ID

            for ($n = 0; $n<strlen($index); $n++) {
                $i[] = substr( $index,$n ,1);
            }

            $passhash = hash('sha256',$passKey);
            $passhash = (strlen($passhash) < strlen($index))
                ? hash('sha512',$passKey)
                : $passhash;

            for ($n=0; $n < strlen($index); $n++) {
                $p[] = substr($passhash, $n ,1);
            }

            array_multisort($p, SORT_DESC, $i);
            $index = implode($i);
        }

        $base = strlen($index);
        if ($to_num) {
            // Digital number <<-- alphabet letter code
            $in = strrev($in);
            $out = 0;
            $len = strlen($in) - 1;
            for ($t = 0; $t <= $len; $t++) {
                $bcpow = bcpow($base, $len - $t);
                $out = $out + strpos($index, substr($in, $t, 1)) * $bcpow;
            }

            if (is_numeric($pad_up)) {
                $pad_up--;
                if ($pad_up > 0) {
                    $out -= pow($base, $pad_up);
                }
            }
            $out = sprintf('%F', $out);
            $out = substr($out, 0, strpos($out, '.'));
        } else {
            // Digital number -->> alphabet letter code
            if (is_numeric($pad_up)) {
                $pad_up--;
                if ($pad_up > 0) {
                    $in += pow($base, $pad_up);
                }
            }

            $out = "";
            for ($t = floor(log($in, $base)); $t >= 0; $t--) {
                $bcp = bcpow($base, $t);
                $a = floor($in / $bcp) % $base;
                $out = $out . substr($index, $a, 1);
                $in = $in - ($a * $bcp);
            }
            $out = strrev($out); // reverse
        }

        return $out;
    }

    /**
     * 根据中文的姓名规则，以及复姓列表，获取尝试去获取其姓氏
     *  目前规则,
     *    如果名字小于3位，直接取第一个字作为其姓氏
     *    如果为3位及以上，取前两个字查复姓列表，如果在里面，则认为是复姓
     *  该算法可能会有误，如果用户的姓名前面2个字正好是一个复姓的话
     *
     * @param string $name 姓名
     * @return string 姓氏
     *
     **/
    public static function getSurname($name) {
        $list = array(
            "欧阳","太史","端木","上官","司马","东方","独孤","南宫","万俟","闻人",
            "夏侯","诸葛","尉迟","公羊","赫连","澹台","皇甫","宗政","濮阳","公冶",
            "太叔","申屠","公孙","慕容","仲孙","钟离","长孙","宇文","司徒","鲜于",
            "司空","闾丘","子车","亓官","司寇","巫马","公西","颛孙","壤驷","公良",
            "漆雕","乐正","宰父","谷梁","拓跋","夹谷","轩辕","令狐","段干","百里",
            "呼延","东郭","南门","羊舌","微生","公户","公玉","公仪","梁丘","公仲",
            "公上","公门","公山","公坚","左丘","公伯","西门","公祖","第五","公乘",
            "贯丘","公皙","南荣","东里","东宫","仲长","子书","子桑","即墨","达奚","褚师");
        $len = mb_strlen($name, "UTF-8");
        $surnameLen = 1;
        if($len > 2) {
            $t = mb_substr($name, 0, 2, "UTF-8");
            if(array_search($t, $list) !== false) {
                $surnameLen = 2;
            }
        }

        return mb_substr($name, 0, $surnameLen, "UTF-8");
    }

    private static $_scwsInstance = null;
    public static function splitWord($str) {
        $scws = self::$_scwsInstance;
        if(self::$_scwsInstance == null) {
            $scws = scws_new();
            $scws->set_charset("utf8");
            $scws->set_dict("/usr/share/scws/dict.utf8.xdb");
            $scws->set_rule("/etc/rules.utf8.ini");
            $scws->set_duality(true);
            self::$_scwsInstance = $scws;
        }

        $scws->send_text($str);

        $ret = array();
        while($res = $scws->get_result()) {
            foreach($res as $r) {
                $ret[] = $r['word'];
            }
        }
        return $ret;
    }

    //取得车牌号
    public static function getPlate($plate, $default = '未知') {
        $plate = trim(strval($plate));
        if (empty($plate)) {
            $plate = $default;
        } else {
            $l = mb_strlen($plate, 'utf-8');
            $l = $l < 5 ? $l - 2 : $l = 3;
            $plate = mb_substr($plate, 0, 1, 'utf-8') . '***' . mb_substr($plate, $l * -1, $l, 'utf-8');
        }
        return $plate;
    }

    /**************************************XML操作代码 开始******************************************/
    public static function getXMLInstance(){
        return new XmlWriter();
    }

    public static function xmlToArray( $xml ){
        $reg = '/<(\w+)[^>]*>([\x00-\xFF]*)<\/\1>/';
        if(preg_match_all($reg, $xml, $matches))
        {
            $count = count($matches[0]);
            for($i = 0; $i < $count; $i++)
            {
                $subxml= $matches[2][$i];
                $key = $matches[1][$i];
                if(preg_match( $reg, $subxml ))
                {
                    $arr[$key] = self::xmlToArray( $subxml );
                }else{
                    $arr[$key] = $subxml;
                }
            }
        }
        return $arr;
    }

    public static function arrToXml($data){
        $xml = self::getXMLInstance();
        $xml->openMemory();
        self::_buildXml($data,$xml);
        return $xml->outputMemory(true);
    }

    public static function _buildXml($data,$xmlWriter){
        foreach($data as $key => $value){
            if(is_numeric($key)) {
                $key = 'value';
            }
            if(!is_array($value)){
                $xmlWriter->writeElement($key, $value);
                continue;
            } else {
                $xmlWriter->startElement($key);
                self::_buildXml($value,$xmlWriter);
                $xmlWriter->endElement();
            }
        }
    }
    /**************************************XML操作代码 结束******************************************/

    /**
     * 加密数字ID
     *
     * @param int $id  最大支持 10^16
     * @param string $salt
     *
     * @return bool|int
     *
     **/
    public static function encryptId($id, $salt = '') {
        $x = base_convert((int) $id, 10, 36);
        $t = base_convert(sha1($x . ($salt ?: self::$_salt)), 16, 36);
        $len = base_convert(strlen($x), 10, 36);
        return substr($t, 0, 5) . $len . $x . substr($t, - (10 - $len));
    }

    /**
     * 解密数字ID
     *
     * @param string $encryptedId
     * @param string $salt
     *
     * @return bool|int
     */
    public static function decryptId($encryptedId, $salt = '') {
        if(strlen($encryptedId) != 16) {
            return false;
        }
        $len = base_convert(substr($encryptedId, 5, 1), 36, 10);
        $id = base_convert(substr($encryptedId, 6, $len), 36, 10);
        if(self::encryptId($id, $salt) == $encryptedId) {
            return (int) $id;
        }
        return false;
    }

}