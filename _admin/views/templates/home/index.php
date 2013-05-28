<div class="login_box">
    <p class="js-error error_message" style="display: none;"></p>
    <form class="js-login-form" method="POST" name="login-form" action="#" enctype="multipart/form-data">
        <dl>
            <dt>Email</dt>
            <dd><input type="text" class="js-username" /></dd>
            <dt>Password</dt>
            <dd><input type="password" class="js-password"/></dd>
            <input type="submit" value="Login" class="login-button"/>
            <ul>
                <li>Remember me <input type="checkbox" name="remember" value="remember"></li>
                <li><a href="#" class="js-forgotten-password-button">Forgotten password</a></li>
            </ul>
        </dl>
    </form>
</div>
<div class="forgotten-form">
    <form method="POST" name="forgotten-password-form" action="#" enctype="multipart/form-data" class="js-forgotten-password">
        <dl>
            <dt>Email</dt>
            <dd><input type="text" class="js-forgot-password-email" class="email_address"/></dd>
        </dl>
        <input type="submit" value="Get new password" class="login-button"/>
    </form>
</div>