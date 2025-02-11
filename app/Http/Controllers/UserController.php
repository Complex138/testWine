<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Models\Wine;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('favoriteWine')
            ->select('id', 'name', 'phone', 'birth_date', 'address', 'favorite_wine_id', 'is_draft')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Users/Index', [
            'users' => $users
        ]);
    }

    public function create()
    {
        $wines = Wine::select('id', 'name')->get();

        return Inertia::render('Users/Create', [
            'wines' => $wines
        ]);
    }

    public function store(UserRequest $request)
    {
        try {
            Log::info('Попытка создания пользователя', $request->validated());

            $user = User::create($request->validated());

            Log::info('Пользователь создан', ['id' => $user->id]);

            return redirect()->route('users.index')
                ->with('message', 'Пользователь успешно создан');
        } catch (\Exception $e) {
            Log::error('Ошибка создания пользователя', [
                'message' => $e->getMessage(),
                'data' => $request->validated()
            ]);

            return back()
                ->withErrors(['error' => 'Ошибка при создании пользователя: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function edit(User $user)
    {
        $wines = Wine::select('id', 'name')->get();

        return Inertia::render('Users/Edit', [
            'user' => $user->load('favoriteWine'),
            'wines' => $wines
        ]);
    }

    public function update(UserRequest $request, User $user)
    {
        $user->update($request->validated());

        return redirect()->route('users.index')
            ->with('message', 'Пользователь успешно обновлен');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')
            ->with('message', 'Пользователь успешно удален');
    }
}
