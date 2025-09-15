@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Import Data Siswa</h5>
                    <div>
                        <div class="dropdown d-inline-block me-2">
                            <button class="btn btn-sm btn-info dropdown-toggle" type="button" id="downloadTemplateDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-download"></i> Download Template
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="downloadTemplateDropdown">
                                <li><a class="dropdown-item" href="{{ route('siswa.template.download') }}">Format CSV</a></li>
                                <li><a class="dropdown-item" href="{{ route('siswa.template.download.excel') }}">Format Excel</a></li>
                            </ul>
                        </div>
                        <a href="{{ route('siswa.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            {{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('import_errors') && is_array(session('import_errors')))
                        <div class="alert alert-danger">
                            <p class="mb-2"><strong>Detail Error:</strong></p>
                            <ul class="mb-0">
                                @foreach(session('import_errors') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @php
                        $zipArchiveAvailable = class_exists('ZipArchive');
                    @endphp
                    
                    @if(!$zipArchiveAvailable)
                    <div class="alert alert-warning mb-4">
                        <h6 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Peringatan: Ekstensi ZIP PHP Tidak Tersedia</h6>
                        <p class="mb-0">Server tidak memiliki ekstensi PHP ZipArchive yang dibutuhkan untuk membaca file Excel. Silahkan:</p>
                        <ul class="mb-0">
                            <li><strong>Gunakan file CSV</strong> sebagai gantinya (Excel tidak didukung), atau</li>
                            <li>Minta administrator server untuk mengaktifkan ekstensi PHP zip</li>
                        </ul>
                    </div>
                    @endif
                    
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading"><i class="fas fa-info-circle"></i> Format Import Baru (Disederhanakan)</h6>
                        <ul class="mb-0">
                            <li><strong>Hanya 5 kolom yang dibutuhkan:</strong> nama_siswa, nis, jenis_kelamin, jurusan, kelas</li>
                            <li>Silahkan download template Excel atau CSV terlebih dahulu</li>
                            <li>Kolom <strong>kelas</strong> menggunakan format gabungan: "10 A", "11 IPA 1", "12 TKJ 2"</li>
                            <li>Sistem akan otomatis membuat jurusan dan kelas jika belum ada</li>
                            <li>QR code akan digenerate otomatis untuk setiap siswa</li>
                            <li>Jika NIS sudah ada, data siswa akan diupdate</li>
                            <li>File yang diupload harus berformat <strong>{{ $zipArchiveAvailable ? '.xlsx, .xls, atau .csv' : 'CSV saja (.csv)' }}</strong>{{ !$zipArchiveAvailable ? ' (file Excel tidak didukung karena tidak ada ekstensi ZipArchive)' : '' }}</li>
                        </ul>
                    </div>

                    <form method="POST" action="{{ route('siswa.import.excel') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="file" class="form-label">File Import <span class="text-danger">*</span></label>
                            <input type="file" class="form-control {{ $errors->has('file') ? 'is-invalid' : '' }}" 
                                   id="file" name="file" required accept="{{ $zipArchiveAvailable ? '.xlsx,.xls,.csv' : '.csv' }}">
                            <div class="form-text">
                                Format yang didukung: <strong>{{ $zipArchiveAvailable ? '.xlsx, .xls, .csv' : '.csv saja' }}</strong>
                            </div>
                            @if($errors->has('file'))
                                <div class="invalid-feedback">{{ $errors->first('file') }}</div>
                            @endif
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-file-import"></i> Import Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Format Import Baru (Disederhanakan)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Kolom</th>
                                    <th>Keterangan</th>
                                    <th>Contoh</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>nama_siswa</strong></td>
                                    <td>Nama lengkap siswa (wajib)</td>
                                    <td>Ahmad Fauzi</td>
                                </tr>
                                <tr>
                                    <td><strong>nis</strong></td>
                                    <td>Nomor Induk Siswa (wajib, unik)</td>
                                    <td>2024001</td>
                                </tr>
                                <tr>
                                    <td><strong>jenis_kelamin</strong></td>
                                    <td>Jenis kelamin: L atau P (wajib)</td>
                                    <td>L</td>
                                </tr>
                                <tr>
                                    <td><strong>jurusan</strong></td>
                                    <td>Nama jurusan lengkap (wajib)</td>
                                    <td>Teknik Komputer dan Jaringan</td>
                                </tr>
                                <tr>
                                    <td><strong>kelas</strong></td>
                                    <td>Format gabungan tingkat + nama kelas (wajib)</td>
                                    <td>10 A, 11 IPA 1, 12 TKJ 2</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-success mt-3">
                        <h6 class="alert-heading"><i class="fas fa-check-circle"></i> Keunggulan Format Baru:</h6>
                        <ol class="mb-0">
                            <li><strong>Lebih Sederhana:</strong> Hanya 5 kolom yang diperlukan</li>
                            <li><strong>Auto-Create:</strong> Jurusan dan kelas akan dibuat otomatis jika belum ada</li>
                            <li><strong>Format Gabungan:</strong> Kolom "kelas" menggabungkan tingkat dan nama kelas</li>
                            <li><strong>Validasi Ketat:</strong> Data akan divalidasi secara otomatis</li>
                            <li><strong>Error Handling:</strong> Pesan error yang lebih jelas dan detail</li>
                        </ol>
                    </div>

                    <div class="alert alert-warning mt-3">
                        <h6 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Contoh Data:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th>nama_siswa</th>
                                        <th>nis</th>
                                        <th>jenis_kelamin</th>
                                        <th>jurusan</th>
                                        <th>kelas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Ahmad Fauzi</td>
                                        <td>2024001</td>
                                        <td>L</td>
                                        <td>Teknik Komputer dan Jaringan</td>
                                        <td>10 A</td>
                                    </tr>
                                    <tr>
                                        <td>Siti Nurhaliza</td>
                                        <td>2024002</td>
                                        <td>P</td>
                                        <td>Rekayasa Perangkat Lunak</td>
                                        <td>11 IPA 1</td>
                                    </tr>
                                    <tr>
                                        <td>Dewi Sartika</td>
                                        <td>2024003</td>
                                        <td>P</td>
                                        <td>Akuntansi dan Keuangan Lembaga</td>
                                        <td>12 AKL 2</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
