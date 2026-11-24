@extends('layouts.app')
@section('title', 'Portofolio Proyek')
@section('content')
<div class="row"><div><p class="small muted">PORTOFOLIO DEPARTEMEN</p><h1 style="margin:0">{{ $department->name }}</h1><p class="muted">Pantau progres, tenggat, dan tugas lintas proyek.</p></div><span class="pill">{{ $department->projects->count() }} proyek</span></div>
<div class="grid" style="margin:20px 0">@php($active = $department->projects->where('status', 'active')->count()) @php($blocked = $department->projects->sum(fn ($project) => $project->tasks->where('status', 'blocked')->count())) @php($done = $department->projects->sum(fn ($project) => $project->tasks->where('status', 'done')->count()))
    <div class="card"><span class="muted small">Proyek aktif</span><h2>{{ $active }}</h2></div><div class="card"><span class="muted small">Tugas terhambat</span><h2>{{ $blocked }}</h2></div><div class="card"><span class="muted small">Tugas selesai</span><h2>{{ $done }}</h2></div>
</div>
<section class="card"><div class="row"><h2>Semua proyek</h2><span class="small muted">Kelola dan buka papan kerja</span></div>
@if($department->projects->isEmpty())<p class="muted">Belum ada proyek. Tambahkan proyek pertama di bawah.</p>@else
<div class="grid">@foreach($department->projects as $project)<a class="card" href="{{ route('projects.show', $project) }}"><div class="row"><h3>{{ $project->name }}</h3><span class="pill">{{ ['active'=>'Aktif','paused'=>'Ditunda','completed'=>'Selesai'][$project->status] }}</span></div><p class="muted">{{ $project->description ?: 'Belum ada deskripsi.' }}</p><div class="stats"><span class="pill">{{ $project->tasks->count() }} tugas</span><span class="pill amber">{{ $project->tasks->where('status','blocked')->count() }} terhambat</span>@if($project->due_at)<span class="pill">Tenggat {{ $project->due_at->format('d M Y') }}</span>@endif</div><span class="small muted">Pemilik: {{ $project->owner?->name ?? 'Belum ditetapkan' }}</span></a>@endforeach</div>
@endif</section>
@if(auth()->user()->departments()->wherePivotIn('role',['owner','leader'])->exists())
<section class="card" style="margin-top:16px"><h2>Tambah proyek</h2><form method="post" action="{{ route('projects.store') }}" class="form-grid">@csrf
    <div class="field"><label for="name">Nama proyek</label><input id="name" name="name" required maxlength="160">@error('name')<span class="error">{{ $message }}</span>@enderror</div>
    <div class="field"><label for="due_at">Tenggat</label><input id="due_at" name="due_at" type="date"></div>
    <div class="field"><label for="status">Status</label><select id="status" name="status"><option value="active">Aktif</option><option value="paused">Ditunda</option><option value="completed">Selesai</option></select></div>
    <div class="field"><label for="priority">Prioritas</label><select id="priority" name="priority"><option value="medium">Sedang</option><option value="high">Tinggi</option><option value="low">Rendah</option></select></div>
    <div class="field full"><label for="description">Deskripsi</label><textarea id="description" name="description"></textarea></div><div class="field full"><button type="submit">Simpan proyek</button></div>
</form></section>@endif
@endsection
