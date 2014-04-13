</div>
<div class="bottomimg"></div>

</div>

<div id="footer">Copyright &copy; <?php echo date(Y); ?> SeedofAndromeda.com</div>

<!-- AddThis Smart Layers BEGIN -->

<!-- Go to http://www.addthis.com/get/smart-layers to customize -->

<!--<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=xa-52ad9c1a1c64f85f"></script>
<script type="text/javascript">
  addthis.layers({
    'theme' : 'transparent',
    'share' : {
      'position' : 'right',
      'numPreferredServices' : 5
    }   
  });
</script>-->

<!-- AddThis Smart Layers END -->

<div class="cover registration-cover" style="display: none;">

	<div class="registration-form">

		<div class="close"></div>

		<div class="registration-outer">

			<h2>Create Account</h2>

			<form name="create" action="/Register_Function.php" method="post">

				<table>

					<tr>

						<td><h4>First Name*</h4></td>

						<td><input type="text" maxlength="40" id="reg_first_name"
							name="reg_first_name" placeholder="First Name" /></td>

					</tr>

					<tr>

						<td><h4>Last Name*</h4></td>

						<td><input type="text" maxlength="40" id="reg_last_name"
							name="reg_last_name" placeholder="Last Name" /></td>

					</tr>

					<tr>

						<td><h4>Email Address*</h4></td>

						<td><input type="text" maxlength="100" id="reg_email"
							name="reg_email" placeholder="Email" /></td>

					</tr>

					<tr>

						<td><h4>User Name*</h4></td>

						<td><input type="text" maxlength="40" id="reg_username"
							name="reg_username" placeholder="User Name" /></td>

					</tr>

					<tr>

						<td><h4>Password*</h4></td>

						<td><input type="password" maxlength="40" id="reg_password"
							name="reg_password" placeholder="Password" /></td>

					</tr>

					<tr>

						<td><h4>Confirm Password*</h4></td>

						<td><input type="password" maxlength="40"
							id="reg_confirm_password" name="reg_confirm_password"
							placeholder="Password" /></td>

					</tr>

					<tr>

						<td></td>

						<td><input style="float: right;" type="button"
							onClick="validate_create();" value="Create Account" /></td>

					</tr>

				</table>
			</form>

		</div>

	</div>

</div>

</body>
</html>