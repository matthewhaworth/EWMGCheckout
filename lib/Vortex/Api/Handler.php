<?php
class Vortex_Api_Handler
{
    /**
     * @var Zend_Controller_Request_Http
     */
    protected $request;

    /**
     * @var Zend_Controller_Response_Http
     */
    protected $response;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $apiPrefix;

    /**
     * Vortex_Api_Handler constructor.
     * @param Zend_Controller_Request_Http $request
     * @param Zend_Controller_Response_Http $response
     * @param string $namespace
     * @param string $apiPrefix
     */
    public function __construct(
        Zend_Controller_Request_Http $request,
        Zend_Controller_Response_Http $response,
        $namespace = '',
        $apiPrefix = ''
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->namespace = $namespace;
        $this->apiPrefix = $apiPrefix;
    }

    /**
     * @return void
     */
    public function handle()
    {
        $class = $this->determineClass(
            $this->namespace,
            $this->removeApiPrefix($this->request->getPathInfo()),
            $this->request->getMethod()
        );

        $this->response->setHeader('Content-Type', 'application/json');
        if (!class_exists($class) || !class_implements($class, Vortex_Api_EndpointInterface::class)) {
            $this->do404();
            return;
        }

        try {
            $class = new $class;
            $requestBody = json_decode($this->request->getRawBody(), true);
            if (!$requestBody && $requestBody != false) {
                $this->do4xx();
                return;
            }

            $responseJson = json_encode($class->execute($requestBody ?: [], $this->request, $this->response));
            if (!$responseJson) {
                $this->do500('Could not parse response JSON.');
                return;
            }

            $this->response->setBody($responseJson);
        } catch (Vortex_Api_Exception_BadRequest $e) {
            $this->do4xx($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            $this->do500($e->getMessage());
            return;
        }
    }

    /**
     * @param $url
     * @return string
     */
    private function removeApiPrefix($url)
    {
        $url = ltrim($url ,'/'); // remove leading /
        $url = rtrim($url, '/'); // remove trailing /
        $prefix = ltrim($this->apiPrefix, '/');
        return ltrim(str_replace($prefix, '', $url), '/');
    }

    /**
     * @param $namespace
     * @param $path
     * @param $method
     * @return string
     */
    private function determineClass($namespace, $path, $method)
    {
        $classPathParts = explode('/', $path);
        $classPathParts[] = $method;
        $classPathParts = array_map([$this, 'upperCamelCase'], $classPathParts);
        $classPath = implode('_', $classPathParts);
        return implode('_', [$namespace, $classPath]);
    }

    /**
     * Convert ABC or abc to Abc
     *
     * @param $string
     * @return string
     */
    private function upperCamelCase($string)
    {
        return ucwords(strtolower($string));
    }

    /**
     * Set 400 header and content
     * @param string $message
     * @param int $code
     */
    private function do4xx($message = 'Bad request.', $code = 400)
    {
        $this->response->setHttpResponseCode($code);
        $this->response->setBody(json_encode(['errorCode' => $code, 'errorMessage' => $message]));
    }

    /**
     * Set 404 header and content
     */
    private function do404()
    {
        $this->response->setHttpResponseCode(404);
        $this->response->setBody(json_encode(['errorCode' => '404', 'errorMessage' => 'Not found.']));
    }

    /**
     * Set 500 header and content
     * @param string $errorMessage
     */
    private function do500($errorMessage = '')
    {
        $this->response->setHttpResponseCode(500);
        $this->response->setBody(json_encode(['errorCode' => '500', 'errorMessage' => $errorMessage]));
    }
}