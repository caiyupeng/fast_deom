<?php

namespace app\wap\controller;

use app\common\model\weixin\Member;
use mikkle\tp_wechat\Wechat;
use think\Controller;
use think\Cookie;
use think\Log;

class Weixinindex extends Controller
{

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 查看
     */
    public function index()
    {
        if ( $this->wechatcheckAuthorize() === false ) {
            $this->wechatOauth();
        }
        $openid     = Cookie::get('wxopenid');
        $nickname   = Cookie::get('nickname');
        $miniavatar = Cookie::get('miniavatar');
        if ( !$openid || !$nickname || !$miniavatar ) {
            //用户数据
            $memberinfo = Member::get([ 'openid' => $openid ]);
            if ( !$memberinfo ) {
                $this->wechatOauth();
            }
            $this->assign("nickname", $memberinfo->nickname);
            $this->assign("miniavatar", 'http://' . $_SERVER['HTTP_HOST'] . $memberinfo->miniavatar);
        } else {
            $this->assign("nickname", $nickname);
            $this->assign("miniavatar", $miniavatar);
        }

        //微信jssdk数据
        $url    = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $jsdata = $this->js_getSignPackage($url);
        $this->assign('jsdata', json_encode($jsdata));

        var_dump($nickname);
        var_dump($miniavatar);
        var_dump($jsdata);
        exit;

        return $this->fetch('index');
    }


    /**
     * @return bool
     * 判断是否已经授权
     */
    public function wechatcheckAuthorize()
    {
        $wxopenid = Cookie::get('wxopenid');
        if ( $wxopenid ) {
            Cookie::set('wxopenid', $wxopenid, 3600 * 24 * 2);
        }

        return $wxopenid ? true : false;
    }


    /**
     * 公众号微信授权跳转
     */
    public function wechatOauth()
    {
        $ouath    = Wechat::oauth();
        $code     = $this->request->get('code');//微信二次跳转的code 去获取 openid
        $url      = 'http://fastadmindemo.com/Wap/Weixinindex/index';
        $infodata = $ouath->getOauthAccessToken($code);
        if ( $infodata ) {

            $userinfo = $ouath->getOauthUserInfo($infodata['access_token'], $infodata['openid']);//获取个人信息
            //判断用户是否存在
            $user_model = new Member();
            $res        = $user_model->where('openid', 'eq', $infodata['openid'])->find();
            $insert     = [];
            if ( $userinfo ) {
                $insert['openid']     = $userinfo['openid'];
                $insert['nickname']   = $userinfo['nickname'];
                $insert['miniavatar'] = $this->userIconSave($userinfo['headimgurl'], $userinfo['openid']);
                $insert['sex']        = $userinfo['sex'];
                if ( $res ) {
                    $insert['id'] = $res['id'];
                    $user_model->save($insert, [ 'id' => $insert['id'] ]);
                } else {
                    $user_model->save($insert);
                }
                Cookie::set('wxopenid', $infodata['openid'], 3600 * 24 * 2);
                Cookie::set('nickname', $userinfo['nickname'], 3600 * 24 * 2);
                Cookie::set('miniavatar', $insert['miniavatar'], 3600 * 24 * 2);
            } else {
                Log::error('获取不到用户信息');
                Log::error($userinfo);
            }
        } else {
            $url = $ouath->getOauthRedirect($url, '2018', 'snsapi_userinfo');//组装成二次跳转的url

            header("location:" . $url);
            exit;
        }
    }

    public function js_getSignPackage($url)
    {
        $infodata = Wechat::script()->getJsSign($url);

        return $infodata;
    }

    public function userIconSave($url, $openid)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $file = curl_exec($ch);
        curl_close($ch);
        $path     = ROOT_PATH . 'public' . DS;
        $path1    = "/uploads/usericon/" . md5($openid) . ".jpg";
        $resource = fopen($path . $path1, 'a');
        fwrite($resource, $file);
        fclose($resource);

        return $path1;
    }
}