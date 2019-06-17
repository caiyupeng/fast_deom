<?php

namespace app\common\controller;

use app\common\library\Auth;
use app\common\model\wechatapp\User;
use helper\RedisHelper;
use think\Cache;
use think\Config;
use think\Exception;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Hook;
use think\Lang;
use think\Loader;
use think\Request;
use think\Response;

/**
 * API控制器基类
 */
class Wechatappapi
{

    /**
     * @var Request Request 实例
     */
    protected $request;

    /**
     * @var bool 验证失败是否抛出异常
     */
    protected $failException = false;

    /**
     * @var bool 是否批量验证
     */
    protected $batchValidate = false;

    /**
     * @var array 前置操作方法列表
     */
    protected $beforeActionList = [];

    /**
     * 无需登录的方法,同时也就不需要鉴权了
     *
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 无需鉴权的方法,但需要登录
     *
     * @var array
     */
    protected $noNeedRight = [];

    /**
     * 无需授权的方法
     *
     * @var array
     */
    protected $noNeedAuth = [];

    /**
     * 无需ajax调用的方法,否则一定是ajax调用，直接调用报错，数组成员要为小写
     *
     * @var array
     */
    protected $noNeedAjax = [];

    /**
     * 权限Auth
     *
     * @var Auth
     */
    protected $auth = null;

    /**
     * 默认响应输出类型,支持json/xml
     *
     * @var string
     */
    protected $responseType = 'json';

    //是否发送token，在每次生成或更新时发送
    protected $is_sendtoken = false;

    //token
    protected $token = null;

    //origin token
    protected $origin_token = null;

    //decrypt_token token
    protected $decrypt_token = null;

    //token用户信息
    protected $tokenUserinfo = null;

    //用户openid
    protected $openid = null;

    //用户身份
    protected $userType = 1;//默认游客

    //用户ID
    protected $userId = null;

    /**
     * 构造方法
     *
     * @access public
     *
     * @param Request $request Request 对象
     */
    public function __construct(Request $request = null)
    {
        $this->request = is_null($request) ? Request::instance() : $request;

        // 控制器初始化
        $this->_initialize();

        // 前置操作方法
        if ( $this->beforeActionList ) {
            foreach ( $this->beforeActionList as $method => $options ) {
                is_numeric($method) ?
                    $this->beforeAction($options) :
                    $this->beforeAction($method, $options);
            }
        }
    }

    /**
     * 初始化操作
     *
     * @access protected
     */
    protected function _initialize()
    {
        $error_data = [];

        try {
            $modulename     = $this->request->module();
            $controllername = strtolower($this->request->controller());
            $actionname     = strtolower($this->request->action());

            $path = str_replace('.', '/', $controllername) . '/' . $actionname;
//
//            $this->noNeedAjax = [ 'addwords' ,'seachall'];
//            $this->noNeedAuth = [ 'addwords' ,'seachall'];
//            $this->openid     = 'o5TQ75MbxFrzjzyynyVae8AcPr7w';


//            //签名校验
            if ( !in_array(strtolower($actionname), $this->noNeedAjax) ) {
                $post = $this->request->post();
                $this->signatureValidation($post);
            }

            //移除HTML标签
            $this->request->filter('strip_tags');

            //设置当前token
            $this->gettoken();

            //需要授权登录的
            if ( !in_array(strtolower($actionname), $this->noNeedAuth) ) {
                if ( !isset($this->tokenUserinfo['openid']) || empty($this->tokenUserinfo['openid']) ) {
                    throw new Exception('授权信息过期,请重新微信授权', 4);
                }

                $this->openid = $this->tokenUserinfo['openid'];

                //需要登录
                if ( !in_array(strtolower($actionname), $this->noNeedLogin) ) {
                    //检查用户状态
//                    $this->checkLogin($this->tokenUserinfo['openid']);
                }
            }

            $upload = \app\common\model\Config::upload();

            // 上传信息配置后
            Hook::listen("upload_config_init", $upload);

            Config::set('upload', array_merge(Config::get('upload'), $upload));

            // 加载当前控制器语言包
            $this->loadlang($controllername);
        } catch ( Exception $e ) {
            $message = $e->getMessage() ? $e->getMessage() : '服务端错误';
            $code    = $e->getCode() ? $e->getCode() : 500;

            $this->error($message, $error_data, $code);
        }
    }

    /**
     * 检查用户状态
     */
    protected function checkLogin($openid)
    {
        //缓存60秒
//        $user = User::get([ 'openid' => $openid ], [], 60);
//        if ( !$user ) {
//            throw new Exception('用户不存在,请重新微信授权', 4);
//        }
    }

