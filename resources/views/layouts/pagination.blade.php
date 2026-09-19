@php
  $perPage = request('per_page', 10);
  $options = [10, 15, 20, 25, 50];
  $entity = $name ?? 'entries';
@endphp
<div class="card-footer d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 border-top">
  <div class="d-flex align-items-center gap-2">
    <span class="text-muted small">Show</span>
    <select class="form-select form-select-sm py-1" style="width: 72px; font-size: 0.8125rem;" onchange="changePerPage(this.value)">
      @foreach($options as $opt)
        <option value="{{ $opt }}" {{ $perPage == $opt ? 'selected' : '' }}>{{ $opt }}</option>
      @endforeach
    </select>
    <span class="text-muted small">{{ $entity }}</span>
    @if(isset($paginator) && method_exists($paginator, 'total') && $paginator->total() > 0)
      <span class="text-muted small d-none d-md-inline">&bull; Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }}</span>
    @endif
  </div>
  <div class="d-flex align-items-center">
    @if(isset($paginator) && method_exists($paginator, 'links'))
      {{ $paginator->links('layouts.pagination-links') }}
    @endif
  </div>
</div>
