<form id = 'signupForm' role = 'form' name = 'signupForm' method = post action = './src/ajax.php'>
<input type = 'hidden' id = 'action' name = 'action' value = 'SIGNUP'>
<section class = 'cAlign' id = 'login-section'>
	<section id = 'separator'><img src = './assets/images/logo.jpg'></section>
	<div id = 'login-div'>
		<section class = 'lAlign'><label for = 'name'>User name</label></section>
		<section><input type = 'text' class = 'form-control' id = 'username' name = 'username'></section>
		<section class = 'lAlign'><label for = 'name'>Password</label></section>
		<section><input type = 'password' class = 'form-control' id = 'password' name = 'password'></section>
		<section class = 'lAlign'><label for = 'name'>Confirm Password</label></section>
		<section><input type = 'password' class = 'form-control' id = 'password1' name = 'password`'></section>
		<section>
			<button type = 'button' class = 'btn btn-primary' onclick = 'javascript:signup();'>SignUp</button>
			<button type = 'button' class = 'btn btn-primary' onclick = 'javascript:login();'>Cancel</button>
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

		$('#signupForm').ajaxForm({
			success: function(ret)
			{
				if ( $.trim(ret) == 'SUCCESS' )
				{
					document.location.href = 'index.php?menu=login';
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
				signup();
			}
		});
	});

	function login()
	{
		document.location.href = 'index.php?menu=login';
	}

	function signup()
	{
		if ( $.trim( $('#username').val() ) == '' )
		{
			alert('Please input user name.');
			return;
		}
		
		if ( $('#password').val() == '' )
		{
			alert('Please input password.');
			return;
		}

		if ( $('#password').val() != $('#password1').val() )
		{
			alert('Please check your password again.');
			return;
		}

		show_loading(true);
		$('#signupForm').submit();
	}
</script>