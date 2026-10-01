@extends('layouts.app')

@section('title', 'Welcome')

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
        padding: 30px 36px 36px;
        width: 280px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 18px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    }

    .logo-circle {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 4px;
        width: 220px;
        height: 220px;
        overflow: visible;
    }

    .logo-circle img {
        width: 220px;
        height: 220px;
        object-fit: contain;
        filter: drop-shadow(0 4px 12px rgba(0,0,0,0.25));
    }

    .btn {
        width: 100%;
        padding: 14px 0;
        border-radius: 30px;
        border: none;
        font-size: 0.95rem;
        font-weight: 700;
        letter-spacing: 1.5px;
        cursor: pointer;
        text-decoration: none;
        text-align: center;
        display: block;
        transition: opacity 0.2s, transform 0.1s;
    }

    .btn:hover { opacity: 0.88; transform: translateY(-1px); }
    .btn:active { transform: translateY(0); }

    .btn-login {
        background-color: #b6f5b0;
        color: #1a4d22;
    }
</style>
@endpush

@section('content')
<div class="card">
    <div class="logo-circle">
        <img src="{{ asset('images/logo.svg') }}" alt="NCL Farm Logo">
    </div>

    <a href="{{ route('login') }}" class="btn btn-login">LOG IN</a>
</div>
@endsection
