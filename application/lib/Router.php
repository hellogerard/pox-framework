<?php


class Router
{
    /* 
     * You can put any routing needs, such as a list of hard-coded routes for 
     * special holiday pages, etc.
     */

    public static function route($uri)
    {
        // remove the query string, if any
        $uri = str_replace('?' . $_SERVER['QUERY_STRING'], '', $uri);

        $segments = explode('/', $uri);

        $module = (empty($segments[1])) ? 'index' : $segments[1];
        $action = (empty($segments[2])) ? 'index' : $segments[2];

        $controller = ucfirst($module) . 'Controller';

        // if controller exists, call the action on it
        if (file_exists(APP_ROOT . "/application/controllers/$controller.php"))
        {
            $controller = new $controller($module);

            // call the pre-dispatch hook
            $controller->preDispatch();

            // the remaining "path segments" of the URI are passed as args to 
            // the action, 1 arg per path segment, in order
            call_user_func_array(array($controller, $action), array_slice($segments, 3));

            // call the post-dispatch hook
            $controller->postDispatch();
        }

        // else return 404 Not Found
        else
        {
            self::notFound();
        }
    }

    public static function notFound()
    {
        ob_end_clean();

        header('HTTP/1.0 404 Not Found');
        echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">';
        echo '<HTML><HEAD>';
        echo '<TITLE>404 Not Found</TITLE>';
        echo '</HEAD><BODY>';
        echo '<H1>Not Found</H1>';
        echo 'The requested URL was not found on this server.<P>';
        echo '</BODY></HTML>';
        exit;
    }
}

