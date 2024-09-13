<?php
namespace App\Controllers;

use App\Models\User;

/**
 * AuthController is responsible for authentication of a user by username and password.
 * Example request:
 * curl -X POST http://localhost:8090/auth -d "username=bob" -d "password=bobpassword"
 */
class AuthController extends BaseController 
{
    public function __construct()
    {
        $this->rules = [
            'username' => ['required' => true, 'max-length' => '255'],
            'password' => ['required' => true, 'min-length' => '2'],
        ];
        
        parent::__construct();
    }

    public function auth()
    {   
        $this->executeValidation();

        $user = new User();
        if(!$user->authenticate($this->requestData['username'], $this->requestData['password'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Authentication failed']);
        }
        
        $user->generateToken();

        $this->jsonResponse(['success' => true, 'auth-token' => $user->token, 'auth-token-expires-at' => $user->token_expires_at]);
    }
}


