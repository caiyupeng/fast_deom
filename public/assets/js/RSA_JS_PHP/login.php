<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/14
 * Time: 9:48
 */
header('Content-type:text/html;charset=utf-8');

//私钥
$private_key = "-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQDbHqVytV9dnIrfCS9dzDVZz+qM6LtU46cdqbGvvX0Xz4CtsiT+
TqU3m8eC9BwTd0jY8bWjatYqEn716H77JcIJpmvO6ZJc4JYxvyJx6BEj6TQ4ZGYl
CA/wT08s9TKwd+PjkEht9A51hvCuUiyHPzMXAiL0a9tghPVd5rcDHlXb5wIDAQAB
AoGBANhrD2wZWYSi7cJWVxMkc3kuUvIzl3rDkrZIeXgjBp9y0hw8fC80zBf9Y3Oi
2Owc/7VOHmG2TqqlNAJ7TJePdnGvEG5yzHuMH6/uRPS4A+gDndM8U/sZBUYaZjbr
5M8vg6wL3yQ2awAbXu7pwLEvxVmuvhv+0jOFnqLpTRlki3ZpAkEA+Y00pTwikCEt
N+dkFGbhzZfH6bFNIkUOCrkDMgru1IargO/ggllk4fVLe7WBMWwh/0X9oTeTjLi7
Es856QMdpQJBAODIIeu7/cL3wp6Bigg7V25OSD+7uSjlCpoPSUNZIjZ6HJQsFCnU
RHsEDeD1f88g7i9AGI0htYiJXCgwd6GE9ZsCQGoCUhrfMM+JSGw3H4yLJ+DuWT4s
01d7fjuP3IulmU8u5iwfun+k+fYC/c3PjNIx3T9TvCqAMW3WC6Ix5afWawECQA6p
n2TUL3pvVPen9YwR6uMcIiReJ3becfGYu6uz/cJV9tVHhs0vtoPbwNgCy6KEQGU+
phtWrpPIegV5G+SiWq8CQQCoH+ic1j9b1DzENUb206w7KpcIhm629iUWUgBTrnlC
LzOA6xwY78V7cAUdzhTycAxhmWq/1FBlCCKtuZHVHnE/
-----END RSA PRIVATE KEY-----";

$hex_encrypt_data = trim($_POST['password']); //十六进制数据
$encrypt_data = pack("H*", $hex_encrypt_data);//对十六进制数据进行转换
openssl_private_decrypt($encrypt_data, $decrypt_data, $private_key);

echo '解密后的数据：' . $decrypt_data;
