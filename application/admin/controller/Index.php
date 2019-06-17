<?php

namespace app\admin\controller;

use app\admin\model\AdminLog;
use app\common\controller\Backend;
use think\Config;
use think\Hook;
use think\Validate;

/**
 * 后台首页
 * @internal
 */
class Index extends Backend
{

    protected $noNeedLogin = ['login'];
    protected $noNeedRight = ['index', 'logout'];
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 后台首页
     */
    public function index()
    {
        //左侧菜单
        list($menulist, $navlist, $fixedmenu, $referermenu) = $this->auth->getSidebar([
            'dashboard' => 'hot',
            'addon'     => ['new', 'red', 'badge'],
            'auth/rule' => __('Menu'),
            'general'   => ['new', 'purple'],
        ], $this->view->site['fixedpage']);
        $action = $this->request->request('action');
        if ($this->request->isPost()) {
            if ($action == 'refreshmenu') {
                $this->success('', null, ['menulist' => $menulist, 'navlist' => $navlist]);
            }
        }
        $this->view->assign('menulist', $menulist);
        $this->view->assign('navlist', $navlist);
        $this->view->assign('fixedmenu', $fixedmenu);
        $this->view->assign('referermenu', $referermenu);
        $this->view->assign('title', __('Home'));
        return $this->view->fetch();
    }

    /**
     * 管理员登录
     */
    public function login()
    {
        $url = $this->request->get('url', 'index/index');
        if ($this->auth->isLogin()) {
            $this->success(__("You've logged in, do not login again"), $url);
        }
        if ($this->request->isPost()) {
            $username = $this->request->post('username');
            $password = $this->request->post('password');
            //加上非对称加密
            $password = $this->chekcpws($password);

            $keeplogin = $this->request->post('keeplogin');
            $token = $this->request->post('__token__');
            $rule = [
                'username'  => 'require|length:3,30',
                'password'  => 'require|length:3,33',
                '__token__' => 'token',
            ];
            $data = [
                'username'  => $username,
                'password'  => $password,
                '__token__' => $token,
            ];
            if (Config::get('fastadmin.login_captcha')) {
                $rule['captcha'] = 'require|captcha';
                $data['captcha'] = $this->request->post('captcha');
            }


            $validate = new Validate($rule, [], ['username' => __('Username'), 'password' => __('Password'), 'captcha' => __('Captcha')]);
            $result = $validate->check($data);

            if (!$result) {
                $this->error($validate->getError(), $url, ['token' => $this->request->token()]);
            }
            AdminLog::setTitle(__('Login'));
            $result = $this->auth->login($username, $password, $keeplogin ? 86400 : 0);
            if ($result === true) {
                Hook::listen("admin_login_after", $this->request);
                $this->success(__('Login successful'), $url, ['url' => $url, 'id' => $this->auth->id, 'username' => $username, 'avatar' => $this->auth->avatar]);
            } else {
                $msg = $this->auth->getError();
                $msg = $msg ? $msg : __('Username or password is incorrect');
                $this->error($msg, $url, ['token' => $this->request->token()]);
            }
        }

        // 根据客户端的cookie,判断是否可以自动登录
        if ($this->auth->autologin()) {
            $this->redirect($url);
        }
        $background = Config::get('fastadmin.login_background');
        $background = stripos($background, 'http') === 0 ? $background : config('site.cdnurl') . $background;
        $this->view->assign('background', $background);
        $this->view->assign('title', __('Login'));
        Hook::listen("admin_login_init", $this->request);
        return $this->view->fetch();
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        $this->auth->logout();
        Hook::listen("admin_logout_after", $this->request);
        $this->success(__('Logout successful'), 'index/login');
    }


    public function chekcpws($password){
        //私钥
        $private_key = "-----BEGIN RSA PRIVATE KEY-----
MIICXgIBAAKBgQDXpZ39hjXafD42qq1Np/rG03mlURyo/gyWnLJAXSxSKUtndbNK
rqQYdzovReQdsCB1f5/yQ7kcl30J2pNDdBO5EG0+JjynZJFFSzKlFmHJ7av1NZVt
2cAnfQnUnWLi7PfR2XG0279SBiee+gb+FJMtrGXa1NUMoe3cLRqBXY3byQIDAQAB
AoGBAIGv2zzdmsODlpKfwEuEax9pjK2sAxVqez2UjOqCXiYnKW7V7PZL4unHwhkt
6gskodCn6RP0QH3+aLclWQzm4Ph/UhWeN1HPdxTW4b0joe9/2lnONoi4l5bxs6gQ
KLxB44vr1ejo/2BkwtHQniRE/vEFLhTEvjGQq6zCGQcVXgEBAkEA7ozMoR9i8rkY
n+GWUKygvngOwVKMuyHTVQ+GHH/Q9ZxZlrU7kFMVBbjeXY6SNrVHfpbm80SBZrkq
ct2HiHB9qQJBAOdr7J+xjilWf/3MA5JrjhjXH5BWmyhfVxPBAaiWct2oFvv721Xn
hf0bCyEQ7PklUKfpyqm31qs1pXJCKhwQASECQHlMBtD20K1zCN5jKreiz6mKCpaq
jvyoWnkqB5t+MpZxBezoAn2EgXADbK5NzHMdAlmQCacw8kt1Y+w8UKpD6OECQQDU
1zLasN+R72dqEd/bI6ad/ASgqLatC/q3RVT0K+LbMARrnvjcakKWRfAXaky43HPw
6xoku9rovj869dVq1+FhAkEA4cOf6zI1jBajvhhKzfTC+/ib2xmOFYgEp23K2a01
dXbZJOJEo/ckejPWC609QTmOC+rexRXV5JrX9gN6gsER1w==
-----END RSA PRIVATE KEY-----";

        $hex_encrypt_data = trim($password); //十六进制数据
        $encrypt_data = pack("H*", $hex_encrypt_data);//对十六进制数据进行转换
        openssl_private_decrypt($encrypt_data, $decrypt_data, $private_key);

        return $decrypt_data;
    }

}
