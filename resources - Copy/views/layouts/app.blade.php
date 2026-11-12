<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title','CargoTrace') · CargoTrace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
:root{--navy:#10243a;--navy2:#142d49;--blue:#2864e8;--ink:#172b4d;--muted:#718096;--bg:#f4f7fb;--line:#e5ebf3;--green:#15966b;--orange:#d97706}
*{box-sizing:border-box}body{margin:0;background:var(--bg);font-family:'DM Sans',sans-serif;color:var(--ink)}a{text-decoration:none;color:inherit}
.shell{display:block;min-height:100vh}.side{position:fixed;top:0;left:0;bottom:0;width:232px;height:100vh;overflow-y:auto;overflow-x:hidden;z-index:1000;background:var(--navy);color:#dce7f5;padding:25px 14px 18px;display:flex;flex-direction:column}.main{margin-left:232px;min-height:100vh;min-width:0}
.brand{font-size:23px;font-weight:800;color:white;margin:0 12px 42px;letter-spacing:-.7px}.brand span{color:#72a4ff}
.navlabel{font-size:10px;letter-spacing:1.7px;color:#8398b1;margin:19px 12px 9px}.nav a{display:flex;align-items:center;gap:11px;padding:12px 13px;border-radius:9px;color:#c6d3e3;margin:3px 0;font-size:13px;transition:.18s}.nav a:hover,.nav a.active{background:#203e5f;color:white}.navicon{width:17px;text-align:center;color:#b7c9df;font-size:14px}
.sidefoot{margin-top:auto;font-size:11px;line-height:1.6;color:#91a5bc;padding:14px 12px 3px;border-top:1px solid #29415b}
.top{height:70px;background:white;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;padding:0 32px;position:sticky;top:0;z-index:10}.top strong{font-size:14px}.top small{color:var(--muted);font-size:12px}.top-user{display:flex;align-items:center;gap:14px}.top-user>span{font-size:12px;font-weight:700}
.content{padding:29px 32px 40px;max-width:1600px;margin:auto}.pagehead{display:flex;justify-content:space-between;align-items:center;gap:18px;margin-bottom:23px}.pagehead h1{font-size:25px;letter-spacing:-.5px;margin:0 0 5px}.muted{color:var(--muted);font-size:13px}
.grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:15px}.card,.panel{background:white;border:1px solid var(--line);border-radius:14px;box-shadow:0 4px 18px #19385b06}.card{padding:19px 20px}.metric{font-size:29px;font-weight:800;margin-top:8px;letter-spacing:-.6px}.metriclabel{font-size:11px;color:var(--muted);font-weight:700;letter-spacing:.45px}.metric-foot{font-size:11px;color:#91a0b4;margin-top:6px}
.panel{padding:20px;margin-top:17px}.panel h2{font-size:15px;margin:0 0 16px;letter-spacing:-.15px}.twocol{display:grid;grid-template-columns:1.15fr .85fr;gap:17px}.dashboard-lower{display:grid;grid-template-columns:1.25fr .75fr;gap:17px}
table{width:100%;border-collapse:collapse;font-size:12px}th{text-align:left;color:var(--muted);font-size:10px;text-transform:uppercase;letter-spacing:.65px;padding:12px;border-bottom:1px solid var(--line);white-space:nowrap}td{padding:14px 12px;border-bottom:1px solid #eef2f7;vertical-align:middle}tbody tr:last-child td{border-bottom:0}tbody tr:hover{background:#fafcff}
.btn{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:8px;background:var(--blue);color:white;padding:10px 15px;font:700 12px 'DM Sans';cursor:pointer;white-space:nowrap}.btn:hover{filter:brightness(.96)}.btn.secondary{background:#edf3ff;color:var(--blue)}.btn.dark{background:var(--navy)}
.formgrid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}.field label{display:block;font-size:12px;font-weight:700;margin-bottom:7px}.field input,.field select,.field textarea,.search input,.search select{width:100%;border:1px solid #dce4ee;border-radius:8px;padding:11px 12px;font:13px 'DM Sans';background:white;color:var(--ink);outline:none}.field input:focus,.field select:focus,.field textarea:focus,.search input:focus,.search select:focus{border-color:#8fb1ff;box-shadow:0 0 0 3px #2864e814}.field.full{grid-column:1/-1}
.badge{display:inline-block;border-radius:30px;padding:5px 9px;background:#edf3ff;color:#2459ba;font-size:10px;font-weight:800;white-space:nowrap}.badge.green{background:#e5f7ef;color:#13805c}.badge.orange{background:#fff3df;color:#ad6500}.badge.red{background:#fff0f0;color:#c43c3c}
.flash{padding:12px 15px;border-radius:8px;background:#e7f8ef;color:#16764f;margin-bottom:16px;font-size:13px}.error{color:#c53030;font-size:12px;margin-top:5px}.timeline{border-left:2px solid #dce7f4;margin-left:8px;padding-left:20px}.event{position:relative;padding:0 0 22px}.event:before{content:'';position:absolute;width:10px;height:10px;border-radius:50%;background:var(--blue);left:-26px;top:3px}
.search{display:flex;gap:9px;margin-bottom:15px;align-items:center}.search input{max-width:300px}.search select{max-width:180px}#shipment-map{height:330px;border-radius:10px;z-index:1}
.login-page{min-height:100vh;display:grid;grid-template-columns:minmax(320px, .92fr) 1.08fr;background:#fff}.login-visual{background:linear-gradient(145deg,#10243a,#1d4d86);color:#fff;padding:55px clamp(30px,6vw,90px);display:flex;flex-direction:column;justify-content:center;position:relative;overflow:hidden}.login-visual:before,.login-visual:after{content:'';position:absolute;border:1px solid #ffffff18;border-radius:50%;width:440px;height:440px;right:-210px;top:-180px}.login-visual:after{width:330px;height:330px;right:-150px;top:-125px}.login-brand{font-size:22px;font-weight:800;margin-bottom:70px}.login-brand span{color:#83b2ff}.login-kicker{font-size:10px;letter-spacing:2px;color:#a9c4e6;font-weight:700;margin-bottom:17px}.login-visual h1{font-size:clamp(30px,3vw,44px);line-height:1.08;letter-spacing:-1.3px;margin:0 0 17px;max-width:470px}.login-visual p{font-size:13px;line-height:1.8;color:#c4d5e9;max-width:380px}.login-art{height:170px;max-width:420px;margin:30px 0 25px;border:1px solid #ffffff20;border-radius:15px;background:linear-gradient(135deg,#ffffff0d,#ffffff04);position:relative;overflow:hidden}.login-art:before{content:'';position:absolute;left:8%;right:8%;top:55%;height:1px;background:#ffffff35;transform:rotate(-12deg);box-shadow:0 -32px 0 #ffffff0b,0 32px 0 #ffffff0b}.route-dot{position:absolute;width:10px;height:10px;background:#b8ddff;border:3px solid #fff;border-radius:50%;top:49%}.route-dot.one{left:12%}.route-dot.two{left:48%;top:34%}.route-dot.three{right:12%;top:19%}.login-art-label{position:absolute;bottom:18px;left:18px;font-size:10px;color:#c8d9ed}.login-form-side{display:grid;place-items:center;padding:35px}.login-box{width:min(430px,100%)}.login-box h2{font-size:26px;margin:0 0 5px}.login-sub{font-size:12px;color:var(--muted);margin-bottom:27px}.login-box .field{margin:16px 0}.login-box .field input{padding:13px}.login-box button{width:100%;margin-top:12px;padding:13px}.login-demo{font-size:11px;color:var(--muted);margin-top:13px}.login-error{background:#fff0f0;color:#b42318;padding:10px;border-radius:8px;font-size:12px;margin-bottom:12px}
.status-row{display:flex;justify-content:space-between;font-size:11px;margin-bottom:7px}.status-track{height:7px;background:#edf1f7;border-radius:20px;overflow:hidden}.status-fill{height:100%;background:var(--blue);border-radius:20px}
@media(max-width:1050px){.side{width:205px}.main{margin-left:205px}.content{padding:23px}.grid{grid-template-columns:repeat(2,1fr)}.twocol,.dashboard-lower{grid-template-columns:1fr}}
@media(max-width:700px){.shell{display:block}.side{position:relative;width:100%;height:auto;min-height:auto;bottom:auto;padding:12px 14px}.main{margin-left:0}.brand{margin:5px 8px 12px}.navlabel,.sidefoot{display:none}.nav{display:flex;overflow:auto;gap:5px}.nav a{white-space:nowrap;padding:9px}.top{height:62px;padding:0 16px}.content{padding:18px 14px}.grid{grid-template-columns:1fr 1fr}.formgrid{grid-template-columns:1fr}.pagehead{align-items:flex-start;flex-direction:column}.search{flex-wrap:wrap}.search input,.search select{max-width:none;flex:1 1 100%}.login-page{grid-template-columns:1fr}.login-visual{display:none}.login-form-side{min-height:100vh;padding:25px}.panel{overflow-x:auto}}
</style>
@stack('head')
</head>
<body>
<div class="shell">
<aside class="side">
<div class="brand">Cargo<span>Trace</span></div>
<div class="navlabel">WORKSPACE</div>
<nav class="nav">
<a class="{{ request()->routeIs('dashboard')?'active':'' }}" href="{{ route('dashboard') }}"><span class="navicon">▦</span>Overview</a>
<a class="{{ request()->routeIs('shipments.*')?'active':'' }}" href="{{ route('shipments.index') }}"><span class="navicon">▤</span>Shipments</a>
@if(in_array(auth()->user()->role,['admin','operator']))
<div class="navlabel">OPERATIONS</div>
<a class="{{ request()->routeIs('shipments.create')?'active':'' }}" href="{{ route('shipments.create') }}"><span class="navicon">＋</span>Create shipment</a>
@endif
@if(auth()->user()->role==='admin')
<div class="navlabel">MANAGE</div>
<a class="{{ request()->routeIs('admin.users')?'active':'' }}" href="{{ route('admin.users') }}"><span class="navicon">♙</span>Customers & users</a>
<a class="{{ request()->routeIs('admin.carriers')?'active':'' }}" href="{{ route('admin.carriers') }}"><span class="navicon">▣</span>Carriers</a>
<a class="{{ request()->routeIs('admin.locations')?'active':'' }}" href="{{ route('admin.locations') }}"><span class="navicon">⌖</span>Locations</a>
<a class="{{ request()->routeIs('admin.audit')?'active':'' }}" href="{{ route('admin.audit') }}"><span class="navicon">◷</span>Audit logs</a>
@endif
@if(in_array(auth()->user()->role,['admin','operator']))
<div class="navlabel">MONITORING</div>
<a class="{{ request()->routeIs('exceptions.*')?'active':'' }}" href="{{ route('exceptions.index') }}"><span class="navicon">⚠</span>Exceptions</a>
@endif
<div class="navlabel">ACCOUNT</div>
<a class="{{ request()->routeIs('notifications.*')?'active':'' }}" href="{{ route('notifications.index') }}"><span class="navicon">♧</span>Notifications</a>
</nav>
<div class="sidefoot">● All systems operational<br>Global Shipment Visibility<br>Signed in as {{ auth()->user()->role }}</div>
</aside>
<main class="main">
<header class="top"><div><strong>@yield('top-title','Overview')</strong><br><small>Global shipment visibility platform</small></div><div class="top-user"><span>{{ auth()->user()->name }}</span><form method="POST" action="{{ route('logout') }}">@csrf<button class="btn secondary">Logout</button></form></div></header>
<section class="content">
@if(session('success'))<div class="flash">{{ session('success') }}</div>@endif
@if($errors->any())<div class="flash" style="background:#fff0f0;color:#a33">{{ $errors->first() }}</div>@endif
@yield('content')
</section></main></div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script><script src="https://cdn.jsdelivr.net/npm/chart.js"></script>@stack('scripts')
</body></html>