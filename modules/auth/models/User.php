<?php
/**
 * User Model - Authentication Module
 */

class User extends BaseModel {
    protected $table = 'users';
    protected $fillable = ['email', 'password', 'first_name', 'last_name', 'role', 'status'];
    protected $hidden = ['password'];

    /**
     * Find user by email
     */
    public function findByEmail($email) {
        return $this->first('email', $email);
    }

    /**
     * Authenticate user
     */
    public function authenticate($email, $password) {
        $user = $this->findByEmail($email);

        if (!$user) {
            return false;
        }

        $auth = new Auth();
        if (!$auth->verifyPassword($password, $user['password'])) {
            return false;
        }

        return $this->hideFields($user);
    }

    /**
     * Create new user with hashed password
     */
    public function registerUser($data) {
        $auth = new Auth();
        $data['password'] = $auth->hashPassword($data['password']);
        return $this->create($data);
    }
}
?>
