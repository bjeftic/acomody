<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Requests\SuperAdmin\User\UpdateUserRequest;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController
{
    /**
     * Show the application dashboard.
     */
    public function index(Request $request): View
    {
        /** @var string $search */
        $search = $request->search ?? '';

        $usersPaginated = User::with('userProfile')
            ->whereIsSuperadmin(false)
            ->latest('id')
            ->when(! empty($search), function ($q) use ($search) {
                $q->where('email', 'ilike', "%{$search}%")
                    ->orWhereHas('userProfile', function ($q2) use ($search) {
                        $q2->where('first_name', 'ilike', "%{$search}%")
                            ->orWhere('last_name', 'ilike', "%{$search}%");
                    });
            })
            ->paginate(12)
            ->appends($request->only(['search', 'page']));

        return view('super-admin.users.index', [
            'users' => $usersPaginated,
            'search' => $search,
            'page' => $request->page ?? 1,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show($id): View
    {
        $user = User::whereId($id)->firstOrFail();

        $activityLogs = ActivityLog::with(['subject', 'causer'])
            ->forUser($user->id)
            ->latest('id')
            ->limit(20)
            ->get();

        return view('super-admin.users.view')
            ->with('user', $user)
            ->with('activityLogs', $activityLogs);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     */
    public function edit($id): View
    {
        $user = User::findOrFail($id);

        return view('super-admin.users.edit')
            ->with('origin', 'edit')
            ->with('user', $user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     */
    public function update(UpdateUserRequest $request, $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $postData = $request->all();

        if (isset($postData['edit_password'])) {
            $postData['password'] = bcrypt($postData['edit_password']);
        }

        $user->update($postData);

        session()->flash('alert-success', 'User is successfully modified');

        return redirect('/superadmin/users');
    }
}
