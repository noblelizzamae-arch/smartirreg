@extends('layouts.app')

@section('title', 'Forgot Password')

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
        opacity: 0.85;
        transition: opacity 0.2s;
    }
    .back-btn:hover { opacity: 1; }

    .logo-circle {
        background: #fff;
        border-radius: 50%;
        width: 90px;
        height: 90px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        font-weight: 700;
        color: #222;
        letter-spacing: 1px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.10);
    }

    h2 {
        color: #fff;
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: 1px;
        margin-bottom: 6px;
        text-transform: uppercase;
    }

    p.sub {
        color: #d4f5d0;
        font-size: 0.82rem;
        text-align: center;
        margin-bottom: 20px;
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

    .form-group input:focus { box-shadow: 0 0 0 2px #fff; }

    .success-msg {
        width: 100%;
        background: #d1fae5;
        color: #065f46;
        border-radius: 10px;
        padding: 8px 14px;
        font-size: 0.82rem;
        margin-bottom: 10px;
        text-align: center;
    }

    .btn-submit {
        margin-top: 6px;
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
    .btn-submit:hover  { opacity: 0.88; transform: translateY(-1px); }
    .btn-submit:active { transform: translateY(0); }
</style>
@endpush

@section('content')
<div class="card">
    <a href="{{ route('landing') }}" class="back-btn">&#8592;</a>

    <div class="logo-circle">LOGO</div>

    <h2>Forgot Password</h2>
    <p class="sub">Enter your email and we'll send a reset link.</p>

    @if (session('status'))
        <div class="success-msg">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" style="width:100%">
        @csrf
        <div class="form-group">
            <label for="email">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="Enter your email"
                required
            >
        </div>
        <button type="submit" class="btn-submit">SEND RESET LINK</button>
    </form>
</div>
@endsection
