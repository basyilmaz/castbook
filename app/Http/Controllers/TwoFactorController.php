<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    /**
     * 2FA ayarları sayfası
     */
    public function show(): View
    {
        $user = auth()->user();
        
        return view('auth.two-factor', [
            'enabled' => $user->two_factor_enabled,
            'confirmed' => $user->two_factor_confirmed_at !== null,
        ]);
    }

    /**
     * 2FA'yı etkinleştir
     */
    public function enable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ], [
            'password.required' => 'Şifrenizi girmeniz gerekiyor.',
            'password.current_password' => 'Girdiğiniz şifre yanlış.',
        ]);

        $user = auth()->user();
        
        // Secret key oluştur (Base32 formatında)
        $secret = $this->generateSecret();
        
        // Recovery kodları oluştur
        $recoveryCodes = $this->generateRecoveryCodes();

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
            'two_factor_confirmed_at' => null, // Henüz onaylanmadı
        ]);

        return redirect()
            ->route('two-factor.confirm')
            ->with('status', 'İki faktörlü doğrulama kurulumu başlatıldı. Lütfen onaylayın.');
    }

    /**
     * 2FA onay sayfası
     */
    public function confirmShow(): View
    {
        $user = auth()->user();
        
        if (!$user->two_factor_enabled || $user->two_factor_confirmed_at) {
            return redirect()->route('two-factor.show');
        }

        $secret = decrypt($user->two_factor_secret);
        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        
        // QR kod URL'i (Google Authenticator formatı)
        $qrCodeUrl = $this->generateQrCodeUrl($user->email, $secret);

        return view('auth.two-factor-confirm', [
            'secret' => $secret,
            'qrCodeUrl' => $qrCodeUrl,
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    /**
     * 2FA'yı onayla
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [
            'code.required' => 'Doğrulama kodunu girin.',
            'code.size' => 'Doğrulama kodu 6 haneli olmalıdır.',
        ]);

        $user = auth()->user();
        $secret = decrypt($user->two_factor_secret);
        
        // TOTP doğrulaması
        if (!$this->verifyCode($secret, $request->code)) {
            return back()->withErrors(['code' => 'Geçersiz doğrulama kodu.']);
        }

        $user->update([
            'two_factor_confirmed_at' => now(),
        ]);

        return redirect()
            ->route('two-factor.show')
            ->with('status', 'İki faktörlü doğrulama başarıyla etkinleştirildi! 🔐');
    }

    /**
     * 2FA'yı devre dışı bırak
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ], [
            'password.required' => 'Şifrenizi girmeniz gerekiyor.',
            'password.current_password' => 'Girdiğiniz şifre yanlış.',
        ]);

        $user = auth()->user();
        
        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        return redirect()
            ->route('two-factor.show')
            ->with('status', 'İki faktörlü doğrulama devre dışı bırakıldı.');
    }

    /**
     * Recovery kodlarını yenile
     */
    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = auth()->user();
        
        if (!$user->two_factor_enabled) {
            return back()->withErrors(['2fa' => '2FA etkin değil.']);
        }

        $recoveryCodes = $this->generateRecoveryCodes();
        
        $user->update([
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
        ]);

        return back()->with('recoveryCodes', $recoveryCodes);
    }

    /**
     * 2FA doğrulama sayfası (giriş sırasında)
     */
    public function challenge(): View
    {
        return view('auth.two-factor-challenge');
    }

    /**
     * 2FA doğrulaması yap (giriş sırasında)
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required_without:recovery_code', 'nullable', 'string'],
            'recovery_code' => ['required_without:code', 'nullable', 'string'],
        ]);

        $user = User::find(session('login.id'));
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Normal kod ile doğrulama
        if ($request->filled('code')) {
            $secret = decrypt($user->two_factor_secret);
            
            if (!$this->verifyCode($secret, $request->code)) {
                return back()->withErrors(['code' => 'Geçersiz doğrulama kodu.']);
            }
        }
        // Recovery kod ile doğrulama
        elseif ($request->filled('recovery_code')) {
            $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            
            $code = str_replace(' ', '', $request->recovery_code);
            $index = array_search($code, $recoveryCodes);
            
            if ($index === false) {
                return back()->withErrors(['recovery_code' => 'Geçersiz kurtarma kodu.']);
            }
            
            // Kullanılan kodu kaldır
            unset($recoveryCodes[$index]);
            $user->update([
                'two_factor_recovery_codes' => encrypt(json_encode(array_values($recoveryCodes))),
            ]);
        } else {
            return back()->withErrors(['code' => 'Bir doğrulama yöntemi seçin.']);
        }

        // Oturumu başlat
        session()->forget('login.id');
        auth()->login($user, session('login.remember', false));
        session()->forget('login.remember');
        
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Secret key oluştur
     */
    protected function generateSecret(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        
        for ($i = 0; $i < 16; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }
        
        return $secret;
    }

    /**
     * Recovery kodları oluştur
     */
    protected function generateRecoveryCodes(): array
    {
        $codes = [];
        
        for ($i = 0; $i < 8; $i++) {
            $codes[] = Str::random(5) . '-' . Str::random(5);
        }
        
        return $codes;
    }

    /**
     * TOTP kodunu doğrula
     */
    protected function verifyCode(string $secret, string $code): bool
    {
        $timeSlice = floor(time() / 30);
        
        // Mevcut ve önceki/sonraki time slice'ları kontrol et
        for ($i = -1; $i <= 1; $i++) {
            $calculatedCode = $this->getCode($secret, $timeSlice + $i);
            
            if (hash_equals($calculatedCode, $code)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * TOTP kodu hesapla
     */
    protected function getCode(string $secret, int $timeSlice): string
    {
        $secretKey = $this->base32Decode($secret);
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $time, $secretKey, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $code = (
            ((ord($hash[$offset + 0]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        ) % pow(10, 6);
        
        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Base32 decode
     */
    protected function base32Decode(string $input): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $buffer = 0;
        $length = 0;
        $output = '';
        
        for ($i = 0; $i < strlen($input); $i++) {
            $char = $input[$i];
            $val = strpos($chars, $char);
            
            if ($val === false) continue;
            
            $buffer = ($buffer << 5) | $val;
            $length += 5;
            
            if ($length >= 8) {
                $length -= 8;
                $output .= chr(($buffer >> $length) & 0xFF);
            }
        }
        
        return $output;
    }

    /**
     * QR kod URL'i oluştur
     */
    protected function generateQrCodeUrl(string $email, string $secret): string
    {
        $appName = config('app.name', 'CastBook');
        $issuer = urlencode($appName);
        $account = urlencode($email);
        
        return "otpauth://totp/{$issuer}:{$account}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";
    }
}
