@extends('layouts.app')
@section('title', $project->name)
@section('content')
<p class="small"><a href="{{ route('projects.index') }}">← Portofolio</a> / {{ $project->department->name }}</p>
<div class="row"><div><p class="small muted">PAPAN TUGAS</p><h1 style="margin:0">{{ $project->name }}</h1><p class="muted">{{ $project->description ?: 'Tambahkan tugas dan perbarui status proyek.' }}</p></div><span class="pill">{{ $project->tasks->count() }} tugas</span></div>
<section class="card" style="margin:16px 0"><h2>Tambah tugas</h2><form method="post" action="{{ route('tasks.store', $project) }}" class="form-grid">@csrf
    <div class="field"><label for="title">Judul tugas</label><input id="title" name="title" required maxlength="180">@error('title')<span class="error">{{ $message }}</span>@enderror</div>
    <div class="field"><label for="due_at">Tenggat</label><input id="due_at" name="due_at" type="date"></div>
    <div class="field"><label for="status">Status</label><select id="status" name="status"><option value="todo">Akan dikerjakan</option><option value="in_progress">Sedang dikerjakan</option><option value="blocked">Terhambat</option><option value="done">Selesai</option></select></div>
    <div class="field"><label for="priority">Prioritas</label><select id="priority" name="priority"><option value="medium">Sedang</option><option value="high">Tinggi</option><option value="low">Rendah</option></select></div>
    <div class="field full"><label for="assignee_id">Penanggung jawab</label><select id="assignee_id" name="assignee_id"><option value="">Belum ditetapkan</option>@foreach($project->department->members as $member)<option value="{{ $member->id }}">{{ $member->name }}</option>@endforeach</select></div>
    <div class="field full"><label for="description">Detail tugas</label><textarea id="description" name="description"></textarea></div><div class="field full"><button type="submit">Tambah tugas</button></div>
</form></section>
@php($columns = ['todo'=>'Akan dikerjakan','in_progress'=>'Sedang dikerjakan','blocked'=>'Terhambat','done'=>'Selesai'])
<div class="board">@foreach($columns as $status => $label)<section class="column"><h3>{{ $label }} · {{ ($tasksByStatus[$status] ?? collect())->count() }}</h3>
@foreach(($tasksByStatus[$status] ?? collect()) as $task)<article class="task"><div class="row"><strong>{{ $task->title }}</strong><span class="pill {{ $task->priority==='high'?'red':'' }}">{{ ['low'=>'Rendah','medium'=>'Sedang','high'=>'Tinggi'][$task->priority] }}</span></div>@if($task->description)<p>{{ $task->description }}</p>@endif<div class="stats">@if($task->assignee)<span class="pill">{{ $task->assignee->name }}</span>@endif @if($task->due_at)<span class="pill">Tenggat {{ $task->due_at->format('d M Y') }}</span>@endif</div>
<details><summary class="small">Edit tugas</summary><form id="edit-{{ $task->id }}" method="post" action="{{ route('tasks.update',$task) }}" class="form-grid">@csrf @method('PUT')
    <div class="field full"><label for="title-{{ $task->id }}">Judul</label><input id="title-{{ $task->id }}" name="title" value="{{ $task->title }}" required></div>
    <div class="field full"><label for="description-{{ $task->id }}">Detail</label><textarea id="description-{{ $task->id }}" name="description">{{ $task->description }}</textarea></div>
    <div class="field"><label for="status-{{ $task->id }}">Status</label><select id="status-{{ $task->id }}" name="status">@foreach($columns as $key=>$name)<option value="{{ $key }}" @selected($task->status===$key)>{{ $name }}</option>@endforeach</select></div>
    <div class="field"><label for="priority-{{ $task->id }}">Prioritas</label><select id="priority-{{ $task->id }}" name="priority">@foreach(['low'=>'Rendah','medium'=>'Sedang','high'=>'Tinggi'] as $key=>$name)<option value="{{ $key }}" @selected($task->priority===$key)>{{ $name }}</option>@endforeach</select></div>
    <div class="field full"><label for="assignee-{{ $task->id }}">Penanggung jawab</label><select id="assignee-{{ $task->id }}" name="assignee_id"><option value="">Belum ditetapkan</option>@foreach($project->department->members as $member)<option value="{{ $member->id }}" @selected($task->assignee_id===$member->id)>{{ $member->name }}</option>@endforeach</select></div>
    <div class="field full"><label for="due-{{ $task->id }}">Tenggat</label><input id="due-{{ $task->id }}" name="due_at" type="date" value="{{ $task->due_at?->format('Y-m-d') }}"></div><div class="field full"><button type="submit">Simpan perubahan</button></div>
</form></details>
@if($task->created_by===auth()->id() || auth()->user()->departments()->wherePivotIn('role',['owner','leader'])->exists())<form method="post" action="{{ route('tasks.destroy',$task) }}" onsubmit="return confirm('Hapus tugas ini?')">@csrf @method('DELETE')<button class="danger" type="submit">Hapus tugas</button></form>@endif
</article>@endforeach</section>@endforeach</div>
@endsection
