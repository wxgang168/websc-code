<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head><?php echo $this->fetch('library/seller_html_head.lbi'); ?></head>
<body>
	<div class="sller_login">
    	<div class="sller_login_warp">
            <div class="ecsc-login-container">
            	<div class="logo">
                	<?php if ($this->_var['seller_login_logo']): ?>
                	<img src="<?php echo $this->_var['seller_login_logo']; ?>" />
                    <?php else: ?>
                    <img src="images/loginImg.png">
                    <?php endif; ?>
                </div>
                <div class="container">
                	<div class="warp">
                    	<div class="warp_content">
                            <div class="title">
                                <h3><?php echo $this->_var['lang']['seller_center']; ?></h3>
                                <span class="txt"></span>
                            </div>
                            <form method="post" id="theForm" action="privilege.php?act=signin" name='theForm'>
                                <div class="formInfo">
                                    <div class="formText">
                                        <i class="login-icon login-icon-user"></i>
                                        <input type="text" name="username" autocomplete="off" class="input-text" value="" placeholder="<?php echo $this->_var['lang']['user_name']; ?>">
                                    </div>
                                    <div class="formText">
                                        <i class="login-icon login-icon-pwd"></i>
                                        <input type="password"   style="display:none"/>
                                        <input type="password" name="password" autocomplete="off" class="input-text" value="" placeholder="<?php echo $this->_var['lang']['seller_login_pwd']; ?>">
                                    </div>
                                    <div class="formText">
                                        <div class="checkbox">
                                            <div class="cur">
                                                <input type="hidden" value="0" name="remember">
                                            </div>
                                        </div>
                                        <span class="span"><?php echo $this->_var['lang']['save_info']; ?></span>
                                        <a href="javascript:void(0);" class="forget_pwd"><?php echo $this->_var['lang']['forget_pwd']; ?></a>
                                    </div>
                                    <div class="formText submitDiv">
                                        <?php if ($this->_var['gd_version'] > 0): ?>
                                        <span class="text_span">
                                            <div class="code">
                                                <div class="arrow"></div>
                                                <div class="code-img"><img style="cursor: pointer;" src="index.php?act=captcha" onclick= $(".code-img").find('img').attr('src',"index.php?act=captcha&"+Math.random()) /></div>
                                            </div>
                                            <input type="text" name="captcha" class="input-yzm" value="" autocomplete="off" placeholder="<?php echo $this->_var['lang']['please_input_verify']; ?>" size="4" />
                                        </span>
                                        <span class="submit_span">
                                            <input type="submit" name="submit" class="sub" value="<?php echo $this->_var['lang']['seller_login']; ?>" />
                                        </span>
                                        <?php else: ?>
                                        <span class="submit_span">
                                            <input type="submit"  name="submit" class="sub qp_sub" value="<?php echo $this->_var['lang']['seller_login']; ?>" />
                                        </span>
                                        <?php endif; ?>
                                        <input type="hidden" name="dsc_token" value="<?php echo $this->_var['dsc_token']; ?>" autocomplete="off" />
                                    </div>
                                </div>
                                <div id="error" class="error"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="backpwd_layout">
        <div class="backpwd_info">
            <div class="close"></div>
            <?php if ($this->_var['form_act'] == "forget_pwd"): ?>
            <div class="title"><?php echo $this->_var['lang']['forget_password']; ?></div>
            <form action="get_password.php" method="post" name="submitAdmin" id="submitAdmin1" >
                <div class="formInfo">
                    <div class="formText">
                        <input type="text" name="user_name" class="input-text" autocomplete="off" placeholder="<?php echo $this->_var['lang']['user_name']; ?>"/>
                        <div class="form_prompt"></div>
                    </div>
                    <div class="formText">
                        <input type="text" name="email" class="input-text" autocomplete="off" placeholder="<?php echo $this->_var['lang']['email']; ?>"/>
                        <div class="form_prompt"></div>
                    </div>
                    <div class="formText submitDiv">
                        <input type="submit" name="submit" class="sub qp_sub" value="<?php echo $this->_var['lang']['seller_send']; ?>" id="submitBtn"/>
                        <input type="hidden" name="action" value="get_pwd" />
                        <input type="hidden" name="act" value="forget_pwd" />
                    </div>
                </div>
            </form>
            <?php endif; ?>
            <?php if ($this->_var['form_act'] == "reset_pwd"): ?>
            <div class="title"><?php echo $this->_var['lang']['reset_pwd']; ?></div>
            <form action="get_password.php" method="post" name="submitAdmin" id="submitAdmin2">
                <div class="formInfo">
                    <div class="formText">
                        <input type="password"   style="display:none"/>
                        <input type="password" name="password" id="password" class="input-text" autocomplete="off" placeholder="<?php echo $this->_var['lang']['enter_admin_pwd']; ?>"/>
                        <div class="form_prompt"></div>
                    </div>
                    <div class="formText">
                        <input type="password"   style="display:none"/>
                        <input type="password" name="confirm_pwd" class="input-text" autocomplete="off" placeholder="<?php echo $this->_var['lang']['confirm_admin_pwd']; ?>"/>
                        <div class="form_prompt"></div>
                    </div>
                    <div class="formText submitDiv">
                        <input type="hidden" name="action" value="reset_pwd" />
                        <input type="hidden" name="act" value="forget_pwd" />
                        <input type="hidden" name="adminid" value="<?php echo $this->_var['adminid']; ?>" />
                        <input type="hidden" name="code" value="<?php echo $this->_var['code']; ?>" />
                        <input type="submit" name="submit" class="sub qp_sub" value="<?php echo $this->_var['lang']['click_button2']; ?>" id="submitBtn1"/>
                    </div>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <div class="backpwd_layout_bg"></div>
    <script type="text/javascript">
		$(function(){
            if("<?php echo $this->_var['form_act']; ?>" == 'forget_pwd' || "<?php echo $this->_var['form_act']; ?>" == 'reset_pwd'){
            	$(".backpwd_layout").show();
				$(".backpwd_layout_bg").show();
            }
			$(".formText .input-text").focus(function(){
				$(this).parent().addClass("focus");
			});
			
			$(".formText .input-text").blur(function(){
				$(this).parent().removeClass("focus");
			});
			
			$(".checkbox").click(function(){
				if($(this).hasClass("checked")){
					$(this).removeClass("checked");
                    $('input[name=remember]').val(0);
				}else{
					$(this).addClass("checked");
                    $('input[name=remember]').val(1);
				}
			});
			
			$(".formText .input-yzm").focus(function(){
				$(this).prev().show();
			});
			
			$(".formText").blur(function(){
				$(this).prev().hide();
			});
			
			$('.submit_span .sub').on('click',function(){
                $('.code').show();
            });
		});
		
		$(function(){
			$('#theForm input[name=submit]').on('click',function(){
                var username=true,password=true,captcha=true;
				var dsc_token = $(":input[name='dsc_token']").val();
				var yzm = $('#theForm input[name=captcha]'),
					seller_name = $('#theForm input[name=username]'),
					pwd = $('#theForm input[name=password]');

                if(seller_name.val() == ''){
                    $('#error').html(jl_user_name_not_null);
                    seller_name.focus();
                    username = false;
                    return false;
                }

                if(pwd.val() == ''){
                    $('#error').html(jl_pwd_not_null);
                    pwd.focus();
                    password = false;
                    return false;
                }

                if(yzm.val() == ''){
                    $('#error').html(jl_verify_not_null);
                    yzm.focus();
                    captcha = false;
                    return false;
                }
				
                if(captcha){
					$.ajax({
						async:false,
						url:'privilege.php?act=signin&type=captcha',
						data:{'captcha':yzm.val(),'dsc_token':dsc_token},
						type:'post',
						success:function(data){
							if(data == 'false'){
								$('#error').html(jl_verify_wrong);
								captcha = false;
								return false;
							}
						}
					});
                }
                if(captcha && seller_name != '' && pwd != ''){
                    $.ajax({
                        async:false,
                        url:'privilege.php?act=signin&type=password',
                        data:{'username':seller_name.val(),'password':pwd.val(),'dsc_token':dsc_token},
                        type:'post',
                        success:function(data){
                            if(data == 'false'){
                                $('#error').html(jl_name_pwd_wrong);
                                $('.code-img img').attr('src','index.php?act=captcha&'+Math.random());
                                username=false;
                                password=false;
                                return false;
                            }
                        }
                    });
                }
				
                if(captcha && username && password){
                    $('#theForm').submit();
                }else{
                    return false;
                }
            });
			
			$(document).click(function(e){
				if(e.target.name !='captcha' && !$(e.target).parents("div").is(".submitDiv")){
					$('.code').hide();
				}
			});
			
			//忘记密码
			$(".forget_pwd").on("click",function(){
            	window.location.href = "get_password.php?act=forget_pwd";
			});
			
			$(".close").on("click",function(){
            	window.location.href = "privilege.php?act=login";
			});
		});
		
		$(function(){
			//表单验证
			$("#submitBtn").click(function(){
				if($("#submitAdmin1").valid()){
					$("#submitAdmin1").submit();
				}
			});

			$('#submitAdmin1').validate({
				errorPlacement:function(error, element){
					var error_div = element.parents('.formText').find('div.form_prompt');
					element.parents('.formText').find(".notic").hide();
					error_div.append(error);
				},
				rules:{
					user_name :{
						required : true
					},
					email :{
						required : true,
						email : true
					}
				},
				messages:{
					user_name :{
						required : '<i class="icon icon-exclamation-sign"></i>'+jl_admin_name_not_null
					},
					email :{
						required : '<i class="icon icon-exclamation-sign"></i>'+jl_email_not_null,
						email : '<i class="icon icon-exclamation-sign"></i>'+jl_email_format_wrong
					}
				}
			});
			
			$("#submitBtn1").click(function(){
				if($("#submitAdmin2").valid()){
					$("#submitAdmin2").submit();
				}
			});

			$('#submitAdmin2').validate({
				errorPlacement:function(error, element){
					var error_div = element.parents('.formText').find('div.form_prompt');
					element.parents('.formText').find(".notic").hide();
					error_div.append(error);
				},
				rules:{
					password :{
						required : true,
						minlength : 6
					},
					confirm_pwd :{
						required : true,
						equalTo:"#password"
					}
				},
				messages:{
					password :{
						required : '<i class="icon icon-exclamation-sign"></i>'+jl_new_pwd_not_null,
						minlength : '<i class="icon icon-exclamation-sign"></i>'+jl_pwd_nolessthan_6
					},
					confirm_pwd :{
						required : '<i class="icon icon-exclamation-sign"></i>'+jl_repwd_not_null,
						equalTo : '<i class="icon icon-exclamation-sign"></i>'+jl_pwd_not_equal
					}
				}
			});
		});
    </script>
</body>
</html>
