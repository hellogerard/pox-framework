<?php

/**
 * All functions in this class should be static, and not use any
 * business-specific logic.
 */

class Utilities
{
    public static function purgeOldFilesIn($dir)
    {
        // delete files in $dir older than 1 hour
        $now = $_SERVER['REQUEST_TIME'];
        $handle = opendir($dir);

        while (($file = readdir($handle)) !== false)
        {
            $path = "$dir/$file";
            // ignore hidden files and special files "." and ".."
            if (strncmp('.', $file, 1) != 0 && ($now - filemtime($path) > 3600))
            {
                unlink($path);
            }
        }

        closedir($handle);
    } 

    public static function randomPattern($length = 8)
    {
        $chars = '1234567890abcdefghijknopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $size = strlen($chars) - 1;
        $pattern = '';

        for ($i = 0; $i < $length; $i++)
        {
            $pattern .= $chars[rand(0, $size)];
        }

        return $pattern;
    }

    public static function howLongAgo($then)
    {
        $delta = time() - $then;

        if ($delta < 60) 
        {
            $howlong = 'less than a minute ago';
        }
        else if ($delta < 120) 
        {
            $howlong = 'about a minute ago';
        }
        else if ($delta < (60 * 60)) 
        {
            $howlong = round((float)($delta / 60.0)) . ' minutes ago';
        }
        else if ($delta < (120 * 60)) 
        {
            $howlong = 'about an hour ago';
        }
        else if ($delta < (24 * 60 * 60)) 
        {
            $howlong = 'about ' . round((float) ($delta / 3600.0)) . ' hours ago';
        }
        else if ($delta < (48 * 60 * 60)) 
        {
            $howlong = '1 day ago';
        }
        else 
        {
            $days = round((float) ($delta / 86400.0));

            $howlong = "$days days ago";
            if ($days > 7)
            {
                $howlong = date('F j \a\t g:ia', $then);
            }
        }

        return $howlong;
    }

    public static function newIv()
    {
        $size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, 'ctr');
        $iv = mcrypt_create_iv($size, MCRYPT_RAND);
        $iv = base64_encode($iv);
        return $iv;
    }

    public static function encrypt($salt, $iv, $text)
    {
        $iv = base64_decode($iv);
        $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $salt, $text, 'ctr', $iv);
        $encrypted = base64_encode($encrypted);
        return $encrypted;
    }

    public static function decrypt($salt, $iv, $encrypted)
    {
        $iv = base64_decode($iv);
        $encrypted = base64_decode($encrypted);
        $text = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, $encrypted, 'ctr', $iv);
        return $text;
    }
}

