<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RuangKerja') · Seksi Aplikasi, Siber dan Statistik</title>
    <style>
        :root{font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;color:#20312a;background:#f5f7f5;font-synthesis:none}*{box-sizing:border-box}body{margin:0}a{color:#176449;text-decoration:none}button,input,select,textarea{font:inherit}button,.button{border:0;border-radius:10px;background:#176449;color:#fff;padding:10px 15px;font-weight:650;cursor:pointer}.button.secondary,button.secondary{background:#e9f1ed;color:#176449}.button.danger,button.danger{background:#fce9e6;color:#a43931}.wrap{max-width:1160px;margin:auto;padding:28px 22px}.topbar{background:#fff;border-bottom:1px solid #e5ebe7}.topinner{max-width:1160px;margin:auto;padding:15px 22px;display:flex;justify-content:space-between;align-items:center}.brand{font-weight:800;color:#174d3c;font-size:18px}.brand small{display:block;color:#728078;font-weight:500;font-size:11px;margin-top:2px}.row{display:flex;align-items:center;justify-content:space-between;gap:12px}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px}.card{background:#fff;border:1px solid #e6ebe7;border-radius:16px;padding:18px;box-shadow:0 5px 22px #1f332b08}.card h2,.card h3{margin:0 0 9px}.muted{color:#748078}.small{font-size:12px}.stats{display:flex;gap:7px;flex-wrap:wrap;margin:12px 0}.pill{display:inline-block;border-radius:999px;padding:4px 9px;background:#edf4f0;color:#246044;font-size:11px;font-weight:650}.pill.amber{background:#fff2df;color:#9a621b}.pill.red{background:#fae9e6;color:#a33f38}.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.field{display:flex;flex-direction:column;gap:5px}.field.full{grid-column:1/-1}.field label{font-size:12px;font-weight:700}.field input,.field textarea,.field select{border:1px solid #dce4df;border-radius:9px;padding:10px;background:#fff;color:#20312a}.field textarea{min-height:80px;resize:vertical}.flash{margin:16px 0;padding:12px 15px;border-radius:10px;background:#e7f4ec;color:#195638}.error{color:#a43931;font-size:12px}.board{display:grid;grid-template-columns:repeat(4,minmax(220px,1fr));gap:12px;overflow-x:auto;padding-bottom:12px}.column{background:#edf1ee;border-radius:14px;padding:12px;min-height:240px}.column h3{font-size:13px;margin:0 0 10px}.task{background:#fff;border:1px solid #e3e9e5;border-radius:12px;padding:12px;margin:8px 0}.task p{margin:6px 0;color:#69766f;font-size:12px;white-space:pre-wrap}.task form{margin-top:10px}.auth{max-width:440px;margin:6vh auto}.actions{display:flex;gap:8px;align-items:center}.table{width:100%;border-collapse:collapse}.table th,.table td{text-align:left;padding:11px;border-bottom:1px solid #e8ede9;font-size:13px}@media(max-width:700px){.wrap{padding:20px 14px}.board{grid-template-columns:repeat(4,minmax(245px,1fr))}.form-grid{grid-template-columns:1fr}.field.full{grid-column:auto}.topinner{padding:14px}.mobile-hide{display:none}}
    </style>
    <style>
        .page-heading{margin:10px 0 18px}.page-heading h1{margin:0;font-size:clamp(25px,4vw,34px)}
        .summary-grid{grid-template-columns:repeat(3,minmax(0,1fr));margin:18px 0}.summary-number{display:block;font-size:30px;margin:8px 0 2px}.summary-owner{display:block;font-size:17px;margin:8px 0 3px}
        .section-card{margin:16px 0}.section-heading p{margin:2px 0 0}.filters{display:grid;grid-template-columns:minmax(180px,2fr) repeat(3,minmax(130px,1fr));gap:12px;margin:16px 0 20px;align-items:end}
        .checkbox-field{display:flex;align-items:center;gap:8px;min-height:42px;font-size:13px}.checkbox-field input{width:16px;height:16px;accent-color:#176449}.filter-actions{display:flex;gap:8px;align-items:center}.filter-actions .button{display:inline-block}
        .project-grid{grid-template-columns:repeat(auto-fit,minmax(270px,1fr))}.project-card{height:100%}.project-link{display:block;color:inherit}.project-link:hover h3{text-decoration:underline}.project-description{min-height:38px}
        .progress-label{display:flex;justify-content:space-between;gap:8px;font-size:12px;margin:12px 0 5px}.progress-track{height:8px;background:#e7eeea;border-radius:999px;overflow:hidden;margin:8px 0}.progress-track span{display:block;height:100%;background:#25825f;border-radius:inherit;transition:width .2s ease}
        .edit-project{border-top:1px solid #e8ede9;margin-top:14px;padding-top:12px}.edit-project summary,.task summary,.section-card>summary{cursor:pointer;color:#176449;font-weight:700}.compact-form{margin-top:12px}.empty-state{padding:20px 0}.column h2{font-size:13px;margin:0 0 10px}
        .activity-list{list-style:none;margin:0;padding:0}.activity-list li{border-top:1px solid #e8ede9;padding:12px 0}.activity-list li:first-child{border-top:0}.activity-list p{margin:0 0 4px}.error-summary{margin:16px 0;padding:12px 15px;border-radius:10px;background:#fae9e6;color:#8f2e28}.error-summary ul{margin:6px 0 0;padding-left:20px}
        @media(max-width:700px){.summary-grid{grid-template-columns:1fr}.filters{grid-template-columns:1fr}.filter-actions{flex-wrap:wrap}.section-card{padding:15px}.project-description{min-height:0}}
    </style>
</head>
<body>
<header class="topbar"><div class="topinner"><a class="brand" href="{{ route('projects.index') }}">RuangKerja<small>Seksi Aplikasi, Siber dan Statistik</small></a>
    @auth <div class="actions"><span class="small muted mobile-hide">{{ auth()->user()->name }}</span><form method="post" action="{{ route('logout') }}">@csrf<button class="secondary" type="submit">Keluar</button></form></div> @else <a href="{{ route('login') }}">Masuk</a> @endauth
</div></header>
<main class="wrap">
    @if (session('status'))<div class="flash" role="status">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="error-summary" role="alert"><strong>Periksa formulir berikut.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
</main>
</body>
</html>