    /**
     * 签名校验
     */
    protected function signatureValidation(array $data)
    {

        $timestamp = time();

        if ( $this->request->request('debug') == 1 ) {
            return true;
        }
        if ( empty($data['sign']) ) {
            throw new Exception('缺少参数sign!', 400);
        }

        if ( empty($data['timestamp']) ) {
            throw new Exception('缺少参数timestamp!', 400);
        }

        if ( $timestamp - $data['timestamp'] > 300 ) {
            throw new Exception('请求超时!', 400);
        }
        ksort($data);
        $encryptStr = '';
        foreach ( $data as $key => $value ) {
            if ( $key == 'sign' ) {
                continue;
            }
            //JSON_UNESCAPED_SLASHES 对斜杠不转义
            //JSON_NUMERIC_CHECK 数字不转字符串
            //JSON_UNESCAPED_UNICODE 中文不转编码
            //这一切一切都是为了和前端的json化契合,md,还不如用数组遍历拼接算了

            $encryptStr .= json_encode($value, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
        }
        $encryptStr = str_replace('"', '', $encryptStr);//去除字符串中的所有"
        $encryptStr = str_replace('\\', '', $encryptStr);//去除字符串中的所有"
        $str        = strtotime(date("Y-m-d", time()));
        $str        = $str + intval(date("Y", time())) ^ 2;

        $str1 = base_convert($str, 10, 32) . 'by lesamly@2019';

        $accessKey = md5($str1);
        $sign      = md5($encryptStr . $accessKey);
        if ( $data['sign'] != $sign ) {

            if ( Config::get('app_debug') ) {

                $this->error('接口鉴权失败', [
                    'encryptStr'             => $encryptStr,
                    'accessKey'              => $accessKey,
                    'encryptStr + accessKey' => $encryptStr . $accessKey,
                    'sign'                   => $sign,
                    'data'                   => $this->request->post(),
                ], 400);
            }

            throw new Exception('接口鉴权失败', 400);
        }

        return true;
    }

    /**
     * @return string
     * 获取生成token
     */
    public function gettoken()
    {
        //获取header token信息
        $decrypt = true;
        $token   = $this->request->header('tk', $this->request->get('tk'));//这里的token是经过前端加密的
        $debug   = $this->request->param('debug', false);//debug参数和验签那个一样

        $this->origin_token = $token;
        //如果需要解密
        if ( $decrypt && !$debug ) {
            //进行获取过来的token解密
            //加密：Base64.encode(md5(精确到分的时间戳) + 真实token)
            //解密：Base64.decode(加密字符串).replace(md5(精确到分的时间戳), '') = 真实 token
            $tokensrt = base64_decode($token);

            $timestamp1 = strtotime(date("Y-m-d H:i", time()));
            $timestamp2 = strtotime('-1 Minute', $timestamp1);

            $timestampmd5_1 = md5($timestamp1);
            $timestampmd5_2 = md5($timestamp2);

            $strstr1 = strpos($tokensrt, $timestampmd5_1);
            $strstr2 = strpos($tokensrt, $timestampmd5_2);
            if ( $strstr1 !== false ) {
                $token = str_replace($timestampmd5_1, "", $tokensrt);
            } else {
                if ( $strstr2 !== false ) {
                    $token = str_replace($timestampmd5_2, "", $tokensrt);
                } else {
                    $token = false;
                }
            }
        }

        //到这里的都是没加密的token
        $this->decrypt_token = $token;
        if ( $token ) {
            $userInfo = $this->getCacheByToken($token);
            if ( $userInfo ) {
                $this->token         = $token;
                $this->tokenUserinfo = $userInfo;
                //续命
                $this->setCacheByToken($token, $userInfo);

                return true;
            }
        }

        //token或者userinfo没有则新建
        $token    = $this->setToken();
        $userInfo = [
            'createtime' => time()
        ];

        $this->token         = $token;
        $token               = $this->getguidtoken($token);
        $this->tokenUserinfo = $userInfo;
        $this->setCacheByToken($token, $userInfo);
    }

    /**
     * User: little_cai
     * Date: times
     *
     * @param $token
     * 根据guid保证并发时tk唯一性
     *
     * @return mixed
     */
    protected function getguidtoken($token)
    {
        //根据guid保证并发时tk唯一性
        $guid = $this->request->header('guid', '');
        if ( $guid ) {
            $redis = RedisHelper::getInstance();
            if ( $redis->setnx($guid, $token) === false ) {
                $token = $redis->get($guid);
            }
            $redis->expire($guid, 5);
        }

        return $token;
    }

    /**
     * 设置token
     */
    public function setToken()
    {
        $str                = md5(microtime(true), true);  //生成一个不会重复的字符串
        $str                = sha1($str);  //加密
        $this->is_sendtoken = true;

        return $str;
    }

    /**
     * 根据token设置缓存
     */
    public function setCacheByToken($token, $data, $expire = 36000)
    {
        return Cache::set($token, $data, $expire);
    }

    /**
     * 根据token取缓存
     */
    public function getCacheByToken($token)
    {
        return Cache::get($token);
    }

    /**
     * 加载语言文件
     *
     * @param string $name
     */
    protected function loadlang($name)
    {
        Lang::load(APP_PATH . $this->request->module() . '/lang/' . $this->request->langset() . '/' . str_replace('.',
                '/', $name) . '.php');
    }

    /**
     * 操作成功返回的数据
     *
     * @param string $msg 提示信息
     * @param mixed  $data 要返回的数据
     * @param int    $code 错误码，默认为1
     * @param string $type 输出类型
     * @param array  $header 发送的 Header 信息
     */
    protected function success($msg = '', $data = null, $code = 1, $type = null, array $header = [])
    {
//        if ( $data === null ) {
//            $data             = [];
//            $data['submitid'] = 1;
//        }
        $this->result($msg, $data, $code, $type, $header);
    }

    /**
     * 操作失败返回的数据
     *
     * @param string $msg 提示信息
     * @param mixed  $data 要返回的数据
     * @param int    $code 错误码，默认为0
     * @param string $type 输出类型
     * @param array  $header 发送的 Header 信息
     */
    protected function error($msg = '', $data = null, $code = 0, $type = null, array $header = [])
    {
        $this->result($msg, $data, $code, $type, $header);
    }

    /**
     * 返回封装后的 API 数据到客户端
     *
     * @access protected
     *
     * @param mixed  $msg 提示信息
     * @param mixed  $data 要返回的数据
     * @param int    $code 错误码，默认为0
     * @param string $type 输出类型，支持json/xml/jsonp
     * @param array  $header 发送的 Header 信息
     *
     * @return void
     * @throws HttpResponseException
     */
    protected function result($msg, $data = null, $code = 0, $type = null, array $header = [])
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
//            'timestamp' => Request::instance()->server('REQUEST_TIME'),
            'data' => $data,
        ];

