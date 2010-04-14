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

        // these defaults should be set in config somehow?
        $module = (empty($segments[1])) ? 'home' : $segments[1];
        $action = (empty($segments[2])) ? 'index' : $segments[2];

        $controller = ucfirst($module) . 'Controller';

        // if controller exists, call the action on it
        if (file_exists("../controllers/$controller.php"))
        {
            Zend_Registry::get('logger')->debug("calling $controller::$action()");

            $controller = new $controller($module);

            // the remaining "path segments" of the URI are passed as args to 
            // the action, 1 arg per path segment, in order
            call_user_func_array(array($controller, $action), array_slice($segments, 3));
        }

        // else return 404 Not Found
        else
        {
            Zend_Registry::get('logger')->debug("$controller::$action() not found");

            header('HTTP/1.0 404 Not Found');
            exit;
        }
    }
}

