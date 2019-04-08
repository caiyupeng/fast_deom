<?php
/**
 * @desc: 企业微信
 * @date: 2018-8-1 下午3:34:43
 * @author: stonechen@gdtengnan.com
 */

namespace mikkle\tp_wechat\src;

//use mikkle\tp_wechat\base\WechatQyBase;
use app\common\model\QyweixinAgent;
use app\common\model\QyweixinConfig;
use mikkle\tp_master\Config;
use mikkle\tp_wechat\support\Curl;
use mikkle\tp_master\Log;
use mikkle\tp_wechat\support\StaticFunction;
use think\Cache;

class Qy
{
    const API_BASE_URL_PREFIX = 'https://qyapi.weixin.qq.com/';
    const GET_TOKEN_URL = 'cgi-bin/gettoken?';
    const SEND_MESSAGE_URL = 'cgi-bin/message/send?';
    const CONVERT_OPENID_URL = 'cgi-bin/user/convert_to_openid?';
    const OAUTH_PREFIX = 'https://open.weixin.qq.com/connect/oauth2';
    const OAUTH_AUTHORIZE_URL = '/authorize?';
    const OAUTH_TOKEN_URL = '/sns/oauth2/access_token?';
    const OAUTH_REFRESH_URL = '/sns/oauth2/refresh_token?';
    const OAUTH_USERINFO_URL = '/sns/userinfo?';
    const OAUTH_AUTH_URL = '/sns/auth?';
    const DEPARTMENT_ADD_URL = 'cgi-bin/department/create?';
    const DEPARTMENT_UPDATE_URL = 'cgi-bin/department/update?';
    const DEPARTMENT_DEL_URL = 'cgi-bin/department/delete?';
    const DEPARTMENT_URL = 'cgi-bin/department/list?';
    const DEPARTMENT_USER_URL = 'cgi-bin/user/simplelist?';
    const DEPARTMENT_USER_DETAIL_URL = 'cgi-bin/user/list?';
    const USER_LIST_URL = 'cgi-bin/user/get?';
    const USER_ADD_URL = 'cgi-bin/user/create?';
    const USER_UPDATE_URL = 'cgi-bin/user/update?';
    const USER_DEL_URL = 'cgi-bin/user/delete?';
    const USER_BATCH_DEL_URL = 'cgi-bin/user/batchdelete?';
    const USER_CONVERT_TO_USERID_URL = 'cgi-bin/user/convert_to_userid?';
    const INVITE_USER_URL = 'cgi-bin/batch/invite?';
    const GET_USER_INFO_URL = 'cgi-bin/user/getuserinfo?';
    const GET_USER_DETAIL_URL = 'cgi-bin/user/getuserdetail?';
    const TAG_LIST_URL = 'cgi-bin/tag/list?';
    const TAG_USER_URL = 'cgi-bin/tag/get?';
    const TAG_ADD_URL = 'cgi-bin/tag/create?';
    const TAG_UPDATE_URL = 'cgi-bin/tag/update?';
    const TAG_DEL_URL = 'cgi-bin/tag/delete?';
    const TAG_ADD_USER_URL = 'cgi-bin/tag/addtagusers?';
    const TAG_DEL_USER_URL = 'cgi-bin/tag/deltagusers?';
    const GET_TICKET_URL = 'cgi-bin/get_jsapi_ticket?';

    public $access_token;
    private $corpid;
    private $corpsecret;
    public $jsapi_ticket;

    public function __construct($options = [])
    {
        //获取企业号配置
        $qycinfig = $this->getconfigcorp();
        if ( $qycinfig ) {
            $this->corpid     = isset($options['corpid']) ? $options['corpid'] : $qycinfig['corpid'];
            $this->corpsecret = isset($options['corpsecret']) ? $options['corpsecret'] : $qycinfig['corpsecret'];
        } else {
            return false;
        }

    }

    /**
     * Oauth 授权跳转接口
     *
     * @param string $callback 授权回跳地址
     * @param string $state 为重定向后会带上state参数（填写a-zA-Z0-9的参数值，最多128字节）
     * @param string $scope 授权类类型(可选值snsapi_base|snsapi_userinfo)
     *
     * @return string
     */
    public function getOauthRedirect($redirect_uri, $state = '', $scope = 'snsapi_base', $agentid = '')
    {
        ////$redirect_uri = urlencode($callback);
        return self::OAUTH_PREFIX . self::OAUTH_AUTHORIZE_URL . "appid={$this->corpid}&redirect_uri={$redirect_uri}&response_type=code&scope={$scope}&state={$state}&agentid={$agentid}#wechat_redirect";
    }

