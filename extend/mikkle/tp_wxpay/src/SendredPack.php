<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/4/6
 * Time: 11:54
 */

namespace mikkle\tp_wxpay\src;


use mikkle\tp_master\Exception;
use mikkle\tp_wxpay\base\Tools;
use mikkle\tp_wxpay\base\WxpayClientBase;

class SendredPack extends WxpayClientBase
{
    protected $url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack";

    protected function checkParams()
    {
        //检测必填参数
        if ($this->params["send_name"] == null) {
            throw new Exception("缺少统一支付接口必填参数send_name！" . "<br>");
        } elseif ($this->params["re_openid"] == null) {
            throw new Exception("缺少统一支付接口必填参数re_openid！" . "<br>");
        } elseif ($this->params["total_amount"] == null) {
            throw new Exception("缺少统一支付接口必填参数total_amount！" . "<br>");
        } elseif ($this->params["total_num"] == null) {
            throw new Exception("缺少统一支付接口必填参数total_num！" . "<br>");
        } elseif ($this->params["wishing"] == null) {
            throw new Exception("缺少统一支付接口必填参数wishing！" . "<br>");
        }elseif ($this->params["act_name"] == null) {
            throw new Exception("缺少统一支付接口必填参数act_name！" . "<br>");
        }elseif ($this->params["remark"] == null) {
            throw new Exception("缺少统一支付接口必填参数remark！" . "<br>");
        }
        if (!isset( $this->params["client_ip"] ) || $this->params["client_ip"] == null){
            $this->params["client_ip"] =Tools::getRealIp();//终端ip
        }
    }

    /**
     * 	作用：获取结果，默认不使用证书
     */
    function getResult()
    {
        $this->postXmlSSL();
        $this->result = Tools::xmlToArray($this->response);
        return $this->result;
    }


}