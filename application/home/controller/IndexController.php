<?php

/**
 * Created by PhpStorm.
 * User: hqs <klinson@163.com>
 * Date: 2017/6/3
 * Time: 11:19
 */
namespace app\home\controller;
use think\Url;

class IndexController extends _BaseController
{
    public function index()
    {
        $this->redirect(Url::build('Web/index'));
        return $this->fetch();
    }
}