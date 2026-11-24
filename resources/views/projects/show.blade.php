@extends('layouts.app')
@section('title', $project->name)

@section('content')
<p class="small"><a href="{{ route('projects.index') }}">← Portofolio</a> / {{ $project->department->name }}</p>
<div class="row page-heading">
    <div><p class="small muted">PAPAN TUGAS</p><h1>{{ $project->name }}</h1><p class="muted">{{ $project->description ?: 'Tambahkan tugas dan perbarui status pekerjaan.' }}</p></div>
    <span class="pill">{{ ['active'=>'Aktif','paused'=>'Ditunda','completed'=>'Selesai'][$project->status] }}</span>
</div>

<section class="grid summary-grid" aria-label="Ringkasan proyek">
    <div class="card"><span class="muted small">Progres</span><strong class="summary-number">{{ $progress }}%</strong><span class="small muted">{{ $completedCount }} dari {{ $taskCount }} tugas selesai</span><div class="progress-track" role="progressbar" aria-label="Progres {{ $project->name }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progress }}"><span style="width: {{ $progress }}%"></span></div></div>
    <div class="card"><span class="muted small">Terhambat</span><strong class="summary-number">{{ $project->tasks->where('status', 'blocked')->count() }}</strong></div>
    <div class="card"><span class="muted small">Pemilik</span><strong class="summary-owner">{{ $project->owner?->name ?? 'Belum ditetapkan' }}</strong><span class="small muted">@if($project->due_at) Tenggat {{ $project->due_at->format('d M Y') }} @else Tenggat belum ditetapkan @endif</span></div>
</section>

@if($canManageProjects)
<details class="card section-card">
    <summary>Edit detail proyek</summary>
    <form method="post" action="{{ route('projects.update', $project) }}" class="form-grid compact-form">
        @csrf @method('PUT')
        <div class="field full"><label for="board-project-name">Nama proyek</label><input id="board-project-name" name="name" value="{{ $project->name }}" required maxlength="160"></div>
        <div class="field full"><label for="board-project-description">Deskripsi</label><textarea id="board-project-description" name="description" maxlength="5000">{{ $project->description }}</textarea></div>
        <div class="field"><label for="board-project-status">Status</label><select id="board-project-status" name="status">@foreach(['active'=>'Aktif','paused'=>'Ditunda','completed'=>'Selesai'] as $value=>$label)<option value="{{ $value }}" @selected($project->status===$value)>{{ $label }}</option>@endforeach</select></div>
        <div class="field"><label for="board-project-priority">Prioritas</label><select id="board-project-priority" name="priority">@foreach(['high'=>'Tinggi','medium'=>'Sedang','low'=>'Rendah'] as $value=>$label)<option value="{{ $value }}" @selected($project->priority===$value)>{{ $label }}</option>@endforeach</select></div>
        <div class="field"><label for="board-project-due">Tenggat</label><input id="board-project-due" name="due_at" type="date" value="{{ $project->due_at?->format('Y-m-d') }}"></div>
        <div class="field"><label for="board-project-owner">Pemilik</label><select id="board-project-owner" name="owner_id"><option value="">Belum ditetapkan</option>@foreach($project->department->members as $member)<option value="{{ $member->id }}" @selected($project->owner_id===$member->id)>{{ $member->name }}</option>@endforeach</select></div>
        <div class="field full"><button type="submit">Simpan perubahan</button></div>
    </form>
</details>
@endif

<section class="card section-card">
    <h2>Tambah tugas</h2>
    <form method="post" action="{{ route('tasks.store', $project) }}" class="form-grid">
        @csrf
        <div class="field"><label for="title">Judul tugas</label><input id="title" name="title" required maxlength="180">@error('title')<span class="error">{{ $message }}</span>@enderror</div>
        <div class="field"><label for="due_at">Tenggat</label><input id="due_at" name="due_at" type="date"></div>
        <div class="field"><label for="status">Status</label><select id="status" name="status"><option value="todo">Akan dikerjakan</option><option value="in_progress">Sedang dikerjakan</option><option value="blocked">Terhambat</option><option value="done">Selesai</option></select></div>
        <div class="field"><label for="priority">Prioritas</label><select id="priority" name="priority"><option value="medium">Sedang</option><option value="high">Tinggi</option><option value="low">Rendah</option></select></div>
        <div class="field full"><label for="assignee_id">Penanggung jawab</label><select id="assignee_id" name="assignee_id"><option value="">Belum ditetapkan</option>@foreach($project->department->members as $member)<option value="{{ $member->id }}">{{ $member->name }}</option>@endforeach</select></div>
        <div class="field full"><label for="description">Detail tugas</label><textarea id="description" name="description" maxlength="5000"></textarea></div>
        <div class="field full"><button type="submit">Tambah tugas</button></div>
    </form>
