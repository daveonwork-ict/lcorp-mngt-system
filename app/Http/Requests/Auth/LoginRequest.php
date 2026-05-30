<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Services\LoginSecurityService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = $this->string('login')->trim()->toString();
        $password = $this->string('password')->toString();

        $authenticated = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? Auth::attempt(['email' => $login, 'password' => $password], $this->boolean('remember'))
            : Auth::attempt(['username' => $login, 'password' => $password], $this->boolean('remember'));

        if (! $authenticated) {
            $matchedUser = User::query()
                ->when(filter_var($login, FILTER_VALIDATE_EMAIL), fn ($q) => $q->where('email', $login), fn ($q) => $q->where('username', $login))
                ->first();

            app(LoginSecurityService::class)->logFailure($matchedUser?->id, $login, 'Failed login attempt');

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login')).'|'.$this->ip());
    }
}
