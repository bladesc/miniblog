<?php


namespace src\controller;


use src\core\general\Communicate;
use src\core\redirect\Redirect;
use src\model\CommonModel;
use src\model\LoginModel;
use src\view\View;

class LoginController extends CommonController
{
    public function login()
    {
        $data = (new LoginModel($this->request))->getData();
        (new View($this->request))->data($data)->template('default')->file('login')->render();
    }

    public function processLogin()
    {
        $model = (new LoginModel($this->request));
        $data = $model->loginUser()->getData();
        if ($data[CommonModel::ACTION_LOGGED]) {
            $this->session->change(Communicate::C_POSITIVE, 'Zalogowano pomyslnie');
            Redirect::redirectTo('index.php?page=index');
        }
        (new View($this->request))->data($data)->template('default')->file('login')->render();
    }
}