<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle(Request $request)
    {
        session([
            'google_login_redirect' => $request->get('redirect', route('home')),
            'google_login_role' => $request->get('role', 'customer'),
            'table_id' => $request->get('table'),
            'invite_token' => $request->get('invite_token'),
        ]);

        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('home')->with('error', 'Failed to login with Google. '.$e->getMessage());
        }

        $email = $googleUser->getEmail();
        $inviteToken = session()->pull('invite_token');
        $invite = $inviteToken ? cache("worker_invite_{$inviteToken}") : null;
        $intendedRole = session()->pull('google_login_role', 'customer');
        $tableCode = session()->pull('table_id');

        $user = User::where('email', $email)->first();

        if (! $user) {
            if ($invite && $invite['email'] === $email) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $email,
                    'restaurant_id' => $invite['restaurant_id'],
                    'password' => bcrypt(\Str::random(16)),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'is_active' => true,
                    'is_onboarded' => true,
                ]);
                $user->assignRole($invite['role']);
                cache()->forget("worker_invite_{$inviteToken}");
            } elseif (in_array($intendedRole, ['customer', 'restaurant'])) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $email,
                    'password' => bcrypt(\Str::random(16)),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'is_active' => true,
                    'is_onboarded' => $intendedRole === 'restaurant',
                ]);
                $user->assignRole($intendedRole);
            } else {
                return redirect()->route('login')->with('error', 'You are not authorized to register without an invite.');
            }
        } else {
            $isPrivilegedUser = $user->hasAnyRole(['restaurant', 'admin', 'staff']);

            if ($isPrivilegedUser && $intendedRole === 'customer') {
                Auth::login($user);

                return redirect()->route('dashboard')
                    ->with('info', 'You are already logged in with a privileged account.');
            }

            if (! $isPrivilegedUser && in_array($intendedRole, ['customer', 'restaurant']) && ! $user->hasRole($intendedRole)) {
                $user->syncRoles([$intendedRole]);
            }

            if ($invite && $invite['email'] === $email && ! $isPrivilegedUser) {
                $user->syncRoles([$invite['role']]);
                if (! $user->restaurant_id && isset($invite['restaurant_id'])) {
                    $user->restaurant_id = $invite['restaurant_id'];
                    $user->save();
                }
                cache()->forget("worker_invite_{$inviteToken}");
            }
        }

        if (is_null($user->google_id)) {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ]);
        }

        if ($googleUser->getAvatar() && $user->getFirstMedia('avatar') === null) {
            $user->addMediaFromUrl($googleUser->getAvatar())->toMediaCollection('avatar');
        }

        Auth::login($user);

        if ($user->hasRole('customer')) {
            return $tableCode
                ? redirect()->route('table.menu', ['table' => $tableCode])
                : redirect()->route('home')->with('error', 'Table information not found. Please scan the QR again.');
        }

        if ($user->hasAnyRole(['restaurant', 'admin', 'staff'])) {
            return $user->is_onboarded
                ? redirect()->route('dashboard')
                : redirect()->route('onboarding.index');
        }

        return redirect()->route('home');
    }
}
