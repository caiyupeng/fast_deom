<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:91:"Z:\Applications\MAMP\htdocs\fastadmindemo\public/../application/admin\view\index\login.html";i:1560409072;s:81:"Z:\Applications\MAMP\htdocs\fastadmindemo\application\admin\view\common\meta.html";i:1547349022;s:83:"Z:\Applications\MAMP\htdocs\fastadmindemo\application\admin\view\common\script.html";i:1547349022;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
<title><?php echo (isset($title) && ($title !== '')?$title:''); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="renderer" content="webkit">

<link rel="shortcut icon" href="/assets/img/favicon.ico" />
<!-- Loading Bootstrap -->
<link href="/assets/css/backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">

<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
  <script src="/assets/js/html5shiv.js"></script>
  <script src="/assets/js/respond.min.js"></script>
<![endif]-->
<script type="text/javascript">
    var require = {
        config:  <?php echo json_encode($config); ?>
    };
</script>

    <style type="text/css">
        body {
            color: #999;
            background: url('<?php echo $background; ?>');
            background-size: cover;
        }

        a {
            color: #fff;
        }

        .login-panel {
            margin-top: 150px;
        }

        .login-screen {
            max-width: 400px;
            padding: 0;
            margin: 100px auto 0 auto;

        }

        .login-screen .well {
            border-radius: 3px;
            -webkit-box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.2);
        }

        .login-screen .copyright {
            text-align: center;
        }

        @media (max-width: 767px) {
            .login-screen {
                padding: 0 20px;
            }
        }

        .profile-img-card {
            width: 100px;
            height: 100px;
            margin: 10px auto;
            display: block;
            -moz-border-radius: 50%;
            -webkit-border-radius: 50%;
            border-radius: 50%;
        }

        .profile-name-card {
            text-align: center;
        }

        #login-form {
            margin-top: 20px;
        }

        #login-form .input-group {
            margin-bottom: 15px;
        }

    </style>
</head>
<body>
<div class="container">
    <div class="login-wrapper">
        <div class="login-screen">
            <div class="well">
                <div class="login-form">
                    <img id="profile-img" class="profile-img-card" src="/assets/img/avatar.png"/>
                    <p id="profile-name" class="profile-name-card"></p>

                    <!--<form action="" method="post" id="login-form" onsubmit="return md5ps()">-->
                    <form action="" method="post" id="login-form">
                        <div id="errtips" class="hide"></div>
                        <?php echo token(); ?>
                        <div class="input-group">
                            <div class="input-group-addon"><span class="glyphicon glyphicon-user"
                                                                 aria-hidden="true"></span></div>
                            <input type="text" class="form-control" id="pd-form-username"
                                   placeholder="<?php echo __('Username'); ?>" name="username" autocomplete="off" value=""
                                   data-rule="<?php echo __('Username'); ?>:required;username"/>
                        </div>

                        <div class="input-group">
                            <div class="input-group-addon"><span class="glyphicon glyphicon-lock"
                                                                 aria-hidden="true"></span></div>
                            <input type="password" class="form-control" id="pd-form-password"
                                   placeholder="<?php echo __('Password'); ?>" name="password" autocomplete="off" value=""
                                   data-rule="<?php echo __('Password'); ?>:required"/>
                        </div>
                        <?php if($config['fastadmin']['login_captcha']): ?>
                        <div class="input-group">
                            <div class="input-group-addon"><span class="glyphicon glyphicon-option-horizontal"
                                                                 aria-hidden="true"></span></div>
                            <input type="text" name="captcha" class="form-control" placeholder="<?php echo __('Captcha'); ?>"
                                   data-rule="<?php echo __('Captcha'); ?>:required;length(4)"/>
                            <span class="input-group-addon" style="padding:0;border:none;cursor:pointer;">
                                        <img src="<?php echo rtrim('/', '/'); ?>/captcha" width="100" height="30"
                                             onclick="this.src = '<?php echo rtrim('/', '/'); ?>/captcha?r=' + Math.random();"/>
                                    </span>
                        </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label class="inline" for="keeplogin">
                                <input type="checkbox" name="keeplogin" id="keeplogin" value="1"/>
                                <?php echo __('Keep login'); ?>
                            </label>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-lg btn-block" onclick="cmdEncrypt();">
                                <?php echo __('Sign in'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- FastAdmin是开源程序，建议在您的网站底部保留一个FastAdmin的链接 -->
            <!--<p class="copyright"><a href="https://www.fastadmin.net">Powered By FastAdmin</a></p>-->
        </div>
    </div>
</div>
<script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo $site['version']; ?>"></script>
<!--<script src="/assets/js/backend/md5.js"></script>-->
<script type="text/javascript" src="/assets/js/RSA_JS_PHP/js/jsbn.js"></script>
<script type="text/javascript" src="/assets/js/RSA_JS_PHP/js/prng4.js"></script>
<script type="text/javascript" src="/assets/js/RSA_JS_PHP/js/rng.js"></script>
<script type="text/javascript" src="/assets/js/RSA_JS_PHP/js/rsa.js"></script>

<script type="application/javascript">

    function cmdEncrypt() {
        var passwd = $('#pd-form-password').val();
        if(passwd.length<30){
            var rsa = new RSAKey();
            var modulus = "D7A59DFD8635DA7C3E36AAAD4DA7FAC6D379A5511CA8FE0C969CB2405D2C52294B6775B34AAEA418773A2F45E41DB020757F9FF243B91C977D09DA93437413B9106D3E263CA76491454B32A51661C9EDABF535956DD9C0277D09D49D62E2ECF7D1D971B4DBBF5206279EFA06FE14932DAC65DAD4D50CA1EDDC2D1A815D8DDBC9";
            var exponent = "10001";
            rsa.setPublic(modulus, exponent);
            var res = rsa.encrypt(passwd);
            $('#pd-form-password').val(res);
        }
    }

</script>
</body>
</html>