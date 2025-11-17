{{-- resources/views/layouts/sidebar.blade.php --}}
<style>
    .sidebar { width:240px; background:#243b55; color:#fff; height:100vh; position:fixed; top:60px; left:0; padding-top:20px; overflow-y:auto; }
    .sidebar ul { list-style:none; padding:0; margin:0; }
    .sidebar li { padding:12px 20px; }
    .sidebar li a { color:#fff; text-decoration:none; display:block; }
    .sidebar li:hover { background:#1e2f44; }
    .sidebar .active { background:#162330; font-weight:bold; }
</style>

<div class="sidebar">
    <ul>
        {{-- Dashboard --}}
        <li class="{{ Request::is('replacement-seeds/dashboard') ? 'active' : '' }}">
            <a href="{{ route('replacement.dashboard') }}">Dashboard</a>
        </li>

        {{-- Request --}}
        <li class="{{ Request::is('replacement-seeds/request*') ? 'active' : '' }}">
            <a href="{{ route('replacement.request') }}">Request</a>
        </li>
        
        {{-- Users --}}
        <li class="{{ Request::is('replacement-seeds/users*') ? 'active' : '' }}">
            <a href="{{ route('replacement.users') }}">Users</a>
        </li>

        {{-- Add more links as needed --}}
    </ul>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebarLinks = document.querySelectorAll('.sidebar li a');

    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            sidebarLinks.forEach(l => l.parentElement.classList.remove('active'));
            this.parentElement.classList.add('active');
        });
    });
});
</script>
