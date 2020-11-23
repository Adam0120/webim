<?php
declare (strict_types = 1);

namespace app\index\controller;

use app\lib\controller\MyBaseController;

class Index extends MyBaseController
{
    public function index()
    {
        return $this->redirect('/laychat');
    }
    public function share()
    {
        return view();
    }
}
