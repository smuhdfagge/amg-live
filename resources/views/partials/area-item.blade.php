<a class="area" href="{{ route('work.area', $area['slug']) }}">
    <span class="area-icon"><x-icon :name="$area['icon']" :size="26" /></span>
    <span>
        <h3>{{ $area['title'] }}</h3>
        <p>{{ $area['summary'] }}</p>
    </span>
</a>
