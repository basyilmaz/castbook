<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /**
     * Şifremi unuttum formunu göster
     */
    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Şifre sıfırlama e-postası gönder
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'E-posta adresi gereklidir.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Bu e-posta adresi kayıtlı değil.']);
        }

        if (!$user->is_active) {
            return back()->withErrors(['email' => 'Hesabınız pasif durumda. Lütfen yöneticinizle iletişime geçin.']);
        }

        // Token oluştur
        $token = Str::random(64);

        // Eski token'ları sil
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Yeni token kaydet
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // E-posta gönder
        $resetUrl = route('password.reset', ['token' => $token, 'email' => $request->email]);
        
        Mail::send('emails.password-reset', [
            'user' => $user,
            'resetUrl' => $resetUrl,
            'expireMinutes' => config('auth.passwords.users.expire', 60),
        ], function ($message) use ($user) {
            $message->to($user->email, $user->name)
                    ->subject('Şifre Sıfırlama Talebi - ' . config('app.name'));
        });

        return back()->with('status', 'Şifre sıfırlama linki e-posta adresinize gönderildi.');
    }

    /**
     * Şifre sıfırlama formunu göster
     */
    public function showResetForm(Request $request, string $token): View|RedirectResponse
    {
        $email = $request->query('email');

        if (!$email) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Geçersiz sıfırlama linki.']);
        }

        // Token geçerliliğini kontrol et
        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Şifre sıfırlama talebi bulunamadı. Lütfen yeni bir talep oluşturun.']);
        }

        if (!Hash::check($token, $record->token)) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Geçersiz sıfırlama linki.']);
        }

        // Token süresi dolmuş mu kontrol et (varsayılan 60 dakika)
        $expireMinutes = config('auth.passwords.users.expire', 60);
        $createdAt = \Carbon\Carbon::parse($record->created_at);
        
        if ($createdAt->addMinutes($expireMinutes)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Şifre sıfırlama linkinizin süresi dolmuş. Lütfen yeni bir talep oluşturun.']);
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Şifreyi güncelle
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ], [
            'email.required' => 'E-posta adresi gereklidir.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
            'password.required' => 'Şifre gereklidir.',
            'password.min' => 'Şifre en az 8 karakter olmalıdır.',
            'password.confirmed' => 'Şifreler eşleşmiyor.',
        ]);

        // Token'ı doğrula
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'Geçersiz sıfırlama linki.']);
        }

        // Token süresi dolmuş mu kontrol et
        $expireMinutes = config('auth.passwords.users.expire', 60);
        $createdAt = \Carbon\Carbon::parse($record->created_at);
        
        if ($createdAt->addMinutes($expireMinutes)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Şifre sıfırlama linkinizin süresi dolmuş. Lütfen yeni bir talep oluşturun.']);
        }

        // Kullanıcıyı bul
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Kullanıcı bulunamadı.']);
        }

        // Şifreyi güncelle
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Token'ı sil
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')
            ->with('status', 'Şifreniz başarıyla güncellendi. Yeni şifrenizle giriş yapabilirsiniz.');
    }
}
