<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('dashboard') }}" class="brand-link">
        @php $logoPath = public_path('images/logo-koperasi.png'); @endphp
        @if(file_exists($logoPath))
            <img src="{{ asset('images/logo-koperasi.png') }}" alt="Logo"
                 class="brand-image elevation-3"
                 style="width:34px;height:34px;border-radius:50%;object-fit:cover;opacity:1;">
        @else
            <i class="fas fa-leaf brand-image"></i>
        @endif
        <span class="brand-text font-weight-light">Kasir Koperasi</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <i class="fas fa-user-circle fa-2x text-white"></i>
            </div>
            <div class="info">
                <a href="{{ route('profile.edit') }}" class="d-block">{{ auth()->user()->name }}</a>
                <small class="text-muted">
                    {{ auth()->user()->getRoleNames()->first() ?? 'User' }}
                    @if(auth()->user()->branch)
                        &mdash; {{ auth()->user()->branch->name }}
                    @endif
                </small>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                <!-- Dashboard -->
                @can('view-dashboard')
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}"
                            class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                @endcan

                <!-- Transaksi -->
                @canany(['manage-transactions', 'view-transactions'])
                    <li class="nav-header">TRANSAKSI</li>
                    <li class="nav-item {{ request()->routeIs('transactions.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>Transaksi <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('manage-transactions')
                                <li class="nav-item">
                                    <a href="{{ route('transactions.create') }}"
                                        class="nav-link {{ request()->routeIs('transactions.create') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Kasir (Input Transaksi)</p>
                                    </a>
                                </li>
                            @endcan
                            <li class="nav-item">
                                <a href="{{ route('transactions.index') }}"
                                    class="nav-link {{ request()->routeIs('transactions.index') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Riwayat Transaksi</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcanany

                <!-- Piutang -->
                @canany(['view-piutang', 'manage-piutang'])
                    <li class="nav-item">
                        <a href="{{ route('piutang.index') }}"
                            class="nav-link {{ request()->routeIs('piutang.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-invoice-dollar"></i>
                            <p>Piutang</p>
                        </a>
                    </li>
                @endcanany

                <!-- Stok Barang -->
                @canany(['view-stock', 'manage-stock'])
                    <li class="nav-header">STOK BARANG</li>
                    <li class="nav-item {{ request()->routeIs('stock.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('stock.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-warehouse"></i>
                            <p>Stok Barang <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('stock.index') }}"
                                    class="nav-link {{ request()->routeIs('stock.index') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Daftar Stok</p>
                                </a>
                            </li>
                            @can('manage-stock')
                                <li class="nav-item">
                                    <a href="{{ route('stock.in') }}"
                                        class="nav-link {{ request()->routeIs('stock.in') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Stok Masuk</p>
                                    </a>
                                </li>
                            @endcan
                            <li class="nav-item">
                                <a href="{{ route('stock.history') }}"
                                    class="nav-link {{ request()->routeIs('stock.history') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Riwayat Pergerakan</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcanany

                <!-- Master Data -->
                @canany(['manage-products', 'manage-categories', 'manage-customers', 'manage-units'])
                    <li class="nav-header">MASTER DATA</li>
                    @can('manage-products')
                        <li class="nav-item">
                            <a href="{{ route('products.index') }}"
                                class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-box"></i>
                                <p>Produk</p>
                            </a>
                        </li>
                    @endcan
                    @can('manage-categories')
                        <li class="nav-item">
                            <a href="{{ route('categories.index') }}"
                                class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tags"></i>
                                <p>Kategori</p>
                            </a>
                        </li>
                    @endcan
                    @can('manage-units')
                        <li class="nav-item">
                            <a href="{{ route('units.index') }}"
                                class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-ruler"></i>
                                <p>Satuan</p>
                            </a>
                        </li>
                    @endcan
                    @can('manage-customers')
                        <li class="nav-item">
                            <a href="{{ route('customers.index') }}"
                                class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user-friends"></i>
                                <p>Pelanggan Tetap</p>
                            </a>
                        </li>
                    @endcan
                @endcanany

                <!-- Laporan -->
                @can('view-reports')
                    <li class="nav-header">LAPORAN</li>
                    <li class="nav-item {{ request()->routeIs('reports.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>Laporan <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('reports.daily') }}"
                                    class="nav-link {{ request()->routeIs('reports.daily') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Harian</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('reports.monthly') }}"
                                    class="nav-link {{ request()->routeIs('reports.monthly') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Bulanan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('reports.stock') }}"
                                    class="nav-link {{ request()->routeIs('reports.stock') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Stok Barang</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('reports.piutang') }}"
                                    class="nav-link {{ request()->routeIs('reports.piutang') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Piutang</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcan

                <!-- Pengaturan (Super Admin) -->
                @can('manage-branches')
                    <li class="nav-header">PENGATURAN</li>
                    <li class="nav-item">
                        <a href="{{ route('branches.index') }}"
                            class="nav-link {{ request()->routeIs('branches.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-code-branch"></i>
                            <p>Cabang</p>
                        </a>
                    </li>
                @endcan

                <!-- User & Akses -->
                @canany(['manage-users', 'manage-roles'])
                    <li class="nav-header">USER & AKSES</li>
                    @can('manage-users')
                        <li class="nav-item">
                            <a href="{{ route('users.index') }}"
                                class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Manajemen User</p>
                            </a>
                        </li>
                    @endcan
                    @can('manage-roles')
                        <li class="nav-item">
                            <a href="{{ route('roles.index') }}"
                                class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-shield-alt"></i>
                                <p>Role & Akses</p>
                            </a>
                        </li>
                    @endcan
                @endcanany

            </ul>
        </nav>
    </div>
</aside>