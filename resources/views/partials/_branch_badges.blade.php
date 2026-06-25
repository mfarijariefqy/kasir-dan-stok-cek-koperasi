@forelse($branches as $b)
    <span class="badge badge-light border">{{ $b->name }}</span>
@empty
    <span>-</span>
@endforelse
