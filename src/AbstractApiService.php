<?php

namespace WonderWp\Component\API;

use WonderWp\Component\HttpFoundation\Request;
use WonderWp\Component\HttpFoundation\Result;

abstract class AbstractApiService implements ApiServiceInterface
{
    /** @var Request */
    protected $request;

    protected function enableErrors(){
        error_reporting(E_ALL);
        ini_set('display_errors','on');
    }

    /** @inheritdoc */
    public function registerEndpoints()
    {
        $exclude   = ['__construct', 'registerEndpoints'];
        $className = (new \ReflectionClass($this))->getShortName();
        $methods   = array_diff(get_class_methods($this), $exclude);

        foreach ($methods as $method) {
            add_action('wp_ajax_' . $className . '.' . $method, function () use ($method) {
                $this->setRequest(Request::getInstance());
                call_user_func([$this, $method]);
                wp_die();
            });

            add_action('wp_ajax_nopriv_' . $className . '.' . $method, function () use ($method) {
                $this->setRequest(Request::getInstance());
                call_user_func([$this, $method]);
                wp_die();
            });
        }
    }

    /**
     * @codeCoverageIgnore
     * @param mixed $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @param Result $result
     * @param string $format
     *
     * @return Result
     */
    public function respond(Result $result, $format = 'json')
    {
        if ($format === 'json') {
            header('Content-Type: application/json');
            echo $result;
        }

        return $result;
    }

    /**
     * @param string $paramName
     *
     * @return array|mixed
     */
    public function requestParameter($paramName = 'all')
    {
        if ($paramName == 'all') {
            return $this->request->request->all();
        } else {
            return $this->request->request->get($paramName);
        }
    }
}