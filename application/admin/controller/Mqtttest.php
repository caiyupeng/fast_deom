<?php

namespace app\admin\controller;

use think\Controller;


/**
 *
 *
 * @icon fa fa-circle-o
 */
class Mqtttest extends Controller
{


    public function index()
    {
        return $this->view->fetch();
    }


}
