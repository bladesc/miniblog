<?php

namespace src\view;

use src\language\Ttranslation;
use src\config\Config;
use src\core\request\request;
use src\session\Session;

class BaseView
{
    protected $config;
    protected $templateName = 'default';
    protected $fileName = 'index';
    protected $request;
    protected $translations;
    protected $session;

    public function __construct(request $request)
    {
        $this->request = $request;
        $this->config = (new Config())->getConfigContainer();
        $this->translations = (new Ttranslation())->getTranslations();
        $this->templateName = $this->config['template']['defaultTemplate'];
        $this->templateName = $this->config['template']['defaultFile'];
        $this->session = new Session();
    }
}