<?php

namespace app\api\controller\wechatapp;

use app\common\model\wechatapp\Config;
use app\common\model\wechatapp\Formidlist;
use app\common\model\wechatapp\Send;
use think\Controller;


class Sendmessage extends Controller
{
    /**
     * User: little_cai
     * Date: 2019/6/17 3:04 PM
     * @return mixed
     * 获取小程序token
     */
    public function getAccessToken()
    {
        //获取小程序配置
        $configModel = Config::get([ 'id' => 1 ], [], 60);
        $APPID       = $configModel['appid'];
        $AppSecret   = $configModel['appsecret'];
        $accesstoken = $configModel['accesstoken'];
        $url         = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $APPID . '&secret=' . $AppSecret . '';
        if ( $accesstoken ) {
            //获取最后更新时间
            $updatetime = $configModel['updatetime'];
            if ( (time() - $updatetime) > 7000 ) {
                $arr   = $this->vegt($url);
                $arr   = json_decode($arr, true);
                $token = $arr['access_token'];
                //更新最新时间
                $configModel->accesstoken = $token;
                $configModel->updatetime  = time();
                $configModel->save();

            } else {
                $token = $accesstoken;
            }
        } else {
            $arr   = $this->vegt($url);
            $arr   = json_decode($arr, true);
            $token = $arr['access_token'];
            //更新最新时间
            $configModel->accesstoken = $token;
            $configModel->updatetime  = time();
            $configModel->save();
        }

        return $token;
    }

    /**
     * User: little_cai
     * Date: 2019/6/17 2:54 PM
     * 测试
     */
    public function testsendmessage()
    {
        //发送企业微信信息
        $data['openid']   = '12';
        $data['keyword1'] = '12';
        $data['keyword2'] = '申请注册加入腾讯智慧党建小程序';
        $data['keyword3'] = '不通过';
        $data['keyword4'] = date('Y-m-d H:i', time());
        $data['keyword5'] = '未通过原因';
        $data['page']     = 'pages/home/index';
        $data['type']     = 1;
        $minisendmessage  = new \app\api\controller\wechatapp\Sendmessage();
        $minisendmessage->insertSendMsg($data);
        $minisendmessage->sendminiwechatmessage();
    }

    /**
     * User: little_cai
     * Date: 2019/6/14 10:20 AM
     * 插入需要推送的数据
     */
    public function insertSendMsg($data)
    {
        //审核推送
        $model                   = new Send();
        $inertdata               = [];
        $inertdata['openid']     = $data['openid'];
        $inertdata['userid']     = $data['userid'];
        $inertdata['type']       = $data['type'];
        $inertdata['sendmsg']    = json_encode($data);
        $inertdata['createtime'] = time();
        $inertdata['updatetime'] = time();
        $inertdata['status']     = 0;
        $model->data($inertdata);
        $model->save();
    }

    /**
     * User: little_cai
     * Date: 2019/6/14 10:51 AM
     * 定时推送小程序信息
     */
    public function sendminiwechatmessage()
    {
        $sendmodel = new Send();
//        40037	template_id不正确
//        41028	form_id不正确，或者过期
//        41029	form_id已被使用
//        500	内部错误
        //获取未推送的数据
        $senddata = $sendmodel
            ->where('(status = 0) or (status =2 and errornum < 5 and (code=\'41028\' or code=\'41029\' or code=\'500\')) ')
            ->limit(10)
            ->select();
        $senddata = collection($senddata)->toArray();
        foreach ( $senddata as $v ) {
            $this->sendmessage($v);
        }
        echo 'ok';
    }

