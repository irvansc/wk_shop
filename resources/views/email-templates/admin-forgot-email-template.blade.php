<p>Dear {{ $admin->name }}</p>
<p>
    We are received a request to reset your password for wkshop account associated with {{ $admin->email }}
    <br>
    Please click on the button below to reset your password :
<br>
    <a href="{{ $actionLink }}" target="_blank"
    style="color: #fff; border-color: #3490dc; background-color: #3490dc;border-style: solid;border-width:5px 10px;
    display: inline-block;text-decoration: none;border-radius:3px;box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16);-webkit-text-size-adjust:none;box-sizing:border-box"
    >Reset Password</a>
    <br>
    <b>NB:</b> This link will expire in 10 minutes
    <br>
    If you did not request a password reset, please ignore this email.
</p>