    /**
     * @desc: 获取access_token
     * @date: 2018-3-21 上午11:46:56
     * @author: stonechen@gdtengnan.com
     *
     * @param: variable
     *
     * @return:
     */
    public function getQyAccessToken($corpid = '', $corpsecret = '', $agentid = '')
    {

        if ( !$corpid ) {
            $corpid = $this->corpid;
        }
        if ( !$corpsecret ) {
            $corpsecret = $this->corpsecret;
        }

        //缓存
//        $cache = 'qywechat_access_token_' . $corpid;
//        if (($access_token = Tools::getCache($cache)) && !empty($access_token)) {
//        return $this->access_token = $access_token;
//        }
//        # 检测事件注册
//        if (isset(Loader::$callback[__FUNCTION__])) {
//        return $this->access_token = call_user_func_array(Loader::$callback[__FUNCTION__], array(&$this, &$cache));
//        }
        $cache        = 'qywechat_access_token_' . $corpsecret;
        $access_token = Cache::get($cache);

        if ( $access_token ) {
            $this->access_token = $access_token;
            return $this->access_token;
        } else {
            $result = Curl::curlGet(self::API_BASE_URL_PREFIX . self::GET_TOKEN_URL . 'corpid=' . $corpid . '&corpsecret=' . $corpsecret);
            if ( $result ) {
                $json = json_decode($result, true);
                if ( !$json || intval($json['errcode']) != 0 ) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg  = $json['errmsg'];
                    Log::error("Get New AccessToken Error. {$this->errMsg}[{$this->errCode}]");

                    return false;
                }
                $this->access_token = $json['access_token'];
                Log::info("Get New AccessToken Success.]");
                //Tools::setCache($cache, $this->access_token, 5000);

                //存入数据库
                $qygent              = QyweixinAgent::get([ 'secret' => $this->corpsecret ]);
                $qygent->accesstoken = $this->access_token;
                $qygent->save();
                //存进去缓存
                Cache::set($cache, $this->access_token, 7000);

                return $this->access_token;
            }

        }


