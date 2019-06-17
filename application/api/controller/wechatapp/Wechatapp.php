<?php

namespace app\api\controller\wechatapp;

use app\common\controller\Api;
use app\common\model\wechatapp\Config;
use app\common\model\wechatapp\Formidlist;
use app\common\model\wechatapp\User;
use think\Cache;
use think\Exception;

class Wechatapp extends Api
{
    //如果$noNeedLogin为空表示所有接口都需要登录才能请求
    //如果$noNeedRight为空表示所有接口都需要验证权限才能请求
    //如果接口已经设置无需登录,那也就无需鉴权了
    //
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = [ 'login', 'saveuserinfo' ];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = [ '*' ];
    // 无需登录的接口,*表示全部
    protected $noNeedAuth = [ 'login' ];


    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 接收小程序code换取openid
     * 前端接收200为成功，8为失败需要重新拉起授权
     * method :post
     * params :code
     */
    public function login()
    {
        try {
            //参数获取
            $code = $this->request->post('code', '');

            if ( !$code ) {
                throw new Exception('参数缺失', 8);
            }

            //获取小程序配置
            $configModel = Config::get([ 'id' => 1 ], [], 60);
            $APPID       = $configModel['appid'];
            $AppSecret   = $configModel['appsecret'];

            //用小程序配置信息和code换取解密session_key
            $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $APPID . '&secret=' . $AppSecret . '&js_code=' . $code . '&grant_type=authorization_code';
            $arr = $this->vegt($url);
            $arr = json_decode($arr, true);
            if ( !isset($arr['session_key']) || !isset($arr['openid']) ) {
                throw new Exception('认证失败,或code失效', 8);
            }
            $session_key = $arr['session_key'];
            $openid      = $arr['openid'];

            //获取用户信息
            $wechatUserModel = User::get([ 'openid' => $openid ]);
            if ( !$wechatUserModel ) {
                //返回，让前端进行getUserinfo获取用户进行登记
                $token                        = $this->token;
                $tokenUserinfo                = $this->tokenUserinfo;
                $tokenUserinfo['openid']      = $openid;
                $tokenUserinfo['session_key'] = $session_key;
                $this->setCacheByToken($token, $tokenUserinfo);

                throw new Exception('暂无用户记录，请获取用户信息', 5);
            }

            //如果超过一星期没更新，也进行用户信息再拉取
            $updatetime = $wechatUserModel->getAttr('updatetime');
            if ( time() > $updatetime + 7 * 86400 ) {
                //返回，让前端进行getUserinfo获取用户进行登记
                $token                        = $this->token;
                $tokenUserinfo                = $this->tokenUserinfo;
                $tokenUserinfo['openid']      = $openid;
                $tokenUserinfo['session_key'] = $session_key;
                $this->setCacheByToken($token, $tokenUserinfo);

                throw new Exception('请更新用户信息', 5);
            }

            //储存用户信息到token
            $token                   = $this->token;
            $tokenUserinfo           = $this->tokenUserinfo;
            $tokenUserinfo['openid'] = $openid;
//            $tokenUserinfo['session_key'] = $session_key;

            //储存用户微信凭证
            $this->setCacheByToken($token, $tokenUserinfo);

            //用户信息
            //获取微信信息
            $wechatUserData = $wechatUserModel->visible([
                'nickname',
                'sex',
                'province',
                'city',
                'country',
                'headimgurl'
            ])->append([], true);
            $wechatUserData = $wechatUserData->toArray();

            $this->success('授权登录成功', [
                'wechatinfo' => $wechatUserData,
            ], 10);
        } catch ( Exception $e ) {
            $error_msg  = $e->getMessage() ? $e->getMessage() : '服务端错误';
            $error_code = $e->getCode() ? $e->getCode() : 8;
            $this->error($error_msg, null, $error_code);
        }
    }

