<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome Email</title>
</head>
<body style="margin:0; padding:10; background-color:#f4f6f8; font-family:Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" style="padding:50px 30px;">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.08);">
                <!-- Header -->
                <tr>
                    <td align="center">
                     <div style="padding-top:30px">
                        <img style="width: 60px;" src={{ asset('https://accountcover.com/settings/1766305629.png')  }} alt="Img">
                        <h2 style="margin:0;">Welcome to {{ config('app.name') }} </h2>
                     </div>
                    </td>
                </tr>
                <!-- Body -->
                <tr>
                    <td style="padding:25px; color:#333;">
                        <p style="font-size:15px;">Dear Customer,</p>

                        <p style="font-size:15px;">
                            Your account has been created successfully. Below are your login details:
                        </p>

                        <!-- Credentials Box -->
                        <table width="100%" cellpadding="10" cellspacing="0" style="background:#f8f9fa; border:1px solid #e1e1e1; border-radius:6px; margin:20px 0;">
                            <tr>
                                <td style="font-size:14px;">
                                    <strong>Email</strong><br>
                                    <span style="color:#0d6efd;">{{ $email }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size:14px;">
                                    <strong>Password</strong><br>

                                    <!-- Password Box -->
                                    <table cellpadding="0" cellspacing="0" style="margin-top:6px;">
                                        <tr>
                                            <td style="
                                                background:#ffffff;
                                                padding:10px 14px;
                                                border:1px dashed #0d6efd;
                                                border-radius:4px;
                                                font-size:16px;
                                                letter-spacing:1px;
                                                font-family:monospace;
                                                color:#000;
                                                user-select:all;
                                            ">
                                                {{ $password }}
                                            </td>
                                        </tr>
                                    </table>

                                    <!-- Instruction -->
                                    <small style="color:#666; font-size:12px;">
                                        Tip: Click password once → it will auto-select → press <b>Ctrl+C</b>
                                    </small>
                                </td>
                            </tr>

                        </table>

                        <!-- Login Button -->
                        <p style="text-align:center; margin:30px 0;">
                            <a href=""
                               style="background:#fe9f43; color:#ffffff; padding:12px 22px; text-decoration:none; border-radius:5px; font-size:15px; display:inline-block;">
                                🔐 Login to Your Account
                            </a>
                        </p>

                        <!-- Note -->
                        <p style="font-size:13px; color:#666; background:#fff3cd; padding:10px; border-radius:5px;">
                            ⚠️ <strong>Security Note:</strong> Please change your password after first login.
                        </p>

                        <p style="font-size:14px; margin-top:30px;">
                            Regards,<br>
                            <strong>M Farhan Qasim</strong><br>
                            {{ config('app.name') }}
                        </p>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background:#f1f1f1; padding:12px; text-align:center; font-size:12px; color:#777; border-radius:0 0 8px 8px;">
                        © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
