<html>
<body>
    <p>Hi {{ $user->profile->first_name ?? $user->email }},</p>

    <p>Welcome! Your account has been created. To secure your account please set your password using the link we sent you.</p>

    @if($resetSent)
        <p>We've sent a password reset link to <strong>{{ $user->email }}</strong>. Check your inbox and follow the instructions to set your password.</p>
    @else
        <p>We couldn't automatically send the password reset email. Please contact support to receive a setup link.</p>
    @endif

    <p>Regards,<br>The Team</p>
</body>
</html>
