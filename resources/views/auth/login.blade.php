@extends('layouts.app')
@section('title', 'Masuk')
@section('content')
<section class="card auth"><h1>Masuk ke RuangKerja</h1><p class="muted">Gunakan akun tim untuk membuka papan proyek.</p>
<form method="post" action="{{ route('login.store') }}" class="form-grid">@csrf
    <div class="field full"><label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username">@error('email')<span class="error">{{ $message }}</span>@enderror</div>
    <div class="field full"><label for="password">Kata sandi</label><input id="password" name="password" type="password" required autocomplete="current-password">@error('password')<span class="error">{{ $message }}</span>@enderror</div>
    <div class="field full"><label><input type="checkbox" name="remember" value="1"> Ingat saya</label></div>
    <div class="field full"><button type="submit">Masuk</button></div>
</form><p class="small muted">@if(config('registration.code'))Belum punya akun? <a href="{{ route('register') }}">Buat akun</a>@else Registrasi saat ini ditutup. @endif</p></section>
@endsection