        //附带TOKEN
        if ( $this->is_sendtoken === true ) {
            $result['timestamp']     = Request::instance()->server('REQUEST_TIME');
            $result['tk']            = $this->token;
            $result['origin_tk']     = $this->origin_token;
            $result['decrypt_token'] = $this->decrypt_token;
        }

        // 如果未设置类型则自动判断
        $type = $type ? $type : ($this->request->param(config('var_jsonp_handler')) ? 'jsonp' : $this->responseType);

        if ( isset($header['statuscode']) ) {
            $code = $header['statuscode'];
            unset($header['statuscode']);
        } else {
            //未设置状态码,根据code值判断
            $code = $code >= 1000 || $code < 200 ? 200 : $code;
        }

        //这里写死200，不给改http状态码
        $response = Response::create($result, $type, 200)->header($header);
        throw new HttpResponseException($response);
    }

    /**
     * 前置操作
     *
     * @access protected
     *
     * @param  string $method 前置操作方法名
     * @param  array  $options 调用参数 ['only'=>[...]] 或者 ['except'=>[...]]
     *
     * @return void
     */
    protected function beforeAction($method, $options = [])
    {
        if ( isset($options['only']) ) {
            if ( is_string($options['only']) ) {
                $options['only'] = explode(',', $options['only']);
            }

            if ( !in_array($this->request->action(), $options['only']) ) {
                return;
            }
        } elseif ( isset($options['except']) ) {
            if ( is_string($options['except']) ) {
                $options['except'] = explode(',', $options['except']);
            }

            if ( in_array($this->request->action(), $options['except']) ) {
                return;
            }
        }

        call_user_func([ $this, $method ]);
    }

    /**
     * 设置验证失败后是否抛出异常
     *
     * @access protected
     *
     * @param bool $fail 是否抛出异常
     *
     * @return $this
     */
    protected function validateFailException($fail = true)
    {
        $this->failException = $fail;

        return $this;
    }

    /**
     * 验证数据
     *
     * @access protected
     *
     * @param  array        $data 数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message 提示信息
     * @param  bool         $batch 是否批量验证
     * @param  mixed        $callback 回调方法（闭包）
     *
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate($data, $validate, $message = [], $batch = false, $callback = null)
    {
        if ( is_array($validate) ) {
            $v = Loader::validate();
            $v->rule($validate);
        } else {
            // 支持场景
            if ( strpos($validate, '.') ) {
                list($validate, $scene) = explode('.', $validate);
            }

            $v = Loader::validate($validate);

            !empty($scene) && $v->scene($scene);
        }

        // 批量验证
        if ( $batch || $this->batchValidate ) {
            $v->batch(true);
        }
        // 设置错误信息
        if ( is_array($message) ) {
            $v->message($message);
        }
        // 使用回调验证
        if ( $callback && is_callable($callback) ) {
            call_user_func_array($callback, [ $v, &$data ]);
        }

        if ( !$v->check($data) ) {
            if ( $this->failException ) {
                throw new ValidateException($v->getError());
            }

            return $v->getError();
        }

        return true;
    }

}
