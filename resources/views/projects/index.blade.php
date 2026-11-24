@extends('layouts.app')
@section('title', 'Portofolio Proyek')

@section('content')
<div class="row page-heading">
    <div>
        <p class="small muted">PORTOFOLIO DEPARTEMEN</p>
        <h1>{{ $department->name }}</h1>
        <p class="muted">Pantau progres, tenggat, dan tugas lintas proyek.</p>
    </div>
    <span class="pill">{{ $projectCount }} proyek</span>
</div>

<section class="grid summary-grid" aria-label="Ringkasan departemen">
    <div class="card"><span class="muted small">Proyek aktif</span><strong class="summary-number">{{ $activeProjectCount }}</strong></div>
    <div class="card"><span class="muted small">Progres seluruh tugas</span><strong class="summary-number">{{ $overallProgress }}%</strong><span class="small muted">{{ $completedCount }} dari {{ $taskCount }} tugas selesai</span></div>
    <div class="card"><span class="muted small">Tugas terhambat</span><strong class="summary-number">{{ $blockedCount }}</strong></div>
</section>

<section class="card section-card">
    <div class="row section-heading"><div><h2>Semua proyek</h2><p class="small muted">{{ $projects->count() }} proyek ditampilkan</p></div></div>
    <form method="get" action="{{ route('projects.index') }}" class="filters" aria-label="Filter proyek">
        <div class="field"><label for="filter-q">Cari proyek</label><input id="filter-q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama atau deskripsi"></div>
        <div class="field"><label for="filter-status">Status</label><select id="filter-status" name="status"><option value="">Semua status</option>@foreach(['active'=>'Aktif','paused'=>'Ditunda','completed'=>'Selesai'] as $value=>$label)<option value="{{ $value }}" @selected(($filters['status'] ?? '')===$value)>{{ $label }}</option>@endforeach</select></div>
        <div class="field"><label for="filter-priority">Prioritas</label><select id="filter-priority" name="priority"><option value="">Semua prioritas</option>@foreach(['high'=>'Tinggi','medium'=>'Sedang','low'=>'Rendah'] as $value=>$label)<option value="{{ $value }}" @selected(($filters['priority'] ?? '')===$value)>{{ $label }}</option>@endforeach</select></div>
        <div class="field"><label for="filter-owner">Pemilik</label><select id="filter-owner" name="owner_id"><option value="">Semua pemilik</option>@foreach($department->members as $member)<option value="{{ $member->id }}" @selected((string)($filters['owner_id'] ?? '')===(string)$member->id)>{{ $member->name }}</option>@endforeach</select></div>
        <label class="checkbox-field"><input type="checkbox" name="overdue" value="1" @checked(($filters['overdue'] ?? '')==='1')> Tenggat lewat</label>
        <div class="filter-actions"><button type="submit">Terapkan filter</button><a class="button secondary" href="{{ route('projects.index') }}">Reset</a></div>
    </form>

    @if($projects->isEmpty())
        <div class="empty-state"><h3>Belum ada proyek yang cocok</h3><p class="muted">Ubah filter atau tambahkan proyek baru.</p></div>
    @else
        <div class="grid project-grid">
            @foreach($projects as $project)
                @php($progress = $project->tasks_count > 0 ? (int) round($project->completed_tasks_count / $project->tasks_count * 100) : 0)
                @php($overdue = $project->status !== 'completed' && $project->due_at && $project->due_at->lt(today()))
                <article class="card project-card">
                    <a class="project-link" href="{{ route('projects.show', $project) }}">
                        <div class="row"><h3>{{ $project->name }}</h3><span class="pill">{{ ['active'=>'Aktif','paused'=>'Ditunda','completed'=>'Selesai'][$project->status] }}</span></div>
                        <p class="muted project-description">{{ $project->description ?: 'Belum ada deskripsi.' }}</p>
                        <div class="progress-label"><span>Progres</span><strong>{{ $progress }}%</strong></div>
                        <div class="progress-track" role="progressbar" aria-label="Progres {{ $project->name }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progress }}"><span style="width: {{ $progress }}%"></span></div>
                        <div class="stats">
                            <span class="pill">{{ $project->completed_tasks_count }}/{{ $project->tasks_count }} tugas selesai</span>
                            @if($project->blocked_tasks_count)<span class="pill amber">{{ $project->blocked_tasks_count }} terhambat</span>@endif
                            @if($overdue)<span class="pill red">Tenggat lewat</span>@endif
                            @if($project->due_at)<span class="pill">Tenggat {{ $project->due_at->format('d M Y') }}</span>@endif
                        </div>
                        <span class="small muted">Pemilik: {{ $project->owner?->name ?? 'Belum ditetapkan' }}</span>
                    </a>

                    @if($canManageProjects)
                        <details class="edit-project">
                            <summary>Edit detail proyek</summary>
                            <form method="post" action="{{ route('projects.update', $project) }}" class="form-grid compact-form">
                                @csrf @method('PUT')
                                <div class="field full"><label for="project-name-{{ $project->id }}">Nama proyek</label><input id="project-name-{{ $project->id }}" name="name" value="{{ $project->name }}" required maxlength="160"></div>
                                <div class="field full"><label for="project-description-{{ $project->id }}">Deskripsi</label><textarea id="project-description-{{ $project->id }}" name="description" maxlength="5000">{{ $project->description }}</textarea></div>
                                <div class="field"><label for="project-status-{{ $project->id }}">Status</label><select id="project-status-{{ $project->id }}" name="status">@foreach(['active'=>'Aktif','paused'=>'Ditunda','completed'=>'Selesai'] as $value=>$label)<option value="{{ $value }}" @selected($project->status===$value)>{{ $label }}</option>@endforeach</select></div>
                                <div class="field"><label for="project-priority-{{ $project->id }}">Prioritas</label><select id="project-priority-{{ $project->id }}" name="priority">@foreach(['high'=>'Tinggi','medium'=>'Sedang','low'=>'Rendah'] as $value=>$label)<option value="{{ $value }}" @selected($project->priority===$value)>{{ $label }}</option>@endforeach</select></div>
                                <div class="field"><label for="project-due-{{ $project->id }}">Tenggat</label><input id="project-due-{{ $project->id }}" name="due_at" type="date" value="{{ $project->due_at?->format('Y-m-d') }}"></div>
                                <div class="field"><label for="project-owner-{{ $project->id }}">Pemilik</label><select id="project-owner-{{ $project->id }}" name="owner_id"><option value="">Belum ditetapkan</option>@foreach($department->members as $member)<option value="{{ $member->id }}" @selected($project->owner_id===$member->id)>{{ $member->name }}</option>@endforeach</select></div>
                                <div class="field full"><button type="submit">Simpan perubahan</button></div>
                            </form>
                        </details>
                    @endif
                </article>
            @endforeach
        </div>
    @endif