    /**
     * User: little_cai
     * Date: 2019/6/14 9:56 AM
     *
     * @param $data
     * @param $type 1小程序支付成功通知模板 2小程序咨询回复通知 3小程序审核通知
     *
     * @return bool
     * 小程序推送
     *
     */
    public function sendmessage($data)
    {
        $mpopenid = $data['openid'];
        $sendmodel = new Send();
        $sendinfo  = $sendmodel->get($data['id']);

        if ( !$mpopenid ) {
            if ( $sendinfo ) {
                $sendinfo->status     = 2;
                $sendinfo->updatetime = time();
                $sendinfo->remark     = '获取不到小程序openid';
                $sendinfo->code       = 500;
                $sendinfo->save();
                $sendmodel->where('id', $data['id'])->setInc('errornum');
            }
            return false;
        }
        $senddata = json_decode($data['sendmsg'], true);
        if ( !$senddata ) {
            if ( $sendinfo ) {
                $sendinfo->status     = 2;
                $sendinfo->updatetime = time();
                $sendinfo->remark     = 'sendmsg数据错误';
                $sendinfo->save();
                $sendmodel->where('id', $data['id'])->setInc('errornum');
            }
            return false;
        }
        //获取我有没有存在可推送的formid
        //5天之内
        $time = time() - (5 * 24 * 3600);
        $form = Formidlist::where([ 'userid' => $mpopenid, 'type' => 0 ])
            ->where('createtime > ' . $time)
            ->where('formid != \'the formId is a mock one\'')
            ->field('*,length(fromid) >= 13')
            ->order('id asc')
            ->find();

        if ( !$form ) {
            if ( $sendinfo ) {
                $sendinfo->status     = 2;
                $sendinfo->updatetime = time();
                $sendinfo->remark     = '找不到最新的formid';
                $sendinfo->code       = 500;
                $sendinfo->save();
                $sendmodel->where('id', $data['id'])->setInc('errornum');
            }

            return false;
        }
        $sendinfo->formid = $form->formid;

        $config = \app\common\model\Config::get([ 'name' => 'wechatapptemplate' . $data['type'] ]);
        if ( !$config ) {
            $sendinfo->status     = 2;
            $sendinfo->updatetime = time();
            $sendinfo->remark     = '获取不到模板配置';
            $sendinfo->code       = 500;
            $sendinfo->save();
            $sendmodel->where('id', $data['id'])->setInc('errornum');
            return false;
        }
        $page               = isset($senddata['page']) ? $senddata['page'] : '/pages/news/index';
        $wechat_template_id = $config->value;
        $token              = $this->getAccessToken();
        $sendurl            = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=' . $token;

        if ( $data['type'] == 1 ) {
            $sendmsgdata = '{
                          "touser": "' . $mpopenid . '",
                          "template_id": "' . $wechat_template_id . '",
                          "page": "' . $page . '",
                          "form_id": "' . $form->fromid . '",
                          "data": {
                                     "keyword1": {
                                         "value":"' . $senddata['keyword1'] . '",
                                         "color":"#173177"
                                      },
                                      "keyword2": {
                                            "value":"' . $senddata['keyword2'] . '",
                                            "color":"#173177"
                                      },
                                      "keyword3": {
                                           "value":"' . $senddata['keyword3'] . '",
                                            "color":"#173177"
                                      } ,
                                      "keyword4": {
                                           "value":"' . $senddata['keyword4'] . '",
                                            "color":"#173177"
                                      },
                                      "keyword5": {
                                           "value":"' . $senddata['keyword5'] . '",
                                            "color":"#173177"
                                      }
                                    }
                        }';
        }
        $post = json_decode($sendmsgdata, true);
        $returnarr = $this->curlPost($sendurl, $post);
        if ( $returnarr ) {
            $arr = json_decode($returnarr, true);
            if ( $arr ) {
                //是否可用
                if ( $arr['errcode'] == '0' ) {
                    $form->type = 1;
                    $form->msg  = $arr['errmsg'];
                    $form->save();
                    if ( $sendinfo ) {
                        $sendinfo->status     = 1;
                        $sendinfo->updatetime = time();
                        $sendinfo->remark     = $arr['errmsg'];
                        $sendinfo->code       = $arr['errcode'];
                        $sendinfo->save();
                    }
                    return true;
                } else {
                    $form->type = 2;
                    $form->msg  = $arr['errmsg'];
                    $form->save();
                    if ( $sendinfo ) {
                        $sendinfo->status     = 2;
                        $sendinfo->updatetime = time();
                        $sendinfo->remark     = $arr['errmsg'];
                        $sendinfo->code       = $arr['errcode'];
                        $sendinfo->save();
                    }
                    return false;
                }
            } else {
                $sendinfo->status     = 2;
                $sendinfo->updatetime = time();
                $sendinfo->remark     = '返回数据解析错误' . $returnarr;
                $sendinfo->code       = 500;
                $sendinfo->save();
                $sendmodel->where('id', $data['id'])->setInc('errornum');
                return false;
            }
        } else {
            $sendinfo->status     = 2;
            $sendinfo->updatetime = time();
            $sendinfo->remark     = '接口返回错误解析不了';
            $sendinfo->code       = 500;
            $sendinfo->save();
            $sendmodel->where('id', $data['id'])->setInc('errornum');
            return false;
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
     * POST 请求
     *
     * @param string  $url
     * @param array   $param
     * @param boolean $post_file 是否文件上传
     *
     * @return string content
     */
    private function curlPost($url, $param, $post_file = false)
    {
        $oCurl = curl_init();
        if ( stripos($url, "https://") !== false ) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if ( is_string($param) || $post_file ) {
            $strPOST = $param;
        } elseif ( is_array($param) ) {
            $strPOST = json_encode($param);
        } else {
            $aPOST = [];
            foreach ( $param as $key => $val ) {
                $aPOST[] = $key . "=" . urlencode($val);
            }
            $strPOST = join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus  = curl_getinfo($oCurl);
        curl_close($oCurl);
        if ( intval($aStatus["http_code"]) == 200 ) {
            return $sContent;
        } else {
            return false;
        }
    }


}
