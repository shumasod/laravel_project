@foreach(['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $type)
    @if(session($key))
    <div class="alert alert-{{ $type }} alert-dismissible fade show mt-3" role="alert">
        @if($type === 'success')<i class="bi bi-check-circle me-1"></i>@endif
        @if($type === 'danger')<i class="bi bi-exclamation-circle me-1"></i>@endif
        @if($type === 'warning')<i class="bi bi-exclamation-triangle me-1"></i>@endif
        @if($type === 'info')<i class="bi bi-info-circle me-1"></i>@endif
        {{ session($key) }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
@endforeach

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
    <i class="bi bi-exclamation-circle me-1"></i>
    <strong>入力内容を確認してください。</strong>
    <ul class="mb-0 mt-1">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
