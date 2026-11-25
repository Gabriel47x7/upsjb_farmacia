<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Muestra el formulario de inicio de sesión
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Intenta autenticar al usuario con correo y contraseña
    public function login(Request $request)
    {
        // Valida las credenciales antes de consultar al usuario
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember');

        // Auth::attempt verifica el correo y la contraseña usando el guard web
        if (Auth::attempt($credentials, $remember)) {
            // Regenera el identificador de sesión para evitar fijación de sesión
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard.index'));
        }

        // Conserva solo el correo cuando las credenciales son incorrectas
        return back()
            ->withErrors(['email' => 'El correo o la contraseña no son correctos.'])
            ->onlyInput('email');
    }

    // Cierra la sesión y protege el estado actual contra reutilización
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login')->with('ok', 'Cerraste sesión correctamente.');
    }
}