</section>

@if($canManageProjects)
<section class="card section-card">
    <h2>Tambah proyek</h2>
    <form method="post" action="{{ route('projects.store') }}" class="form-grid">
        @csrf
        <div class="field"><label for="new-project-name">Nama proyek</label><input id="new-project-name" name="name" required maxlength="160" value="{{ old('name') }}">@error('name')<span class="error">{{ $message }}</span>@enderror</div>
        <div class="field"><label for="new-project-due">Tenggat</label><input id="new-project-due" name="due_at" type="date" value="{{ old('due_at') }}"></div>
        <div class="field"><label for="new-project-status">Status</label><select id="new-project-status" name="status">@foreach(['active'=>'Aktif','paused'=>'Ditunda','completed'=>'Selesai'] as $value=>$label)<option value="{{ $value }}" @selected(old('status', 'active')===$value)>{{ $label }}</option>@endforeach</select></div>
        <div class="field"><label for="new-project-priority">Prioritas</label><select id="new-project-priority" name="priority">@foreach(['high'=>'Tinggi','medium'=>'Sedang','low'=>'Rendah'] as $value=>$label)<option value="{{ $value }}" @selected(old('priority', 'medium')===$value)>{{ $label }}</option>@endforeach</select></div>
        <div class="field"><label for="new-project-owner">Pemilik</label><select id="new-project-owner" name="owner_id"><option value="">Saya (default)</option>@foreach($department->members as $member)<option value="{{ $member->id }}" @selected((string)old('owner_id')===(string)$member->id)>{{ $member->name }}</option>@endforeach</select></div>
        <div class="field full"><label for="new-project-description">Deskripsi</label><textarea id="new-project-description" name="description" maxlength="5000">{{ old('description') }}</textarea></div>
        <div class="field full"><button type="submit">Simpan proyek</button></div>
    </form>
</section>
@endif
@endsection