</section>

@php($columns = ['todo'=>'Akan dikerjakan','in_progress'=>'Sedang dikerjakan','blocked'=>'Terhambat','done'=>'Selesai'])
<section aria-label="Papan Kanban" class="board">
    @foreach($columns as $status => $label)
        <section class="column" aria-labelledby="column-{{ $status }}">
            <h2 id="column-{{ $status }}">{{ $label }} · {{ ($tasksByStatus[$status] ?? collect())->count() }}</h2>
            @foreach(($tasksByStatus[$status] ?? collect()) as $task)
                <article class="task">
                    <div class="row"><strong>{{ $task->title }}</strong><span class="pill {{ $task->priority==='high'?'red':'' }}">{{ ['low'=>'Rendah','medium'=>'Sedang','high'=>'Tinggi'][$task->priority] }}</span></div>
                    @if($task->description)<p>{{ $task->description }}</p>@endif
                    <div class="stats">@if($task->assignee)<span class="pill">{{ $task->assignee->name }}</span>@endif @if($task->due_at)<span class="pill">Tenggat {{ $task->due_at->format('d M Y') }}</span>@endif</div>
                    <details><summary class="small">Edit tugas</summary>
                        <form id="edit-{{ $task->id }}" method="post" action="{{ route('tasks.update', $task) }}" class="form-grid compact-form">
                            @csrf @method('PUT')
                            <div class="field full"><label for="title-{{ $task->id }}">Judul</label><input id="title-{{ $task->id }}" name="title" value="{{ $task->title }}" required maxlength="180"></div>
                            <div class="field full"><label for="description-{{ $task->id }}">Detail</label><textarea id="description-{{ $task->id }}" name="description" maxlength="5000">{{ $task->description }}</textarea></div>
                            <div class="field"><label for="status-{{ $task->id }}">Status</label><select id="status-{{ $task->id }}" name="status">@foreach($columns as $key=>$name)<option value="{{ $key }}" @selected($task->status===$key)>{{ $name }}</option>@endforeach</select></div>
                            <div class="field"><label for="priority-{{ $task->id }}">Prioritas</label><select id="priority-{{ $task->id }}" name="priority">@foreach(['low'=>'Rendah','medium'=>'Sedang','high'=>'Tinggi'] as $key=>$name)<option value="{{ $key }}" @selected($task->priority===$key)>{{ $name }}</option>@endforeach</select></div>
                            <div class="field full"><label for="assignee-{{ $task->id }}">Penanggung jawab</label><select id="assignee-{{ $task->id }}" name="assignee_id"><option value="">Belum ditetapkan</option>@foreach($project->department->members as $member)<option value="{{ $member->id }}" @selected($task->assignee_id===$member->id)>{{ $member->name }}</option>@endforeach</select></div>
                            <div class="field full"><label for="due-{{ $task->id }}">Tenggat</label><input id="due-{{ $task->id }}" name="due_at" type="date" value="{{ $task->due_at?->format('Y-m-d') }}"></div>
                            <div class="field full"><button type="submit">Simpan perubahan</button></div>
                        </form>
                    </details>
                    @if($task->created_by===auth()->id() || $canManageTasks)
                        <form method="post" action="{{ route('tasks.destroy',$task) }}" onsubmit="return confirm('Hapus tugas ini?')">@csrf @method('DELETE')<button class="danger" type="submit">Hapus tugas</button></form>
                    @endif
                </article>
            @endforeach
        </section>
    @endforeach
</section>

<section class="card section-card">
    <h2>Aktivitas terbaru</h2>
    @if($activityEvents->isEmpty())
        <p class="muted">Aktivitas proyek akan muncul di sini.</p>
    @else
        <ol class="activity-list">
            @foreach($activityEvents as $event)
                <li><p>{{ $event->summary }}</p><span class="small muted">{{ $event->actor?->name ?? 'Anggota tim' }} · {{ $event->created_at->timezone(config('app.timezone'))->format('d M Y, H:i') }}</span></li>
            @endforeach
        </ol>
    @endif
</section>
@endsection
