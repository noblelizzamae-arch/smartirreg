@extends('layouts.app')

@section('title', 'Log In')

@push('styles')
<style>
    body {
        background-color: #8fe828;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .card {
        background-color: #2e9e3e;
        border-radius: 24px;
        padding: 28px 32px 36px;
        width: 340px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0;
        box-shadow: 0 8px 32px rgba(0,0,0,0.18);
        position: relative;
    }

    .back-btn {
        position: absolute;
        top: 18px;
        left: 20px;
        color: #fff;
        font-size: 1.3rem;
        text-decoration: none;
        font-weight: 300;
        line-height: 1;
        opacity: 0.85;
        transition: opacity 0.2s;
    }
    .back-btn:hover { opacity: 1; }

    .logo-circle {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 4px;
        width: 200px;
        height: 200px;
        overflow: visible;
    }

    .logo-circle img {
        width: 200px;
        height: 200px;
        object-fit: contain;
        filter: drop-shadow(0 4px 12px rgba(0,0,0,0.25));
    }

    .form-group {
        width: 100%;
        margin-bottom: 14px;
    }

    .form-group label {
        display: block;
        color: #1a4d22;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 1px;
        margin-bottom: 5px;
        text-transform: uppercase;
    }

    .form-group input {
        width: 100%;
        background-color: #b6f5b0;
        border: none;
        border-radius: 20px;
        padding: 12px 16px;
        font-size: 0.95rem;
        color: #1a4d22;
        outline: none;
        transition: box-shadow 0.2s;
    }

    .form-group input:focus {
        box-shadow: 0 0 0 2px #fff;
    }

    .form-group input::placeholder { color: #5a9c65; }

    .error-msg {
        width: 100%;
        background: #ffe4e4;
        color: #b91c1c;
        border-radius: 10px;
        padding: 8px 14px;
        font-size: 0.82rem;
        margin-bottom: 10px;
    }

    .btn-login {
        margin-top: 10px;
        width: 100%;
        padding: 13px 0;
        border-radius: 30px;
        border: none;
        background-color: #b6f5b0;
        color: #1a4d22;
        font-size: 0.95rem;
        font-weight: 700;
        letter-spacing: 1.5px;
        cursor: pointer;
        transition: opacity 0.2s, transform 0.1s;
    }

    .btn-login:hover  { opacity: 0.88; transform: translateY(-1px); }
    .btn-login:active { transform: translateY(0); }
</style>
@endpush

@section('content')
<div class="card">
    <a href="{{ route('landing') }}" class="back-btn">&#8592;</a>

    <div class="logo-circle">
        <img src="{{ asset('images/logo.svg') }}" alt="NCL Farm Logo">
    </div>

    @if ($errors->any())
        <div class="error-msg">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}" style="width:100%">
        @csrf

        <div class="form-group">
            <label for="username">User Name</label>
            <input
                type="text"
                id="username"
                name="username"
                value="{{ old('username') }}"
                placeholder="Enter username or email"
                autocomplete="username"
                required
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter password"
                autocomplete="current-password"
                required
            >
        </div>

        <button type="submit" class="btn-login">LOG IN</button>
    </form>
</div>
@endsection
