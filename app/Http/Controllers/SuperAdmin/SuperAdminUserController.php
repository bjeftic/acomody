<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Requests\SuperAdmin\SuperAdminUser\StoreSuperAdminUserRequest;
use App\Http\Requests\SuperAdmin\SuperAdminUser\UpdateSuperAdminUserRequest;
use App\Mail\SuperAdmin\SuperAdminInvitationMail;
use App\Mail\SuperAdmin\SuperAdminPasswordResetMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SuperAdminUserController
{
    public function index(Request $request): View
    {
        /** @var string $search */
        $search = $request->search ?? '';

        $superadminsPaginated = User::whereIsSuperadmin(true)
            ->latest('id')
            ->when(! empty($search), function ($q) use ($search) {
                $q->where('email', 'ilike', "%{$search}%");
            })
            ->paginate(12)
            ->appends($request->only(['search', 'page']));

        return view('super-admin.superadmin-users.index', [
            'superadmins' => $superadminsPaginated,
            'search' => $search,
            'page' => $request->page ?? 1,
        ]);
    }

    public function create(): View
    {
        return view('super-admin.superadmin-users.create');
    }

    public function store(StoreSuperAdminUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'email' => $request->validated('email'),
            'password' => bcrypt(Str::random(32)),
            'is_superadmin' => true,
            'email_verified_at' => now(),
            'status' => 'active',
            'terms_accepted_at' => now(),
            'privacy_policy_accepted_at' => now(),
        ]);

        $token = Password::broker()->createToken($user);
        $setPasswordUrl = route('admin.set-password', [
            'token' => $token,
            'email' => $user->email,
        ]);

        Mail::to($user->email)->queue(new SuperAdminInvitationMail($user, $setPasswordUrl));

        return redirect()
            ->route('admin.superadmin-users.index')
            ->with('alert-success', "Superadmin created. An invitation email has been sent to {$user->email}.");
    }

    public function edit($id): View
    {
        $superadmin = User::whereIsSuperadmin(true)->findOrFail($id);

        return view('super-admin.superadmin-users.edit', compact('superadmin'));
    }

    public function update(UpdateSuperAdminUserRequest $request, $id): RedirectResponse
    {
        $superadmin = User::whereIsSuperadmin(true)->findOrFail($id);

        $superadmin->update(['email' => $request->validated('email')]);

        return redirect()
            ->route('admin.superadmin-users.index')
            ->with('alert-success', 'Superadmin email updated successfully.');
    }

    public function resetPassword($id): RedirectResponse
    {
        $superadmin = User::whereIsSuperadmin(true)->findOrFail($id);

        $token = Password::broker()->createToken($superadmin);
        $resetUrl = route('admin.set-password', [
            'token' => $token,
            'email' => $superadmin->email,
        ]);

        Mail::to($superadmin->email)->queue(new SuperAdminPasswordResetMail($superadmin, $resetUrl));

        return redirect()
            ->route('admin.superadmin-users.index')
            ->with('alert-success', "Password reset email sent to {$superadmin->email}.");
    }

    public function destroy($id): RedirectResponse
    {
        $superadmin = User::whereIsSuperadmin(true)->findOrFail($id);

        if ($superadmin->id === userOrFail()->id) {
            return redirect()
                ->route('admin.superadmin-users.index')
                ->with('alert-danger', 'You cannot delete your own account.');
        }

        $superadmin->delete();

        return redirect()
            ->route('admin.superadmin-users.index')
            ->with('alert-success', 'Superadmin user deleted.');
    }
}
