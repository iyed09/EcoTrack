<?php
class Router {
    private $routes = [];
    private $params = [];

    public function add($route, $params = []) {
        $route = preg_replace('/\//', '\\/', $route);
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z0-9-]+)', $route);
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);
        $route = '/^' . $route . '$/i';
        $this->routes[$route] = $params;
    }

    public function get($route, $params) {
        $params['method'] = 'GET';
        $this->add($route, $params);
    }

    public function post($route, $params) {
        $params['method'] = 'POST';
        $this->add($route, $params);
    }

    public function match($url, $method = 'GET') {
        foreach ($this->routes as $route => $params) {
            if (preg_match($route, $url, $matches)) {
                if (isset($params['method']) && $params['method'] !== $method) {
                    continue;
                }
                
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $params[$key] = $match;
                    }
                }
                $this->params = $params;
                return true;
            }
        }
        return false;
    }

    public function dispatch($url) {
        $url = $this->removeQueryString($url);
        $method = $_SERVER['REQUEST_METHOD'];
        
        if ($this->match($url, $method) || $this->match($url)) {
            $controller = $this->params['controller'];
            $controller = $this->convertToStudlyCaps($controller) . 'Controller';
            
            $controllerFile = APP_ROOT . '/app/controllers/' . $controller . '.php';
            
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                $controllerObject = new $controller();
                
                $action = $this->params['action'];
                $action = $this->convertToCamelCase($action);
                
                if (is_callable([$controllerObject, $action])) {
                    unset($this->params['controller'], $this->params['action'], $this->params['method']);
                    call_user_func_array([$controllerObject, $action], $this->params);
                } else {
                    $this->error404("Method $action not found in controller $controller");
                }
            } else {
                $this->error404("Controller $controller not found");
            }
        } else {
            $this->error404("Route not found: $url");
        }
    }

    private function convertToStudlyCaps($string) {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    private function convertToCamelCase($string) {
        return lcfirst($this->convertToStudlyCaps($string));
    }

    private function removeQueryString($url) {
        if ($url !== '') {
            $parts = explode('&', $url, 2);
            if (strpos($parts[0], '=') === false) {
                $url = $parts[0];
            } else {
                $url = '';
            }
        }
        return rtrim($url, '/');
    }

    private function error404($message = '') {
        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1>";
        if ($message) {
            echo "<p>$message</p>";
        }
        exit;
    }

    public function getParams() {
        return $this->params;
    }
}
