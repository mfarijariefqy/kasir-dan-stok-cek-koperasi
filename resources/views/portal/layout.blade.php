<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Portal Pelanggan') — {{ config('app.name') }}</title>

    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Custom CSS (same as admin) -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

    @stack('styles')
</head>

<body class="hold-transition layout-top-nav">
<div class="wrapper">

    {{-- ── Top Navbar ──────────────────────────────────────────────────── --}}
    <nav class="main-header navbar navbar-expand-md navbar-white navbar-light">
        <div class="container">

            {{-- Brand --}}
            <a href="{{ route('portal.transactions.index') }}" class="navbar-brand">
                @php $logoPath = public_path('images/logo-koperasi.png'); @endphp
                @if(file_exists($logoPath))
                    <img src="{{ asset('images/logo-koperasi.png') }}"
                         alt="Logo" class="brand-image img-circle elevation-2"
                         style="opacity:.9; width:30px; height:30px; object-fit:contain;">
                @else
                    <i class="fas fa-store mr-1"></i>
                @endif
                <span class="brand-text font-weight-bold ml-1">Portal Pelanggan</span>
            </a>

            {{-- Collapse toggle (mobile) --}}
            <button class="navbar-toggler order-1" type="button"
                    data-toggle="collapse" data-target="#navbarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>

            {{-- Right side --}}
            <div class="collapse navbar-collapse order-3" id="navbarCollapse">
                <ul class="navbar-nav ml-auto">

                    {{-- Transaksi Saya --}}
                    <li class="nav-item {{ request()->routeIs('portal.transactions.index') ? 'active' : '' }}">
                        <a href="{{ route('portal.transactions.index') }}" class="nav-link">
                            <i class="fas fa-receipt mr-1"></i> Transaksi Saya
                        </a>
                    </li>

                    {{-- User dropdown --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-toggle="dropdown" href="#">
                            <i class="far fa-user mr-1"></i>
                            <span>{{ auth()->user()->name }}</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <div class="dropdown-item-text text-muted small">
                                <i class="fas fa-tag mr-1"></i>
                                Username: <strong>{{ auth()->user()->username }}</strong>
                            </div>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Keluar
                                </button>
                            </form>
                        </div>
                    </li>

                </ul>
            </div>
        </div>
    </nav>

    {{-- ── Content Wrapper ─────────────────────────────────────────────── --}}
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">@yield('page-title', 'Portal Pelanggan')</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item">
                                <a href="{{ route('portal.transactions.index') }}">Portal</a>
                            </li>
                            @yield('breadcrumb')
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container">

                {{-- Flash messages --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                @endif

                @yield('content')

            </div>
        </div>
    </div>

    {{-- ── Footer ───────────────────────────────────────────────────────── --}}
    <footer class="main-footer">
        <strong>Copyright &copy; {{ date('Y') }}
            <a href="#">{{ config('koperasi.name', config('app.name')) }}</a>.
        </strong>
        <div class="float-right d-none d-sm-inline-block">
            <b>Portal</b> Pelanggan
        </div>
    </footer>

</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
    });

    @if(session('success'))
        Toast.fire({ icon: 'success', title: @json(session('success')) });
    @endif
    @if(session('error'))
        Toast.fire({ icon: 'error', title: @json(session('error')) });
    @endif
</script>

@stack('scripts')
</body>
</html>
