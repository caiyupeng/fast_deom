<?php
namespace helper;

class RedisHelper
{
    /**
     * 类对象实例数组,共有静态变量
     * @var null
     */
    private static $_instance;

    /**
     * 配置详情
     * @var array
     */
    private static $_options = [
        'host'       => '127.0.0.1',
        'port'       => 6379,
        'password'   => '',
        'select'     => 0,
        'timeout'    => 0,
        'expire'     => 0,
        'persistent' => false,
    ];

    private function __construct()
    {

    }

    private function __clone()
    {

    }

    function __destruct()
    {
        if(isset($this->connection)){
            $this->connection->close();
        }
    }

    /**
     * @return null|Redis
     */
    public static function getInstance()
    {
        if (!(static::$_instance instanceof \Redis))
        {
            if (!extension_loaded('redis')) {
                throw new \BadFunctionCallException('not support: redis');
            }

            self::$_options = array_merge(self::$_options, \think\Config::get('redis'));

            self::$_instance = new \Redis();

            if (self::$_options['persistent']) {
                self::$_instance->pconnect(self::$_options['host'], self::$_options['port'], self::$_options['timeout'], 'persistent_id_' . self::$_options['select']);
            } else {
                self::$_instance->connect(self::$_options['host'], self::$_options['port'], self::$_options['timeout']);
            }

            if ('' != self::$_options['password']) {
                self::$_instance->auth(self::$_options['password']);
            }

            if (0 != self::$_options['select']) {
                self::$_instance->select(self::$_options['select']);
            }
        }

        return self::$_instance;
    }

}