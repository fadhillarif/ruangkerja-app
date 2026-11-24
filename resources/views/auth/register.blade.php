@extends('layouts.app')
@section('title', 'Buat akun')
@section('content')
<section class="card auth"><h1>Buat akun tim</h1><p class="muted">Akun baru bergabung ke Seksi Aplikasi, Siber dan Statistik.</p>
<form method="post" action="{{ route('register.store') }}" class="form-grid">@csrf
    <div class="field full"><label for="name">Nama</label><input id="name" name="name" value="{{ old('name') }}" required autocomplete="name">@error('name')<span class="error">{{ $message }}</span>@enderror</div>
    <div class="field full"><label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">@error('email')<span class="error">{{ $message }}</span>@enderror</div>
    <div class="field full"><label for="registration_code">Kode undangan</label><input id="registration_code" name="registration_code" required autocomplete="off">@error('registration_code')<span class="error">Kode undangan tidak valid.</span>@enderror</div>
    <div class="field full"><label for="password">Kata sandi (minimal 10 karakter)</label><input id="password" name="password" type="password" required autocomplete="new-password">@error('password')<span class="error">{{ $message }}</span>@enderror</div>
    <div class="field full"><label for="password_confirmation">Ulangi kata sandi</label><input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"></div>
    <div class="field full"><button type="submit">Buat akun</button></div>
</form><p class="small muted">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p></section>
@endsection
