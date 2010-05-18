<?php

class Auth
{
    private static function _db()
    {
        return Zend_Registry::get('db');
    }

    public static function loginWithRememberMe($username, $password)
    {
        return self::login($username, $password, true);
    }

    public static function login($username, $password, $rememberMe = false)
    {
        // check the database for this user
        $sql =  "select user_id
                   from users
                  where email = ? and password = md5(concat(salt, ?))";

        $result = self::_db()->getOne($sql, array($username, $password));

        // if login successful
        if ($result)
        {
            $rand = md5(uniqid(rand(), true));

            // hash the client IP address, along with a random ID.  this becomes
            // the session token for this user. as long as the row exists in the
            // DB, we can auto-login the session.

            $token = md5($_SERVER['REMOTE_ADDR'] . $rand);

            $sql = "insert into sessions
                        (session_token, user_id) values (?, ?)
                        on duplicate key update session_token = ?";
 
            $bind = array($token, $result, $token);

            self::_db()->query($sql, $bind);

            if ($rememberMe)
            {
                // set the "remember me" cookie
                $expires = time() + 604800; // 604800 seconds = 1 week
                $domain = $_SERVER['HTTP_HOST'];
                setcookie('pox_remember_me', $rand, $expires, '/', $domain);
            }

            $_SESSION['pox_username'] = $username;

            return true;
        }

        // else login failed
        return false;
    }

    public static function setUsername($username)
    {
        $_SESSION['pox_username'] = $username;
    }

    public static function isLoggedIn()
    {
        if (isset($_SESSION['pox_username']))
        {
            return $_SESSION['pox_username'];
        }

        // session is gone when either:
        // - browser session cookie has timed out
        // - server session file has been garbage collected
        // - user clicked on logout

        // check for "remember me" cookie
        if (isset($_COOKIE['pox_remember_me']))
        {
            // we have to see if this user has a valid session, from the same
            // computer. create a hash using the client IP, and user_id.  look
            // for a match in DB.

            $token = md5($_SERVER['REMOTE_ADDR'] . $_COOKIE['pox_remember_me']);

            $sql =  "select u.email
                       from users u
                                join sessions s on u.user_id = s.user_id
                      where s.session_token = ?";

            $result = self::_db()->getOne($sql, array($token));

            // if a valid session is found
            if ($result)
            {
                // recreate session
                session_regenerate_id();

                $sql = "update sessions set last_updated_dt_tm = now()
                            where session_token = ?";

                self::_db()->query($sql, array($token));

                return $result;
            }
            else
            {
                return false;
            }
        }

        return false;
    }

    public static function logout()
    {
        // log user out natively
        session_unset();
        session_destroy();


        // clear the session in the DB
        $token = md5($_SERVER['REMOTE_ADDR'] . $_COOKIE['pox_remember_me']);
        $sql =  "delete from sessions where session_token = ?";
        self::_db()->query($sql, array($token));


        // clear the "remember me" cookie
        $domain = $_SERVER['HTTP_HOST'];
        setcookie('pox_remember_me', '', time() - 3600, '/', $domain);


        header("HTTP/1.1 302 Found");
        header("Location: /");
        exit;
    }
}

