<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'role' => ['nullable', Rule::in(['admin', 'catalog', 'player'])]]);
        $search = trim($data['q'] ?? '');
        $role = $data['role'] ?? '';
        $users = User::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query->whereLike('name', '%'.$search.'%')->orWhereLike('email', '%'.$search.'%')))
            ->when($role === 'admin', fn ($query) => $query->where('is_admin', true))
            ->when($role === 'catalog', fn ($query) => $query->where('is_admin', false)->where('is_catalog_manager', true))
            ->when($role === 'player', fn ($query) => $query->where('is_admin', false)->where('is_catalog_manager', false))
            ->orderBy('name')->orderBy('id')->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'role'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => mb_strtolower(trim($request->input('email')))]);
        }
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in(['admin', 'catalog', 'player'])],
        ]);
        DB::transaction(function () use ($request, $data) {
            $this->lockAdministrators($request);
            $user = new User;
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = $data['password'];
            $user->is_admin = $data['role'] === 'admin';
            $user->is_catalog_manager = $data['role'] === 'catalog';
            $user->save();
        });

        return redirect()->route('admin.users.index')->with('status', 'Conta cadastrada.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['role' => ['required', Rule::in(['admin', 'catalog', 'player'])]]);
        DB::transaction(function () use ($request, $user, $data) {
            $this->lockAdministrators($request);
            $target = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($target->id === $request->user()->id && $data['role'] !== 'admin') {
                throw ValidationException::withMessages(['role' => 'Você não pode retirar seu próprio acesso de administrador geral.']);
            }
            $target->is_admin = $data['role'] === 'admin';
            $target->is_catalog_manager = $data['role'] === 'catalog';
            $target->save();
        });

        return redirect()->route('admin.users.index')->with('status', 'Cargo atualizado.');
    }

    private function lockAdministrators(Request $request): void
    {
        User::where('is_admin', true)->orderBy('id')->lockForUpdate()->get();
        abort_unless(User::findOrFail($request->user()->id)->is_admin, 403);
    }
}
