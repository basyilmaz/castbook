<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Sıfırlama - {{ config('app.name', 'CastBook') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 24px;">
            {{ config('app.name', 'CastBook') }}
        </h1>
        <p style="color: rgba(255,255,255,0.8); margin: 10px 0 0 0;">
            Şifre Sıfırlama Talebi
        </p>
    </div>
    
    <div style="background: #f8f9fa; padding: 30px; border: 1px solid #e9ecef; border-top: none; border-radius: 0 0 10px 10px;">
        <p>Merhaba <strong>{{ $user->name }}</strong>,</p>
        
        <p>Hesabınız için bir şifre sıfırlama talebi aldık. Şifrenizi sıfırlamak için aşağıdaki butona tıklayın:</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $resetUrl }}" 
               style="display: inline-block; background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold;">
                🔐 Şifreyi Sıfırla
            </a>
        </div>
        
        <p style="color: #666; font-size: 14px;">
            <strong>⏰ Önemli:</strong> Bu link <strong>{{ $expireMinutes }} dakika</strong> içinde geçerliliğini yitirecektir.
        </p>
        
        <p style="color: #666; font-size: 14px;">
            Eğer bu talebi siz oluşturmadıysanız, bu e-postayı görmezden gelebilirsiniz. Hesabınız güvende.
        </p>
        
        <hr style="border: none; border-top: 1px solid #e9ecef; margin: 20px 0;">
        
        <p style="color: #999; font-size: 12px; text-align: center;">
            Bu e-posta {{ config('app.name', 'CastBook') }} tarafından otomatik olarak gönderilmiştir.<br>
            Lütfen bu e-postayı yanıtlamayın.
        </p>
        
        <p style="color: #999; font-size: 11px; text-align: center;">
            Buton çalışmıyorsa, aşağıdaki linki tarayıcınıza yapıştırın:<br>
            <a href="{{ $resetUrl }}" style="color: #2563eb; word-break: break-all;">{{ $resetUrl }}</a>
        </p>
    </div>
</body>
</html>
