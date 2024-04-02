<p>Dear {{ $admin->name }}</p>
<br>
<p>
    Your password on wkshop system was changed successfuly.
    Here is your new login credentials :
    <br>
    <b>Login ID: </b> {{ $admin->email }} or {{ $admin->username }}
    <br>
    <b>Password:</b> {{ $new_password }}
</p>
<br>
<p>
    Please,  keep your login credentials confidential. Your username and password are you own credentials and you should
    never share them with anybody else.

</p>
<br>
<p>
    WKSCHOP will not be liable for any misuse of your login credentials.
</p>
-----------------------------------------------------------------------------
<p>
    This email was sent to you by wkshop system automatically. Do not reply it.
</p>
