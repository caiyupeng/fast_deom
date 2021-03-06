<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/8/30
 * Time: 9:21
 */

namespace mikkle\tp_wxpay;


/**
 * 静态链接二维码
 */
class NativeLink_pub  extends Common_util_pub
{
    var $parameters;//静态链接参数
    var $url;//静态链接

    function __construct()
    {
    }

    /**
     * 设置参数
     */
    function setParameter($parameter, $parameterValue)
    {
        $this->parameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
    }

    /**
     * 生成Native支付链接二维码
     */
    function createLink()
    {
        try
        {
            if($this->parameters["product_id"] == null)
            {
                throw new SDKRuntimeException("缺少Native支付二维码链接必填参数product_id！"."<br>");
            }
            $this->parameters["appid"] = config('wechat_appid');//公众账号ID
            $this->parameters["mch_id"] = config('wechat_mchid');//商户号
            $time_stamp = time();
            $this->parameters["time_stamp"] = "$time_stamp";//时间戳
            $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
            $this->parameters["sign"] = $this->getSign($this->parameters);//签名
            $bizString = $this->formatBizQueryParaMap($this->parameters, false);
            $this->url = "weixin://wxpay/bizpayurl?".$bizString;
        }catch (SDKRuntimeException $e)
        {
            die($e->errorMessage());
        }
    }

    /**
     * 返回链接
     */
    function getUrl()
    {
        $this->createLink();
        return $this->url;
    }
}