    /**
     * 请求方法
     *
     * @param $url
     *
     * @return mixed
     */
    private function vegt($url)
    {
        $info = curl_init();
        curl_setopt($info, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($info, CURLOPT_HEADER, 0);
        curl_setopt($info, CURLOPT_NOBODY, 0);
        curl_setopt($info, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($info, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($info, CURLOPT_URL, $url);
        $output = curl_exec($info);
        curl_close($info);

        return $output;
    }

    /**
     * 接收小程序用户数据进行解密记录入库
     * method :post
     * params :signature
     * params :encryptedData
     * params :iv
     * params :rawData
     */
    public function saveUserinfo()
    {
        try {
            //参数获取
            $signature     = $this->request->post('signature', '');
            $encryptedData = $this->request->post('encryptedData', '');
            $iv            = $this->request->post('iv', '');
            $rawData       = $this->request->post('rawData/a', '');
            if ( !$signature || !$encryptedData || !$iv || !$rawData ) {
                throw new Exception('前端参数缺失', 500);
            }
            //获取公众号配置
            $configModel = Config::get([ 'id' => 1 ], [], 60);
            $APPID       = $configModel['appid'];
            $AppSecret   = $configModel['appsecret'];
            //获取解密的session_key
            $tokenUserinfo = $this->tokenUserinfo;

            if ( !isset($tokenUserinfo['session_key']) || empty($tokenUserinfo['session_key']) ) {
                throw new Exception('解密key丢失，请重新授权', 4);
            }
            $session_key = $tokenUserinfo['session_key'];
            //获取openid
            if ( !isset($tokenUserinfo['openid']) || empty($tokenUserinfo['openid']) ) {
                throw new Exception('openid丢失，请重新授权', 4);
            }
            $openid = $tokenUserinfo['openid'];
            // 加载微信解密类，利用session_key 对 前端加密数据进行解密 并记录入库
            Vendor("wxBizDataCrypt.wxBizDataCrypt");
            $dataCryptClass = new \WXBizDataCrypt($APPID, $session_key);
            $errCode        = $dataCryptClass->decryptData($encryptedData, $iv, $data);//自引用
            if ( $errCode != 0 ) {
                throw new Exception('获取用户信息失败,错误代码:' . $errCode . '，openid：' . $openid, 9);
            }
            //对用户信息进行处理
            $data          = json_decode($data, true);
            $map['openid'] = $data['openId'];

            $save['openid']     = $data['openId'];
            $save['nickname']   = $data['nickName'];
            $save['sex']        = $data['gender'];
            $save['province']   = $data['province'];
            $save['city']       = $data['city'];
            $save['country']    = $data['country'];
            $save['headimgurl'] = $data['avatarUrl'];
            $save['updatetime'] = time();
            //新增或更新用户信息
            $wechatUserModel = User::get($map);
            if ( !$wechatUserModel ) {
                $wechatUserModel = new User();
            }
            if ( $wechatUserModel->save($save) === false ) {
                throw new Exception('用户信息保存失败' . $wechatUserModel->getError(), 8);
            }

            //重新获取模型数据
            $id              = $wechatUserModel->getAttr('id');
            $wechatUserModel = User::get($id);

            $token                   = $this->token;
            $tokenUserinfo           = $this->tokenUserinfo;
            $tokenUserinfo['openid'] = $data['openId'];
            //储存用户微信凭证
            $this->setCacheByToken($token, $tokenUserinfo);
            //用户信息

            //获取微信信息
            $wechatUserData = $wechatUserModel->visible([
                'nickname',
                'sex',
                'province',
                'city',
                'country',
                'headimgurl'
            ]);
            $wechatUserData = $wechatUserData->toArray();

            $this->success('授权登录成功', [
                'wechatinfo' => $wechatUserData,
            ], 11);

        } catch ( Exception $e ) {
            $error_msg  = $e->getMessage() ? $e->getMessage() : '服务端错误';
            $error_code = $e->getCode() ? $e->getCode() : 8;
            $this->error($error_msg, null, $error_code);
        }
    }


    /**
     * User: caiyupeng
     * Date: 2018/11/23 4:05 PM
     * 存入表单id
     */
    public function setFormId()
    {
        $id     = $this->request->request('id');
        $openid = $this->openid;
        if ( empty($id) ) {
            $this->success('请传入formid',[],200);
        }
        if ( !$openid ) {
            $this->success('请传入openid',[],200);
        }
        //判断是否是数字
        $Formidlist         = new Formidlist();
        $Formidlist->formid = $id;
        $Formidlist->openid = $openid;
        $res                = $Formidlist->save();
        if ( $res !== false ) {
            $this->success('成功',[],200);
        } else {
            $this->success('失败',[],200);
        }
    }

}
