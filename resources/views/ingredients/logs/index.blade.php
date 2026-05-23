@extends('layouts.app')

@section('title', 'Log Stok Bahan')

@section('page-title', 'Log Stok Bahan')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Log Stok</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Riwayat Perubahan Stok</h3>
            <div class="card-tools">
                <a href="{{ route('ingredient-logs.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-exchange-alt"></i> Atur Stok
                </a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Bahan</th>
                        <th>Tipe</th>
                        <th>Jumlah</th>
                        <th>Catatan</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $loop->iteration + ($logs->currentPage() - 1) * $logs->perPage() }}</td>
                            <td>{{ $log->ingredient->name }}</td>
                            <td>
                                @if($log->type == 'IN')
                                    <span class="badge badge-success">Masuk</span>
                                @elseif($log->type == 'OUT')
                                    <span class="badge badge-danger">Keluar</span>
                                @else
                                    <span class="badge badge-info">Penyesuaian</span>
                                @endif
                            </td>
                            <td>{{ number_format($log->qty, 2) }} {{ $log->ingredient->unit }}</td>
                            <td>{{ $log->note ?? '-' }}</td>
                            <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada log perubahan stok</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="card-footer">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
@endsection