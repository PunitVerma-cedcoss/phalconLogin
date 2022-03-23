<?php

use Phalcon\Mvc\Controller;
use Phalcon\Http\Request;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Http\Response;

class AuthController extends Controller
{
    public function indexAction()
    {
        // if session is not set
        if (!$this->session->get("user")) {
            // if cookies are set then login through cookies and set session
            if ($this->cookies->get('remember-me')->getValue()) {
                // check in the database
                $user = new Users();
                $dbData = ($user::findFirst([
                    "conditions" => "email = :email:",
                    "bind" => [
                        "email" => $this->cookies->get('remember-me')->getValue(),
                    ]
                ]));
                if ($dbData) {
                    $this->session->set("user", $this->cookies->get('remember-me')->getValue());
                }
            }
        }
        if ($this->session->get("user")) {
            header("location:/index");
        }
        $validation = new Validation();
        $request = new Request();
        // if got post
        if ($request->ispost()) {
            // adding validation for email
            $validation->add(
                'email',
                new Email()
            );
            // adding validation for password
            $validation->add(
                'password',
                new PresenceOf(
                    [
                        'length' => 5,
                        'message' => 'The password is required',
                    ]
                )
            );
            // checking if password is less then 5
            $validation->add(
                'password',
                new StringLength(
                    [
                        'min' => 5,
                        'message' => 'password must be longer',
                    ]
                )
            );
            // fire validation 😻
            $messages = $validation->validate($request->getPost());
            // if validation has errors
            if (count($messages)) {
                $errors = [];
                // if there are errors
                foreach ($messages as $message) {
                    $d = json_decode(json_encode($message, false));
                    $errors[$d->field] = $d->message;
                }
                // send errors into the view
                $this->view->errors = $errors;
            } else {
                // now check for uer login details
                $user = new Users();
                $formData = $this->request->getPost();
                $dbData = ($user::findFirst([
                    "conditions" => "email = :email: AND password = :password:",
                    "bind" => [
                        "email" => $formData["email"],
                        "password" => $formData["password"],
                    ]
                ]));
                // checking for email and password 🔏
                if ($dbData) {
                    echo "creds ok";
                    // setting the session 🔗
                    $this->session->set('user', $dbData->email);
                    if (isset($formData["remember"])) {
                        // setting the cookies 🍪
                        $this->cookies->set(
                            'remember-me',
                            $dbData->email,
                            time() + 15 * 86400
                        );
                        $this->cookies->send();
                    } else {
                        echo "remember me off";
                    }
                    header("location:/index");
                } else {
                    $response = new Response();
                    $response->setStatusCode(403, 'Error');
                    $response->setContent("Hey, the login details doesn't exist");
                    $response->send();
                    // echo "incorrect creds";
                    $this->view->message = "403 email/password is incorrect";
                }
                // die();
            }
        }
    }
    public function logoutAction()
    {
        // remove session 😎
        $this->session->destroy();
        // remove cookies 🍪
        $this->cookies->get("remember-me")->delete();
        header("location:/auth");
    }
}
