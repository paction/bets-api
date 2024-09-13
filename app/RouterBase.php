<?php

class RouterBase 
{
    protected $routes = [];
    protected $currentUri;
    protected $method;

    public function __construct() 
    {
        $this->currentUri = trim($_SERVER['REQUEST_URI']);
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    public function add($method, $uri, $handler) 
    {
        $this->routes[$uri][$method] = $handler;
    }

    public function dispatch() 
    {
        if (array_key_exists($this->currentUri, $this->routes) && 
            array_key_exists($this->method, $this->routes[$this->currentUri])) {
            return call_user_func($this->routes[$this->currentUri][$this->method]);
        } else {
            header("HTTP/1.0 404 Not Found");
            die("Not found");
        }
    }
}