        return false;
    }

    /**
     * @desc: userid转openid
     * 该接口使用场景为微信支付、微信红包和企业转账。
     * 在使用微信支付的功能时，需要自行将企业微信的userid转成openid。
     * 在使用微信红包功能时，需要将应用id和userid转成appid和openid才能使用。
     * 注：需要成员使用微信登录企业微信或者关注微信插件才能转成openid
     * @author: stonechen@gdtengnan.com
     *
     * @param: variable
     *
     * @return:
     */
    public function getOpenIdWithUserId($data)
    {
        if ( !$this->access_token && !$this->getQyAccessToken() ) {
            return false;
        }
        $result = Curl::curlPost(self::API_BASE_URL_PREFIX . self::CONVERT_OPENID_URL . 'access_token=' . $this->access_token,
            $data);

        return $result;
    }

    /**
     * @desc: 通过code获取用户信息 授权时用
     * @author: stonechen@gdtengnan.com
     *
     * @param: variable
     *
     * @return:
     */
    public function getUserInfo($code = '', $corpid = '', $corpsecret = '', $agentid = '')
    {
        $token  = $this->getQyAccessToken($corpid, $corpsecret, $agentid);
        $url    = self::API_BASE_URL_PREFIX . self::GET_USER_INFO_URL . 'access_token=' . $token . '&code=' . $code;
        $result = Curl::curlGet($url);

        return $result;
    }

    /**
     * @desc: 通过user_ticket获取成员详情
     * @author: stonechen@gdtengnan.com
     *
     * @param: variable
     *
     * @return:
     */
    public function getUserDetailByUserTicket($data)
    {
        $token  = $this->getQyAccessToken();
        $url    = self::API_BASE_URL_PREFIX . self::GET_USER_DETAIL_URL . 'access_token=' . $token;
        $result = Curl::curlPost($url, $data);

        return $result;
    }

    /**
     * @desc: 读取成员
     * @author: stonechen@gdtengnan.com
     *
     * @param: variable
     *
     * @return:
     */
    public function getUser($userid)
    {
        $token  = $this->getQyAccessToken();
        $url    = self::API_BASE_URL_PREFIX . self::USER_LIST_URL . 'access_token=' . $token . '&userid=' . $userid;
        $result = Curl::curlGet($url);

        return $result;
    }

    /**
     * @desc: 主动发送信息
     * 应用支持推送文本、图片、视频、文件、图文等类型
     * @author: stonechen@gdtengnan.com
     *
     * @param: $GLOBALS
     *
     * @return:
     */
    public function sendMessage($data, $corpsecret, $agentid)
    {
        if ( !$this->access_token && !$this->getQyAccessToken($this->corpid, $corpsecret, $agentid) ) {
            return false;
        }
        $result = Curl::curlPost(self::API_BASE_URL_PREFIX . self::SEND_MESSAGE_URL . 'access_token=' . $this->access_token,
            $data);

        return $result;
    }

    /**
     * 生成发送消息模板
     *
     * @param $authStatus
     * @param $content
     *
     * @return string
     */
    public function getSendContent($authStatus, $content)
    {
        $toUser  = $content['toUser'];
        $agentId = '1000003';
        if ( $authStatus == 1 ) {
            $to = '"totag" : "' . $toUser . '"';
        } elseif ( $authStatus == 4 ) {
            if ( $content['toType'] == 1 ) {
                $to = '"toparty" : "' . $toUser . '"';
            } else {
                $to = '"totag" : "' . $toUser . '"';
            }
        } else {
            $to = '"touser" : "' . $toUser . '"';
        }
        $data = '{' . $to . ',
           "msgtype": "news",
           "agentid": "' . $agentId . '",
           "news": {
               "articles":[
                   {
                       "title": "' . $content['title'] . '",
                       "description": "' . $content['desc'] . '",
                       "url": "' . $content['url'] . '",
                       "picurl": "' . 'http://' . $_SERVER['SERVER_NAME'] . self::$templatePicArr[$authStatus] . '"
                   }
               ]
           }
        }';

        return $data;
    }

    /**
     * @desc: 获取部门列表
     * @date: 2018-3-21 上午11:52:46
     * @author: stonechen@gdtengnan.com
     *
     * @param: variable
     *
     * @return:
     */
    public function getDept($id)
    {
        if ( !$this->access_token && !$this->getQyAccessToken() ) {
            return false;
        }
        $result = Curl::curlGet(self::API_BASE_URL_PREFIX . self::DEPARTMENT_URL . 'access_token=' . $this->access_token . '&id=' . $id);

        return $result;
    }

    /**
     * @desc: 编辑部门
     * @author: stonechen@gdtengnan.com
     *
     * @param: variable
     *
     * @return:
     */
    public function editDept($data = '', $id = '')
    {
        if ( !$this->access_token && !$this->getQyAccessToken() ) {
            return false;
        }
        if ( isset($id) && $id ) {
            $result = Curl::curlPost(self::API_BASE_URL_PREFIX . self::DEPARTMENT_UPDATE_URL . 'access_token=' . $this->access_token,
                $data);
        } else {
            $result = Curl::curlPost(self::API_BASE_URL_PREFIX . self::DEPARTMENT_ADD_URL . 'access_token=' . $this->access_token,
                $data);
        }

        return $result;
    }

    /**
     * @desc: 删除部门
     * @date: 2018-3-21 下午1:12:28
     * @author: stonechen@gdtengnan.com
     *
     * @param: variable
     *
     * @return:
     */
    public function delDept($id)
    {
        if ( !$this->access_token && !$this->getQyAccessToken() ) {
            return false;
        }
        $result = Curl::curlGet(self::API_BASE_URL_PREFIX . self::DEPARTMENT_DEL_URL . 'access_token=' . $this->access_token . '&id=' . $id);

        return $result;
    }

    /**
     * @desc: 新增成员
     * @date: 2018-3-21 下午1:07:59
     * @author: stonechen@gdtengnan.com
     *
     * @param: variable
     *
     * @return:
     */
    public function addUser($data)
    {
        if ( !$this->access_token && !$this->getQyAccessToken() ) {
            return false;
        }

        $result = Curl::curlPost(self::API_BASE_URL_PREFIX . self::USER_ADD_URL . 'access_token=' . $this->access_token,
            $data);

        return $result;
    }

    /**
     * @desc: 更新成员
     * @date: 2018-3-21 下午2:28:14
     * @author: stonechen@gdtengnan.com
     *
     * @param: variable
     *
     * @return:
     */
    public function editUser($data)
    {
        if ( !$this->access_token && !$this->getQyAccessToken() ) {
            return false;
        }

        $result = Curl::curlPost(self::API_BASE_URL_PREFIX . self::USER_UPDATE_URL . 'access_token=' . $this->access_token,
            $data);

        return $result;
    }

    /**
     * @desc: 删除成员
     * @date: 2018-3-21 下午2:29:49
     * @author: stonechen@gdtengnan.com
     *
     * @param: variable
     *
     * @return:
     */
    public function delUser($userid)
    {
        if ( !$this->access_token && !$this->getQyAccessToken() ) {
            return false;
        }
        $result = Curl::curlGet(self::API_BASE_URL_PREFIX . self::USER_DEL_URL . 'access_token=' . $this->access_token . '&userid=' . $userid);

        return $result;
    }

    /**
     * @desc: 批量删除用户
     * @date: 2018-3-21 下午2:31:28
     * @author: stonechen@gdtengnan.com
     *
     * @param: variable
     *
     * @return:
     */
    public function batchDelUser($data)
    {
        if ( !$this->access_token && !$this->getQyAccessToken() ) {
            return false;
        }
        $result = Curl::curlPost(self::API_BASE_URL_PREFIX . self::USER_BATCH_DEL_URL . 'access_token=' . $this->access_token,
            $data);

        return $result;
    }

    /**
     * openid 换 userid
     *
     * @param $data
     *
     * @return bool|mixed
     */
    public function convertToUserid($data)
    {
        $result = Curl::curlPost(self::API_BASE_URL_PREFIX . self::USER_CONVERT_TO_USERID_URL . 'access_token=' . $this->access_token,
            $data);

        return $result;
    }

    /**
     * @desc: 发送邀请
     * @date: 2018-3-23 下午4:32:55
     * @author: stonechen@gdtengnan.com
     *
     * @param: variable
     *
     * @return:
     */
    public function inviteUser($data)
    {
        if ( !$this->access_token && !$this->getQyAccessToken() ) {
            return false;
        }
        $result = Curl::curlPost(self::API_BASE_URL_PREFIX . self::INVITE_USER_URL . 'access_token=' . $this->access_token,
            $data);

        return $result;
    }

    /**
     * @desc: 获取部门成员
     * @date: 2018-3-21 下午1:14:55
     * @author: stonechen@gdtengnan.com
     *
     * @param: $id int 获取的部门id
     * @param: $fetch_child 1/0：是否递归获取子部门下面的成员
     *
     * @return:
     */
    public function getDeptMember($id, $fetch_child)
    {
        if ( !$this->access_token && !$this->getQyAccessToken() ) {
            return false;
        }
        $url    = self::API_BASE_URL_PREFIX . DEPARTMENT_USER_URL . 'access_token=' . $this->access_token . '&department_id=' . $id . '&fetch_child=' . $fetch_child;
        $result = Curl::curlGet($url);

        return $result;
    }

    /**
     * @desc: 获取部门成员详情
     * @date: 2018-3-21 下午1:22:45
     * @author: stonechen@gdtengnan.com
     *
     * @param: variable
     *
     * @return:
     */
    public function getDeptMemberDetail($id, $fetch_child)
    {
        if ( !$this->access_token && !$this->getQyAccessToken() ) {
            return false;
        }
        $url    = self::API_BASE_URL_PREFIX . DEPARTMENT_USER_DETAIL_URL . 'access_token=' . $this->access_token . '&department_id=' . $id . '&fetch_child=' . $fetch_child;
        $result = Curl::curlGet($url);

        return $result;
    }

    /**
     * @desc: 获取标签列表
     * @date: 2018-3-21 下午2:35:10
     * @author: stonechen@gdtengnan.com
     *
     * @param: variable
     *
     * @return:
     */
    public function getTagList()
    {
        if ( !$this->access_token && !$this->getQyAccessToken() ) {
            return false;
        }
        $result = Curl::curlGet(self::API_BASE_URL_PREFIX . self::TAG_LIST_URL . 'access_token=' . $this->access_token);

        return $result;
    }

    /**
     * @desc: 获取标签成员
     * @date: 2018-3-21 下午2:35:57
     * @author: stonechen@gdtengnan.com
     *
     * @param: $GLOBALS
     *
     * @return:
     */
    public function getTagUser($tagid)
    {
        if ( !$this->access_token && !$this->getQyAccessToken() ) {
            return false;
        }
        $result = Curl::curlGet(self::API_BASE_URL_PREFIX . self::TAG_USER_URL . 'access_token=' . $this->access_token . '&tagid=' . $tagid);

        return $result;
    }

    /**
     * @desc: 编辑标签
     * @date: 2018-3-21 下午2:42:04
     * @author: stonechen@gdtengnan.com
     *
     * @param: variable
     *
     * @return:
     */
    public function editTag($data = '', $tagid = '')
    {
        if ( !$this->access_token && !$this->getQyAccessToken() ) {
            return false;
        }
        if ( isset($tagid) && $tagid ) {
            $result = Curl::curlPost(self::API_BASE_URL_PREFIX . self::TAG_UPDATE_URL . 'access_token=' . $this->access_token,
                $data);
        } else {
            $result = Curl::curlPost(self::API_BASE_URL_PREFIX . self::TAG_ADD_URL . 'access_token=' . $this->access_token,
                $data);
        }

        return $result;
    }

    /**
     * @desc: 删除标签
     * @date: 2018-3-21 下午3:17:19
     * @author: stonechen@gdtengnan.com
     *
     * @param: variable
     *
     * @return:
     */
    public function delTag($id)
    {
        if ( !$this->access_token && !$this->getQyAccessToken() ) {
            return false;
        }
        $result = Curl::curlGet(self::API_BASE_URL_PREFIX . self::TAG_DEL_URL . 'access_token=' . $this->access_token . '&tagid=' . $id);

        return $result;
    }

    /**
     * @desc: 增加标签成员
     * @date: 2018-3-21 下午3:20:42
     * @author: stonechen@gdtengnan.com
     *
     * @param: variable
     *
     * @return:
     */
    public function addTagUsers($data)
    {
        if ( !$this->access_token && !$this->getQyAccessToken() ) {
            return false;
        }
        $result = Curl::curlPost(self::API_BASE_URL_PREFIX . self::TAG_ADD_USER_URL . 'access_token=' . $this->access_token,
            $data);

        return $result;
    }

    /**
     * @desc: 删除标签成员
     * @author: stonechen@gdtengnan.com
     *
     * @param: variable
     *
     * @return:
     */
    public function delTagUsers($data)
    {
        if ( !$this->access_token && !$this->getQyAccessToken() ) {
            return false;
        }
        $result = Curl::curlPost(self::API_BASE_URL_PREFIX . self::TAG_DEL_USER_URL . 'access_token=' . $this->access_token,
            $data);

        return $result;
    }

    //获取有登录权限会员
    public function getLoginMember($department = 0)
    {
        $member_list = [];
        if ( $department ) {
            $loginTag = M('tag')->field('tagid')->where([ 'deptid' => $department, 'login' => 1 ])->select();
            if ( $loginTag ) {
                foreach ( $loginTag as $vv ) {
                    $result = self::getTagMember($vv['tagid']);
                    $mem    = json_decode($result, true);
                    if ( $mem['errcode'] == 0 && !empty($mem['userlist']) ) {
                        $member_list[$vv['tagid']] = $mem['userlist'];
                    }
                }
            }
        }

        return $member_list;
    }


    /**
     * 查看企业号配置
     */
    public function getconfigcorp()
    {
        //查看企业号基本配置
        $data = Cache::get('qyconfig');
        if ( !$data ) {
            $qyweixinconfig = QyweixinConfig::get([ 'status' => 1 ])->toArray();
            $qyconfig       = [ 'corpid' => $qyweixinconfig['appid'] ];
            Cache::set('qyconfig', json_encode($qyconfig));
        }

        $data = Cache::get('qyconfig');
        $data = json_decode($data, true);


        $corpid = $data['corpid'];
        //获取企业微信应用配置信息

        $appdata = Cache::get('qyweixinconfig');
        if ( !!$appdata ) {
            $appdata = json_decode($appdata, true);
        } else {
            $config         = QyweixinConfig::get([ 'status' => 1 ])->toArray();
            $qyweixinagent  = new QyweixinAgent();
            $appdata        = $qyweixinagent->where([ 'qyid' => $config['id'] ])->select();
            $qyweixinconfig = [];
            foreach ( $appdata as $row ) {
                $qyweixinconfig[$row->code] = [
                    'agentid'    => $row->agentid,
                    'corpsecret' => $row->secret
                ];
            }
            Cache::set('qyweixinconfig', json_encode($qyweixinconfig));
            $appdata = Cache::get('qyweixinconfig');
            $appdata = json_decode($appdata, true);
        }
        //查看通讯录是否存在
        if ( $appdata['txl'] ) {
            $corpsecret = $appdata['txl']['corpsecret'];

            return [ 'corpid' => $corpid, 'corpsecret' => $corpsecret ];
        } else {
            return false;
        }
    }


    /**
     * 获取JSAPI授权TICKET
     * Power: Mikkle
     * Email：776329498@qq.com
     *
     * @return bool|mixed
     */
    public function getJsTicket($corpid = '', $corpsecret = '')
    {

        if ( !$corpid ) {
            $corpid = $this->corpid;
        }
        if ( !$corpsecret ) {
            $corpsecret = $this->corpsecret;
        }


        if ( !$this->access_token && !$this->getQyAccessToken($corpid, $corpsecret) ) {
            return false;
        }
        $cache        = 'jsapi_ticket' . $corpid;
        $jsapi_ticket = Cache::get($cache);
        if ( $jsapi_ticket ) {
            $this->jsapi_ticket = $jsapi_ticket;

            return $this->jsapi_ticket;
        } else {
            # 调接口获取
            $result = Curl::curlGet(self::API_BASE_URL_PREFIX . self::GET_TICKET_URL . "access_token={$this->access_token}");
//            $result = Curl::curlGet(self::API_BASE_URL_PREFIX . self::GET_TICKET_URL . "access_token={$this->access_token}" . '&type=agent_config');
            if ( $result ) {
                $json = json_decode($result, true);
                if ( !$json || intval($json['errcode']) != 0 ) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg  = $json['errmsg'];
                    Log::error("Get New AccessToken Error. {$this->errMsg}[{$this->errCode}]");

                    return false;
                }
                $this->jsapi_ticket = $json['ticket'];

                //存入数据库
                $qygent         = QyweixinAgent::get([ 'secret' => $corpsecret ]);
                $qygent->ticket = $this->jsapi_ticket;
                $qygent->save();

                //存进去缓存
                Cache::set($cache, $this->jsapi_ticket, 7000);

                return $this->jsapi_ticket;
            }
        }

        return false;
    }


    /**
     * 获取JsApi使用签名
     * Power: Mikkle
     * Email：776329498@qq.com
     *
     * @param $url
     *
     * @return array|bool
     */
    public function getJsSign($url)
    {

        if ( empty($url) ) {
            return false;
        }

        if ( !$this->jsapi_ticket ) {

            $this->getJsTicket();

            if ( !$this->jsapi_ticket ) {
                return false;
            }
        }
        $data   = [
            "jsapi_ticket" => $this->jsapi_ticket,
            "timestamp"    => time(),
            "noncestr"     => '' . StaticFunction::createRandStr(16),
            "url"          => trim($url),
        ];
        $qygent = QyweixinAgent::get([ 'secret' => $this->corpsecret ]);
        $qyinfo = QyweixinConfig::get([ 'id' => $qygent->qyid ]);

        return [
            "url"       => $data['url'],
            'beta'      => true,
            'debug'     => false,
            "appId"     => $qyinfo->appid,
            "timestamp" => $data['timestamp'],
            "nonceStr"  => $data['noncestr'],
            "signature" => StaticFunction::getSignature($data, 'sha1'),
            'jsApiList' => [
                'onMenuShareAppMessage',
                'onMenuShareWechat',
                'onMenuShareTimeline',
                'startRecord',
                'stopRecord',
                'onVoiceRecordEnd',
                'playVoice',
                'pauseVoice',
                'stopVoice',
                'onVoicePlayEnd',
                'uploadVoice',
                'downloadVoice',
                'chooseImage',
                'previewImage',
                'uploadImage',
                'downloadImage',
                'getLocalImgData',
                'previewFile',
                'getNetworkType',
                'onNetworkStatusChange',
                'openLocation',
                'getLocation',
                'startAutoLBS',
                'stopAutoLBS',
                'onLocationChange',
                'onHistoryBack',
                'hideOptionMenu',
                'showOptionMenu',
                'hideMenuItems',
                'showMenuItems',
                'hideAllNonBaseMenuItem',
                'showAllNonBaseMenuItem',
                'closeWindow',
                'openDefaultBrowser',
                'scanQRCode',
                'selectEnterpriseContact',
                'openEnterpriseChat',
                'chooseInvoice',
                'selectExternalContact',
                'getCurExternalContact',
                'openUserProfile',
                'shareAppMessage',
                'shareWechatMessage',
                'startWifi',
                'stopWifi',
                'connectWifi',
                'getWifiList',
                'onGetWifiList',
                'onWifiConnected',
                'getConnectedWifi',
                'setClipboardData',
                'getClipboardData'
            ]
        ];
    }

}