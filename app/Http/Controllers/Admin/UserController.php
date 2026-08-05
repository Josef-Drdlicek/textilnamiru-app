<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Správa účtů — registrace není self-service, účty zakládá výhradně dílna.
 * Účty se nemažou, jen deaktivují (zakázky na ně dál odkazují).
 */
class UserController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('admin.users.index', [
            'users' => User::query()
                ->withCount('orders')
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $user = User::create([
            ...$request->safe()->except('is_admin'),
            'is_admin' => $request->boolean('is_admin'),
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', "Účet „{$user->username}“ byl založen.");
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', ['user' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = [
            ...$request->safe()->except(['password', 'is_admin']),
            'is_admin' => $request->boolean('is_admin'),
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->string('password')->toString();
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('status', "Účet „{$user->username}“ byl uložen.");
    }

    /**
     * Přepnutí aktivity účtu (deaktivace/aktivace).
     */
    public function toggleActive(User $user): RedirectResponse
    {
        $this->authorize('deactivate', $user);

        $user->update(['is_active' => ! $user->is_active]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', $user->is_active
                ? "Účet „{$user->username}“ byl aktivován."
                : "Účet „{$user->username}“ byl deaktivován.");
    }
}
