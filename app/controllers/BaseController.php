<?php
namespace App\Controllers;

class BaseController
{
    protected $viewData = [];
    public $requestData = [];
    public $rules = [];
    public $errors = [];

    public function __construct()
    {
        $this->requestData = array_merge($this->getPostParams(), $this->getGetParams());
    }

    /**
     * Validate input parameters
     *
     * @param array $params Input parameters
     * @return array Validation result
     */
    protected function validateParams(array $params): array
    {
        $errors = [];

        foreach ($this->rules as $key => $rule) {
            if (isset($rule['required']) && $rule['required'] && !isset($params[$key])) {
                $errors[$key][] = 'This field is required.';
            }

            if (isset($rule['type']) && $rule['type'] == 'numeric' && isset($params[$key]) && !is_numeric($params[$key])) {
                $errors[$key][] = 'Invalid type. Expected numeric';
            }

            if (isset($rule['max-length']) && isset($params[$key]) && strlen($params[$key]) > $rule['max-length']) {
                $errors[$key][] = 'Invalid length. Expected <= ' . $rule['max-length'] . '.';
            }

            if (isset($rule['min-length']) && isset($params[$key]) && strlen($params[$key]) < $rule['min-length']) {
                $errors[$key][] = 'Invalid length. Expected > ' . $rule['min-length'] . '.';
            }

            if (isset($rule['min-value']) && isset($params[$key]) && ($params[$key]) < $rule['min-value']) {
                $errors[$key][] = 'Invalid value. Expected >= ' . $rule['min-value'] . '.';
            }
        }

        return $errors;
    }

    protected function executeValidation()
    {
        $this->errors = $this->validateParams($this->requestData);

        if(!empty($this->errors)) {
            $this->jsonResponse(['success' => false, 'errors' => $this->errors], 400);
        }
    }

    /**
     * Load a model.
     *
     * @param string $model Name of the model class.
     * @return object       Instance of the model.
     */
    public function loadModel($model)
    {
        require_once "../models/$model.php";

        return new $model();
    }

    /**
     * Return a JSON response.
     *
     * @param array  $data Data to return as JSON.
     * @param int    $status HTTP status code (default: 200).
     */
    public function jsonResponse($data, $status = 200)
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);

        exit;
    }

    /**
     * Set data to be included in the JSON response.
     *
     * @param string $key   The name of the data.
     * @param mixed  $value The value of the data.
     */
    public function set($key, $value)
    {
        $this->viewData[$key] = $value;
    }

    /**
     * Redirect to another page.
     *
     * @param string $url The URL to redirect to.
     */
    public function redirect($url)
    {
        header("Location: $url");
        
        exit;
    }
    
    /**
     * Retrieve all POST parameters.
     *
     * @return array
     */
    protected function getPostParams(): array
    {
        $params = $_POST;
        
        foreach ($params as $key => $value) {
            $params[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        return $params;
    }

    /**
     * Retrieve all GET parameters.
     *
     * @return array
     */
    protected function getGetParams(): array
    {
        $params = $_GET;
        
        foreach ($params as $key => $value) {
            $params[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        return $params;
    }
}
?>