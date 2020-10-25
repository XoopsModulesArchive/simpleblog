<?php
/*
create table token (
    id int(10) private key auto_increment,
    name varchar(100) not null,
    value varchar(100) not null,
    expire int(10) not null
) ENGINE = ISAM;

*/
define('XOOPS_TOKEN_VALID', 1);

define('XOOPS_TOKEN_INVALID', 10);
define('XOOPS_TOKEN_INVALID_NO_TOKEN', 11);
define('XOOPS_TOKEN_INVALID_NO_TOKEN_FORM', 12);
define('XOOPS_TOKEN_INVALID_NO_TOKEN_COOKIE', 13);
define('XOOPS_TOKEN_INVALID_UNMATCH_TOKEN', 14);
define('XOOPS_TOKEN_INVALID_TOKEN_NOT_FOUND', 15);
define('XOOPS_TOKEN_INVALID_ID', 20);
define('XOOPS_TOKEN_INVALID_REFERER', 30);
define('XOOPS_TOKEN_INTERNAL_ERROR', 99);

class XoopsToken
{
    public $_name;

    public $_length;

    public $_id;

    public $_value;

    public $_table = '0123456789abcdefghijklmnopqrstuvwxyz';

    public $_expire;

    public $_current_time;

    public function __construct($name, $expire = 3600)
    {
        $this->_length = 20;

        $this->_name = $name;

        $this->_id = 0;

        $this->_value = null;

        $this->_current_time = time();

        $expire_check = (int)$expire;

        if ($expire_check > 0) {
            $this->_expire = $this->_current_time + $expire_check;
        } else {
            $this->_expire = $this->_current_time + 3600;
        }
    }

    public function delete()
    {
        global $xoopsDB;

        if ($this->_id <= 0) {
            return false;
        }

        $sql = sprintf(
            'delete from %s where id = %u',
            $xoopsDB->prefix('token'),
            $this->_id
        );

        $xoopsDB->queryF($sql);

        return true;
    }

    public function deleteExpired()
    {
        global $xoopsDB;

        if ($this->_id <= 0) {
            return false;
        }

        $sql = sprintf(
            'delete from %s where expire > %u',
            $xoopsDB->prefix('token'),
            (int)$this->_current_time
        );

        $xoopsDB->queryF($sql);

        return true;
    }

    public function _load($value)
    {
        global $xoopsDB;

        $value = trim($value);

        if (preg_match('/^([0-9]+)-([a-zA-Z0-9]*)/', $value, $m)) {
            $id = (int)$m[0];

            if ($id <= 0) {
                return false;
            }

            $value = $m[1];

            if ((mb_strlen($value) != $this->_length)) {
                return false;
            }

            $sql = sprintf(
                'select id, value, expire from %s where id=%u and name=%s and value=%s',
                $xoopsDB->prefix('token'),
                $id,
                $xoopsDB->quoteString($this->_name),
                $xoopsDB->quoteString($value)
            );

            $rs = $xoopsDB->query($sql);

            if (!$rs) {
                return false;
            }

            if (list($id, $value, $expire) = $this->_xoopsDB->fetchRow($rs)) {
                if ($this->_current_time > $expire) {
                    return false;
                }

                $this->_id = $id;

                $this->_value = $value;

                $this->_expire = $expire;

                return true;
            }

            return false;
        }

        return false;
    }

