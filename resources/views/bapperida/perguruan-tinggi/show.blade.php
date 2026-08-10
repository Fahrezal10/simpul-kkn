@extends('layouts.app')

@section('title', 'Detail Perguruan Tinggi')

@section('content')
    <x-page-header :title="$pt->nama_pt" subtitle="Detail registrasi institusi dan persetujuan akun." icon="bi-building" />

    <div class="row g-3">
        <div class="col-lg-7">
            <x-card title="Data Institusi">
                <table class="table table-sm table-borderless detail-table mb-0">
                    <tbody>
                        <tr>
                            <th class="text-muted">Nama Perguruan Tinggi</th>
                            <td>{{ $pt->nama_pt }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Alamat</th>
                            <td>{{ $pt->alamat ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Email Akun</th>
                            <td>{{ $pt->user->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Dokumen Legalitas</th>
                            <td>
                                @if ($pt->dokumen_legalitas)
                                    <a href="{{ route('file.download', ['jenis' => 'legalitas', 'path' => $pt->dokumen_legalitas]) }}"
                                       target="_blank" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> Lihat Dokumen
                                    </a>
                                @else
                                    <span class="text-muted">Tidak diunggah</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Jumlah Permohonan</th>
                            <td>{{ $pt->permohonan_kkn_count ?? $pt->permohonanKkn->count() }}</td>
                        </tr>
                    </tbody>
                </table>
            </x-card>
        </div>

        <div class="col-lg-5">
            <x-card title="Penanggung Jawab (PIC)">
                <table class="table table-sm table-borderless detail-table mb-0">
                    <tbody>
                        <tr>
                            <th class="text-muted">Nama</th>
                            <td>{{ $pt->pic_nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Email</th>
                            <td>{{ $pt->pic_email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Telepon</th>
                            <td>{{ $pt->pic_telp ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Status Approval</th>
                            <td><x-status-badge :status="$pt->status_approval" /></td>
                        </tr>
                        @if ($pt->catatan_penolakan)
                            <tr>
                                <th class="text-muted">Catatan Penolakan</th>
                                <td class="text-danger">{{ $pt->catatan_penolakan }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </x-card>

            @if ($pt->status_approval === 'menunggu')
                <x-card title="Keputusan" class="mt-3">
                    <form method="POST" action="{{ route('bapperida.pt.approve', $pt) }}" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle me-1"></i> Setujui Akun
                        </button>
                    </form>

                    <form method="POST" action="{{ route('bapperida.pt.reject', $pt) }}">
                        @csrf
                        <div class="mb-2">
                            <label for="catatan_penolakan" class="form-label">Catatan Penolakan <span class="text-danger">*</span></label>
                            <textarea name="catatan_penolakan" id="catatan_penolakan" rows="2"
                                      class="form-control @error('catatan_penolakan') is-invalid @enderror"
                                      placeholder="Alasan penolakan, tampil untuk PT"></textarea>
                            @error('catatan_penolakan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-x-circle me-1"></i> Tolak Akun
                        </button>
                    </form>
                </x-card>
            @endif
        </div>
    </div>
@endsection
