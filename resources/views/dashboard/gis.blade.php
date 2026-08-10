@extends('layouts.app')

@section('title', 'Dashboard GIS')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@endpush

@section('content')
    <x-page-header title="Dashboard GIS"
                   subtitle="Peta sebaran desa & kelompok KKN aktif di Kabupaten Indramayu."
                   icon="bi-map" />

    <div class="row g-3">
        <div class="col-lg-9">
            <x-card :bodyClass="'p-0'">
                <div id="petaDesa" style="height: 560px; border-radius: 0 0 var(--radius, .5rem) var(--radius, .5rem);"></div>
            </x-card>
        </div>
        <div class="col-lg-3">
            <x-card title="Info Desa" :bodyClass="'p-3'">
                <div id="infoDesa">
                    <p class="text-muted small mb-0">Klik marker desa untuk melihat detail.</p>
                </div>
            </x-card>
            <div class="mt-3">
                <x-card :bodyClass="'p-3'">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Total Desa</span>
                        <strong id="statDesa">-</strong>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <span class="text-muted small">Kelompok Aktif</span>
                        <strong id="statKelompok">-</strong>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        (function () {
            'use strict';

            // H2: escape nilai user sebelum dimasukkan ke innerHTML (cek XSS).
            function esc(v) {
                var d = document.createElement('div');
                d.textContent = v == null ? '' : String(v);
                return d.innerHTML;
            }

            var dataUrl = @json(route('dashboard.gis.data'));

            fetch(dataUrl)
                .then(function (r) { return r.json(); })
                .then(function (geo) {
                    document.getElementById('statDesa').textContent = geo.meta.total_desa;
                    document.getElementById('statKelompok').textContent = geo.meta.total_kelompok;

                    var map = L.map('petaDesa').setView([-6.3278, 108.3201], 10);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);

                    var info = document.getElementById('infoDesa');

                    L.geoJSON(geo, {
                        pointToLayer: function (feature, latlng) {
                            var ikon = feature.properties.kelompok.length > 0
                                ? L.divIcon({ className: 'gis-marker gis-marker-aktif', html: '<i class="bi bi-geo-alt-fill"></i>' })
                                : L.divIcon({ className: 'gis-marker', html: '<i class="bi bi-geo-alt"></i>' });
                            return L.marker(latlng, { icon: ikon });
                        },
                        onEachFeature: function (feature, layer) {
                            layer.bindPopup('<strong>' + esc(feature.properties.nama_desa) + '</strong><br><small>' + esc(feature.properties.kecamatan) + '</small>');
                            layer.on('click', function () {
                                var p = feature.properties;
                                var kelompok = p.kelompok.length
                                    ? '<h6 class="mt-3 mb-1">Kelompok KKN Aktif</h6>' + p.kelompok.map(function (k) {
                                        return '<div class="small border-bottom py-1"><strong>' + esc(k.kode) + '</strong> — ' + esc(k.tema) + '</div>';
                                      }).join('')
                                    : '<p class="text-muted small mt-2 mb-0">Belum ada kelompok aktif.</p>';
                                info.innerHTML = '<h6 class="mb-1">' + esc(p.nama_desa) + '</h6>'
                                    + '<small class="text-muted">Kec. ' + esc(p.kecamatan) + ' · Kode ' + esc(p.kode) + '</small>'
                                    + (p.penduduk ? '<div class="small mt-2"><i class="bi bi-people me-1"></i>' + Number(p.penduduk).toLocaleString('id-ID') + ' penduduk</div>' : '')
                                    + (p.profil ? '<div class="small text-muted mt-2">' + esc(p.profil) + '</div>' : '')
                                    + (p.potensi.length ? '<div class="small mt-2"><i class="bi bi-lightning me-1 text-teal"></i>' + p.potensi.map(esc).join(', ') + '</div>' : '')
                                    + kelompok;
                            });
                        }
                    }).addTo(map);
                });
        })();
    </script>
@endpush