    public function create()
    {
        global $xoopsDB;

        $this->_value = '';

        [, $msec1] = explode(' ', microtime());

        [, $msec2] = explode(' ', microtime());

        [, $msec3] = explode(' ', microtime());

        [, $msec4] = explode(' ', microtime());

        // mt_srand(  ((int)$msec1 ) + ((int)$msec2 ) + ((int)$msec3 ) + ((int)$msec4 ) );

        for ($i = 0; $i < $this->_length; $i++) {
            $key = mt_rand(0, mb_strlen($this->_table) - 1);

            $this->_value .= $this->_table[$key];
        }

        $sql = sprintf(
            'insert into %s(id, name, value, expire) values(0, %s, %s, %u)',
            $xoopsDB->prefix('token'),
            $xoopsDB->quoteString($this->_name),
            $xoopsDB->quoteString($this->_value),
            $this->_current_time + $this->_expire
        );

        $xoopsDB->queryF($sql);

        $this->_id = $xoopsDB->getInsertId();
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getExpire()
    {
        if (0 != $this->_id) {
            return $this->_expire;
        }

        return null;
    }

    public function getValue()
    {
        if (0 != $this->_id) {
            return $this->_id . '-' . $this->_value;
        }

        return null;
    }

    public function getTokenHtml()
    {
        if (0 != $this->_id) {
            $result = sprintf(
                '<input type="hidden" name="%s" value="%s">',
                htmlspecialchars($this->_name, ENT_QUOTES | ENT_HTML5),
                htmlspecialchars($this->_getValue(), ENT_QUOTES | ENT_HTML5)
            );

            return $result;
        }

        return null;
    }

    public function sendTokenCookie($cookie_path = XOOPS_URL, $is_secure = 0)
    {
        $domain = '';

        $path = '';

        $parsed_url = parse_url($cookie_path);

        $domain = $parsed_url['host'];

        $path = $parsed_url['path'];

        $is_secure = (int)$is_secure;

        if (!(0 == $is_secure) && !(1 == $is_secure)) {
            $is_secure = 0;
        }

        setcookie($this->_name, $this->_getValue(), $this->_expire, $path, $domain, $is_secure);
    }

    public function _getToken($array)
    {
        if (array_key_exists($this->_name, $array)) {
            $result = $array[$this->_name];

            if (get_magic_quotes_gpc()) {
                $result = stripslashes($result);
            }

            if (empty($result) || (0 == mb_strlen($result))) {
                return null;
            }

            return $result;
        }

        return null;
    }

    public function checkToken($method = 'POST', $cookie_check = true, $referer_check = true)
    {
        global $_POST, $_GET, $HTTP_COOKIE_VARS;

        if ($referer_check && !XoopsSecurity::checkReferer()) {
            return XOOPS_TOKEN_INVALID_REFERER;
        }

        $formValue = null;

        $checkVar = null;

        if ('POST' == $method) {
            $checkVar = &$_POST;
        } elseif ('GET' == $method) {
            $checkVar = &$_GET;
        } else {
            return XOOPS_TOKEN_INTERNAL_ERROR;
        }

        $formValue = $this->__getToken($checkVar);

        if (null === $formValue) {
            return XOOPS_TOKEN_INVALID_NO_TOKEN_FORM;
        }

        if ($cookie_check) {
            $cookieIdValue = $this->__getToken($HTTP_COOKIE_VARS);

            if (null === $cookieIdValue) {
                return XOOPS_TOKEN_INVALID_NO_TOKEN_COOKIE;
            } elseif ($cookieIdValue != $formIdValue) {
                return XOOPS_TOKEN_INVALID_UNMATCH_TOKEN;
            }
        }

        if (!$this->__load($formValue)) {
            return XOOPS_TOKEN_INVALID_TOKEN_NOT_FOUND;
        }

        if ($this->_getToken() != $formValue) {
            return XOOPS_TOKEN_INVALID_UNMATCH_TOKEN;
        }

        return true;
    }
}
$val = '';
$table = '0123456789abcdefghijklmnopqrstuvwxyz';
[$msec1, $sec1] = explode(' ', microtime());
[$msec2, $sec2] = explode(' ', microtime());
[$msec3, $sec3] = explode(' ', microtime());
[$msec4, $sec4] = explode(' ', microtime());
$sal = ((float)$msec1 * 100000) * ((float)$msec2 * 100000) + ((float)$msec3 * 100000) * ((float)$msec4 * 100000);
// mt_srand(  $sal );
for ($i = 0; $i < 20; $i++) {
    $key = mt_rand(0, mb_strlen($table) - 1);

    $val .= $table[$key];
}
echo $msec4 . "\n";
echo $sal . "\n";
echo $val;
