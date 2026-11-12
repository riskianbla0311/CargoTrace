@extends('layouts.app')
@section('title','Users')
@section('top-title','User Management')
@section('content')
<div class="pagehead"><div><h1>User management</h1><div class="muted">Create accounts and manage user profile and access roles.</div></div></div>
<div class="panel" style="margin-bottom:16px">
    <h2>Add user</h2>
    <form method="POST" action="{{ route('admin.users.store') }}" class="formgrid">
        @csrf
        <div class="field"><label>Full name</label><input name="name" required></div>
        <div class="field"><label>Email</label><input type="email" name="email" required></div>
        <div class="field"><label>Temporary password</label><input type="password" name="password" minlength="8" required></div>
        <div class="field"><label>Role</label><select name="role"><option value="customer">Customer</option><option value="operator">Operator</option><option value="admin">Admin</option></select></div>
        <div class="field full"><button class="btn">Create user</button></div>
    </form>
</div>
<div class="panel" style="overflow-x:auto">
<table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Update account</th></tr></thead><tbody>
@foreach($users as $u)
<tr><td>{{ $u->name }}</td><td>{{ $u->email }}</td><td><span class="badge">{{ $u->role }}</span></td><td>
<form method="POST" action="{{ route('admin.users.update',$u) }}" style="display:flex;gap:6px;flex-wrap:wrap">
@csrf @method('PUT')
<input name="name" value="{{ $u->name }}" required style="min-width:130px">
<input type="email" name="email" value="{{ $u->email }}" required style="min-width:170px">
<select name="role">@foreach(['admin','operator','customer'] as $r)<option value="{{ $r }}" @selected($u->role===$r)>{{ ucfirst($r) }}</option>@endforeach</select>
<button class="btn secondary">Save</button>
</form></td></tr>
@endforeach
</tbody></table>{{ $users->links() }}</div>
@endsection
