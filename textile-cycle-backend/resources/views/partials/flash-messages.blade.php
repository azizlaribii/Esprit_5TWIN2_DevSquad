{{-- Partial: flash-messages --}}
@if(session('success'))
    <div class="alert alert-success" style="margin-bottom:1rem">
        <span class="material-icons-round" style="font-size:1.1rem">check_circle</span>
        <div>{{ session('success') }}</div>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger" style="margin-bottom:1rem">
        <span class="material-icons-round" style="font-size:1.1rem">error</span>
        <div>{{ session('error') }}</div>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger" style="margin-bottom:1rem">
        <span class="material-icons-round" style="font-size:1.1rem">warning</span>
        <ul style="list-style:none;padding:0;margin:0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
