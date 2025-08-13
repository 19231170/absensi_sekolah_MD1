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

                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading"><i class="fas fa-info-circle"></i> Informasi</h6>
                        <ul class="mb-0">
                            <li>Silahkan download template Excel atau CSV terlebih dahulu</li>
                            <li>Isi data sesuai format yang telah ditentukan</li>
                            <li>Pastikan data kelas dan jurusan sudah ada di sistem</li>
                            <li>Jika NIS sudah ada, data siswa akan diupdate</li>
                            <li>File yang diupload harus berformat <strong>.xlsx</strong>, <strong>.xls</strong>, atau <strong>.csv</strong></li>
                            <li>Jika mengalami masalah dengan format file, coba gunakan format CSV</li>
                        </ul>
                    </div>

                    <form method="POST" action="{{ route('siswa.import.excel') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="file" class="form-label">File Import <span class="text-danger">*</span></label>
                            <input type="file" class="form-control {{ $errors->has('file') ? 'is-invalid' : '' }}" 
                                   id="file" name="file" required accept=".xlsx,.xls,.csv">
                            <div class="form-text">
                                Format yang didukung: <strong>.xlsx</strong>, <strong>.xls</strong>, <strong>.csv</strong>
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
                    <h5 class="mb-0">Petunjuk Format Excel</h5>
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
                                    <td><strong>nis</strong></td>
                                    <td>Nomor Induk Siswa (wajib)</td>
                                    <td>12345678</td>
                                </tr>
                                <tr>
                                    <td><strong>nama</strong></td>
                                    <td>Nama lengkap siswa (wajib)</td>
                                    <td>John Doe</td>
                                </tr>
                                <tr>
                                    <td><strong>tingkat</strong></td>
                                    <td>Tingkat kelas (wajib)</td>
                                    <td>X, XI, XII</td>
                                </tr>
                                <tr>
                                    <td><strong>kelas</strong></td>
                                    <td>Nama kelas (wajib)</td>
                                    <td>A, B, C</td>
                                </tr>
                                <tr>
                                    <td><strong>jurusan</strong></td>
                                    <td>Nama jurusan (wajib, harus sama persis dengan jurusan yang ada di sistem)</td>
                                    <td>TKJ, RPL, MM</td>
                                </tr>
                                <tr>
                                    <td><strong>jenis_kelamin</strong></td>
                                    <td>Jenis kelamin: L atau P</td>
                                    <td>L</td>
                                </tr>
                                <tr>
                                    <td><strong>tanggal_lahir</strong></td>
                                    <td>Format: DD/MM/YYYY</td>
                                    <td>01/01/2008</td>
                                </tr>
                                <tr>
                                    <td><strong>alamat</strong></td>
                                    <td>Alamat lengkap</td>
                                    <td>Jl. Contoh No. 123</td>
                                </tr>
                                <tr>
                                    <td><strong>nomor_hp</strong></td>
                                    <td>Nomor telepon</td>
                                    <td>081234567890</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-warning mt-3">
                        <h6 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Catatan Penting:</h6>
                        <ol class="mb-0">
                            <li>Pastikan semua kolom wajib terisi dengan benar.</li>
                            <li>Kolom jurusan harus sama persis dengan yang ada di sistem, termasuk huruf besar/kecil.</li>
                            <li>NIS tidak boleh duplikat, jika NIS sudah ada, data akan diupdate.</li>
                            <li>QR code akan digenerate otomatis oleh sistem.</li>
                            <li>Jika terdapat kesalahan format, sistem akan memberikan pesan error.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
