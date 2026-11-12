
@extends('layouts.app')

@section('title', 'Locations')
@section('top-title', 'Locations')

@section('content')

<div class="pagehead">
    <div>
        <h1>Locations</h1>
        <div class="muted">
            Origin, destination, hub, and port coordinates.
        </div>
    </div>
</div>

<div class="twocol">

    {{-- Form tambah lokasi --}}
    <div class="panel">
        <h2>Add location</h2>

        <form method="POST" action="{{ route('admin.locations.store') }}">
            @csrf

            <div class="field">
                <label>Location name</label>
                <input type="text" name="name" value="{{ old('name') }}" required>
            </div>

            <div class="field">
                <label>City</label>
                <input type="text" name="city" value="{{ old('city') }}" required>
            </div>

            <div class="field">
                <label>Country</label>
                <input type="text" name="country" value="{{ old('country') }}" required>
            </div>

            <div class="field">
                <label>Address</label>
                <input type="text" name="address" value="{{ old('address') }}">
            </div>

            <div class="field">
                <label>Latitude</label>
                <input type="number" step="any" name="latitude" value="{{ old('latitude') }}">
            </div>

            <div class="field">
                <label>Longitude</label>
                <input type="number" step="any" name="longitude" value="{{ old('longitude') }}">
            </div>

            <div class="field">
                <label>Location type</label>
                <input type="text" name="location_type" value="{{ old('location_type') }}" placeholder="PORT, HUB, WAREHOUSE">
            </div>

            <button type="submit" class="btn" style="margin-top: 12px;">
                Save Location
            </button>
        </form>
    </div>

    {{-- Daftar lokasi --}}
    <div class="panel">
        <h2>Location list</h2>

        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>City</th>
                    <th>Country</th>
                    <th>Coordinates</th>
                </tr>
            </thead>

            <tbody>
                @forelse($locations as $location)
                    <tr>
                        <td>{{ $location->name }}</td>
                        <td>{{ $location->city }}</td>
                        <td>{{ $location->country }}</td>
                        <td>
                            {{ $location->latitude }},
                            {{ $location->longitude }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No locations available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 18px;">
            {{ $locations->links() }}
        </div>
    </div>

</div>

@endsection
