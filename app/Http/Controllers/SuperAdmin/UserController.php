<?php

namespace App\Http\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Requests\SuperAdmin\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UserController
{
    /**
     * Show the application dashboard.
     *
     * @return View
     */
    public function index(Request $request): View
    {
        /** @var string $search */
        $search = $request->search ?? '';

        $usersPaginated = User::whereIsSuperadmin(false)
            ->latest('id')
            ->when(!empty($search), function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
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
     * @param int $id
     * @return View
     */
    public function show($id): View
    {
        $user = User::whereId($id)->firstOrFail();

        return view('super-admin.users.view')
            ->with('user', $user);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return View
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
     * @param UpdateUserRequest $request
     * @param int $id
     * @return RedirectResponse
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

