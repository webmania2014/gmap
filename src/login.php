<form id = 'loginForm' role = 'form' name = 'loginForm' method = post action = './src/ajax.php'>
<input type = 'hidden' id = 'action' name = 'action' value = 'LOGIN'>
<section class = 'cAlign' id = 'login-section'>
	<section id = 'separator'><img src = './assets/images/logo.jpg'></section>
	<div id = 'login-div'>
		<section class = 'lAlign'><label for = 'name'>User name</label></section>
		<section><input type = 'text' class = 'form-control' id = 'username' name = 'username'></section>
		<section class = 'lAlign'><label for = 'name'>Password</label></section>
		<section><input type = 'password' class = 'form-control' id = 'password' name = 'password'></section>
		<section>
			<button type = 'button' class = 'btn btn-primary' onclick = 'javascript:login();'>LogIn</button>
			<button type = 'button' class = 'btn btn-primary' onclick = 'javascript:signup();'>SignUp</button>
		</section>
	</div>
</section>
</form>
<script>
	$(document).ready(function(){
/*
		//get login form height.
		var login_height = $('#login-div').height() + 444;
		var page_height = $(window).height();

		$('#separator').css('margin-top', ( page_height - login_height ) / 2);
*/
		$('#loginForm').ajaxForm({
			success: function(ret)
			{
				if ( $.trim(ret) == 'SUCCESS' )
				{
					document.location.href = 'index.php';
				}
				else
				{
					alert(ret);
					show_loading(false);
				}
			}
		});

		$('input').keypress(function(e){
			var key = e.which || e.keyCode;
			if ( key == 13 )
			{
				login();
			}
		});
	});

	function login()
	{
		show_loading(true);
		$('#loginForm').submit();
	}

	function signup()
	{
		document.location.href = 'index.php?menu=signup';
	}
</script>