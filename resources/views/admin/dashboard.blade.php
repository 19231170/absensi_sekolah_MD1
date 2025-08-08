@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header -->
        <div class="card card-custom bg-gradient-danger text-white mb-4">
            <div class="card-body text-center">
                <h2 class="mb-2">
                    <i class="fas fa-crown me-2"></i>
                    Admin Dashboard
                </h2>
                <p class="mb-0 opacity-75">
                    <i class="fas fa-shield-alt me-1"></i>
                    Area Administrator - Pengelolaan Data Sistem
                </p>
                <small class="d-block mt-2">
                    <i class="fas fa-clock me-1"></i>
                    Session expires: {{ session('admin_token_expires')->format('H:i:s d/m/Y') }}
                </small>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- System Statistics -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                        <h4>{{ $stats['total_jadwal'] ?? 0 }}</h4>
                        <small>Total Jadwal</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <h4>{{ $stats['total_absensi'] ?? 0 }}</h4>
                        <small>Total Absensi</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <h4>{{ $stats['total_mahasiswa'] ?? 0 }}</h4>
                        <small>Total Mahasiswa</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-database fa-2x mb-2"></i>
                        <h4>{{ $stats['dummy_data_count'] ?? 0 }}</h4>
                        <small>Total Data</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Statistics -->
        <div class="row mb-4">
            <div class="col-md-2 mb-3">
                <div class="card bg-secondary text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-school fa-2x mb-2"></i>
                        <h5>{{ $stats['total_kelas'] ?? 0 }}</h5>
                        <small>Kelas</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 mb-3">
                <div class="card bg-dark text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-graduation-cap fa-2x mb-2"></i>
                        <h5>{{ $stats['total_jurusan'] ?? 0 }}</h5>
                        <small>Jurusan</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-clock fa-2x mb-2"></i>
                        <h5>{{ $stats['total_jam_sekolah'] ?? 0 }}</h5>
                        <small>Jam Sekolah</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 mb-3">
                <div class="card bg-purple text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-user-shield fa-2x mb-2"></i>
                        <h5>{{ $stats['total_users'] ?? 0 }}</h5>
                        <small>Users</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 mb-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-check-double fa-2x mb-2"></i>
                        <h5>{{ $stats['jadwal_aktif'] ?? 0 }}</h5>
                        <small>Jadwal Aktif</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-day fa-2x mb-2"></i>
                        <h5>{{ $stats['absensi_today'] ?? 0 }}</h5>
                        <small>Absensi Hari Ini</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    DANGER ZONE - Zona Berbahaya
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning mb-4">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h5>🚨 PERINGATAN KERAS - PENGHAPUSAN TOTAL 🚨</h5>
                    <p class="mb-2">
                        Fungsi di bawah ini akan <strong>MENGHAPUS SEMUA DATA</strong> secara permanen dari seluruh sistem:
                    </p>
                    <ul class="mb-2">
                        <li><strong>SEMUA data absensi</strong> ({{ $stats['total_absensi'] ?? 0 }} records)</li>
                        <li><strong>SEMUA jadwal kelas</strong> ({{ $stats['total_jadwal'] ?? 0 }} records)</li>
                        <li><strong>SEMUA data siswa</strong> ({{ $stats['total_mahasiswa'] ?? 0 }} records)</li>
                        <li><strong>SEMUA data kelas</strong> ({{ $stats['total_kelas'] ?? 0 }} records)</li>
                        <li><strong>SEMUA data jurusan</strong> ({{ $stats['total_jurusan'] ?? 0 }} records)</li>
                        <li><strong>SEMUA jam sekolah</strong> ({{ $stats['total_jam_sekolah'] ?? 0 }} records)</li>
                        <li><strong>SEMUA users non-admin</strong> ({{ $stats['total_users'] ?? 0 }} records)</li>
                    </ul>
                    <p class="mb-0 text-danger">
                        <strong>TOTAL: {{ $stats['dummy_data_count'] ?? 0 }} RECORDS AKAN DIHAPUS!</strong><br>
                        <strong>TINDAKAN INI TIDAK DAPAT DIBATALKAN!</strong>
                    </p>
                </div>

                <!-- Confirmation Steps -->
                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h6 class="card-title">Langkah Konfirmasi:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="confirm1">
                                    <label class="form-check-label" for="confirm1">
                                        Saya memahami bahwa ini akan menghapus SEMUA DATA secara permanen
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="confirm2">
                                    <label class="form-check-label" for="confirm2">
                                        Saya telah membackup SELURUH DATABASE sebelumnya
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="confirm3">
                                    <label class="form-check-label" for="confirm3">
                                        Saya yakin ingin melanjutkan penghapusan TOTAL DATABASE
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="confirm4">
                                    <label class="form-check-label" for="confirm4">
                                        Saya bertanggung jawab penuh atas RESET DATABASE ini
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Final Confirmation -->
                <div class="mb-4">
                    <label for="finalConfirm" class="form-label">
                        <strong>Ketik "HAPUS SEMUA DATA" untuk mengonfirmasi RESET DATABASE:</strong>
                    </label>
                    <input type="text" class="form-control" id="finalConfirm" placeholder="Ketik konfirmasi di sini...">
                </div>

                <!-- Delete Button -->
                <div class="text-center">
                    <button type="button" class="btn btn-danger btn-lg" id="deleteBtn" disabled>
                        <i class="fas fa-trash-alt me-2"></i>
                        🚨 RESET DATABASE - HAPUS SEMUA DATA 🚨
                    </button>
                </div>

                <!-- Delete Form (Hidden) -->
                <form id="deleteForm" action="{{ route('admin.delete-dummy') }}" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="token" value="{{ request('token') }}">
                </form>
            </div>
        </div>

        <!-- Back to Safety -->
        <div class="text-center mt-4">
            <a href="{{ route('absensi.index') }}" class="btn btn-outline-success btn-lg">
                <i class="fas fa-home me-2"></i>
                Kembali ke Beranda (Aman)
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    const finalConfirm = document.getElementById('finalConfirm');
    const deleteBtn = document.getElementById('deleteBtn');
    const deleteForm = document.getElementById('deleteForm');

    function checkAllConditions() {
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        const confirmText = finalConfirm.value.trim();
        const correctText = 'HAPUS SEMUA DATA';
        
        if (allChecked && confirmText === correctText) {
            deleteBtn.disabled = false;
            deleteBtn.classList.remove('btn-danger');
            deleteBtn.classList.add('btn-danger', 'pulse');
        } else {
            deleteBtn.disabled = true;
            deleteBtn.classList.remove('pulse');
        }
    }

    // Add event listeners
    checkboxes.forEach(cb => cb.addEventListener('change', checkAllConditions));
    finalConfirm.addEventListener('input', checkAllConditions);

    // Handle delete button click
    deleteBtn.addEventListener('click', function() {
        if (!deleteBtn.disabled) {
            // Final confirmation
            const confirmed = confirm(
                '🚨 KONFIRMASI TERAKHIR - RESET TOTAL DATABASE 🚨\n\n' +
                'Apakah Anda BENAR-BENAR YAKIN ingin menghapus SEMUA DATA?\n\n' +
                'Data yang akan dihapus:\n' +
                '❌ SEMUA absensi\n' +
                '❌ SEMUA jadwal kelas\n' +
                '❌ SEMUA data siswa\n' +
                '❌ SEMUA kelas\n' +
                '❌ SEMUA jurusan\n' +
                '❌ SEMUA jam sekolah\n' +
                '❌ SEMUA users non-admin\n\n' +
                '⚠️ TINDAKAN INI TIDAK DAPAT DIBATALKAN! ⚠️\n' +
                '⚠️ DATABASE AKAN DIRESET TOTAL! ⚠️\n\n' +
                'Klik OK untuk RESET DATABASE atau Cancel untuk membatalkan.'
            );

            if (confirmed) {
                // Show loading state
                deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menghapus SEMUA DATA...';
                deleteBtn.disabled = true;
                
                // Use AJAX to submit form and handle response
                $.ajax({
                    url: "{{ route('admin.delete-dummy') }}",
                    method: 'DELETE',
                    data: {
                        token: $('input[name="token"]').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            alert('✅ BERHASIL!\n\n' + response.message + '\n\n' +
                                  'Total data yang dihapus: ' + response.total_deleted + ' records\n\n' +
                                  'Anda akan diarahkan ke halaman utama.');
                            
                            // Redirect to home page
                            window.location.href = response.redirect_url || "{{ route('absensi.index') }}";
                        } else {
                            alert('❌ GAGAL!\n\n' + response.message);
                            // Reset button
                            deleteBtn.innerHTML = '<i class="fas fa-trash-alt me-2"></i>🚨 RESET DATABASE - HAPUS SEMUA DATA 🚨';
                            deleteBtn.disabled = false;
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Terjadi kesalahan saat menghapus data.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        alert('❌ ERROR!\n\n' + errorMessage);
                        
                        // Reset button
                        deleteBtn.innerHTML = '<i class="fas fa-trash-alt me-2"></i>🚨 RESET DATABASE - HAPUS SEMUA DATA 🚨';
                        deleteBtn.disabled = false;
                    }
                });
            }
        }
    });

    // Auto-logout countdown
    const expiryTime = new Date("{{ session('admin_token_expires')->format('Y-m-d H:i:s') }}");
    const now = new Date();
    const timeLeft = expiryTime - now;

    if (timeLeft > 0) {
        setTimeout(() => {
            alert('Session admin telah berakhir. Anda akan diarahkan ke halaman utama.');
            window.location.href = "{{ route('absensi.index') }}";
        }, timeLeft);
    }
});
</script>
@endpush

@push('styles')
<style>
.card-custom {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.bg-gradient-danger {
    background: linear-gradient(135deg, #dc3545, #c82333);
}

.bg-purple {
    background-color: #6f42c1 !important;
}

.pulse {
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.btn-lg {
    padding: 12px 24px;
    font-size: 1.1em;
}

.form-check-label {
    cursor: pointer;
}

.border-danger {
    border-width: 2px !important;
}
</style>
@endpush
