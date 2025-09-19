<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>E-Mail-Adresse bestätigen</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <h2 style="color: #4CAF50; text-align: center;">E-Mail-Adresse bestätigen</h2>
        <p>Hallo {{ $user->name ?? 'Benutzer' }},</p>
        <p>Du hast eine Änderung deiner E-Mail-Adresse auf {{ $newEmail }} angefordert. Bitte klicke auf den folgenden Link, um die neue E-Mail-Adresse zu bestätigen:</p>
        <p style="text-align: center;">
            <a href="{{ $verificationUrl }}" style="display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: #fff; text-decoration: none; border-radius: 5px;">E-Mail-Adresse bestätigen</a>
        </p>
        <p>Dieser Link ist 24 Stunden lang gültig. Wenn du diese Änderung nicht angefordert hast, ignoriere diese E-Mail oder kontaktiere unseren Support unter <a href="mailto:business.promptin@gmail.com">business.promptin@gmail.com</a>.</p>
        <p>Mit freundlichen Grüßen,<br>Das PromptIn-Team</p>
    </div>
</body>
</html>