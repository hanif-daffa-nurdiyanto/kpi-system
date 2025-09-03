@if (session('error') || request()->get('error'))
    <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">
        {{ session('error') ?? request()->get('error') }}
    </div>
@endif

<script>
    if (sessionStorage.getItem("needs_refresh")) {
        sessionStorage.removeItem("needs_refresh");
        window.location.reload();
    }
</script>