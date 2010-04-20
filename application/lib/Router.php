<?php


class Router
{
    /* 
     * You can put any routing needs, such as a list of hard-coded routes for 
     * special holiday pages, etc.
     */

    private static function _namespace($subdomain)
    {
        switch ($subdomain)
        {
            case 'gomobile':
                $namespace = 'Gomobile_';
                break;
            case 'mobilize':
                $namespace = 'Mobilize_';
                break;
            default:
                $namespace = '';
                break;
        }

        return $namespace;
    }

    public static function route($uri)
    {
        // get the subdomain
        $segments = explode('.', $_SERVER['HTTP_HOST']);
        $subdomain = strtolower($segments[0]);

        // use subdomain to determine controller namespace/subdir, if any
        $namespace = self::_namespace($subdomain);
        $subdir = str_replace('_', DIRECTORY_SEPARATOR, $namespace);

        // remove the query string, if any
        $uri = str_replace('?' . $_SERVER['QUERY_STRING'], '', $uri);

        // deconstruct the URI to get module, controller, action
        $segments = explode('/', $uri);

        $module = (empty($segments[1])) ? 'home' : $segments[1];
        $action = (empty($segments[2])) ? 'index' : $segments[2];

        $controller = ucfirst($module) . 'Controller';
        $offset = 3;

        // if $module is a venue, then NEXT segments are controller/action
        if (! file_exists(APP_ROOT . "/application/controllers/{$subdir}{$controller}.php"))
        {
            $module = (empty($segments[2])) ? 'home' : $segments[2];
            $action = (empty($segments[3])) ? 'index' : $segments[3];

            $controller = ucfirst($module) . 'Controller';
            $offset = 4;
        }

        // if controller exists, call the action on it
        if (file_exists(APP_ROOT . "/application/controllers/{$subdir}{$controller}.php"))
        {
            $class = $namespace . $controller;
            $controller = new $class($module);

            try
            {
                // call the pre-dispatch hook
                $controller->preDispatch();

                // the remaining "path segments" of the URI are passed as args
                // to the action, 1 arg per path segment, in order
                call_user_func_array(array($controller, $action), array_slice($segments, $offset));

                // call the post-dispatch hook
                $controller->postDispatch();
            }
            catch (Exception $e)
            {
                if ($e->getMessage() == 'Invalid slug URL' 
                        || $e->getMessage() == 'Slug URL not found')
                {
                    self::notFound();
                }

                throw $e;
            }
        }

        // else
        else
        {
            // if the static route exists, use it
            $controller = new StaticController($module);

            try
            {
                // call the pre-dispatch hook
                $controller->preDispatch();

                // pass the entire URI into the static router
                $controller->route($uri);

                // call the post-dispatch hook
                $controller->postDispatch();
            }
            catch (Exception $e)
            {
                if ($e->getMessage() == 'Route not found')
                {
                    // else return 404 Not Found
                    self::notFound();
                }

                throw $e;
            }
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

