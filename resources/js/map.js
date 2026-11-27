/*
 * Modul 3 — Peta drop point (SIG) dengan Leaflet + OpenStreetMap.
 * File ini hanya dimuat di halaman yang memiliki peta:
 *   @push('head') @vite('resources/js/map.js') @endpush
 *
 * Dua jenis peta (dikenali dari id elemen):
 *   #drop-point-picker : peta di form/detail drop point (klik untuk memilih lokasi)
 *   #drop-point-map    : peta semua drop point + data real time (fetch tiap 30 detik)
 */
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const REFRESH_EVERY_MS = 30000;

/* ---------- Fungsi bantu ---------- */

function createMap(element, center, zoom) {
    const map = L.map(element).setView(center, zoom);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(map);

    return map;
}

// Marker berbentuk pin, dibuat dengan HTML/CSS agar tidak perlu file gambar
function pinIcon(color) {
    return L.divIcon({
        className: '',
        html: `<span style="display:block;width:24px;height:24px;background:${color};border:3px solid #fff;
               border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 1px 4px rgba(0,0,0,.4)"></span>`,
        iconSize: [24, 24],
        iconAnchor: [12, 24],
        popupAnchor: [0, -24],
    });
}

const ICON_ACTIVE = pinIcon('#059669'); // emerald-600
const ICON_INACTIVE = pinIcon('#9ca3af'); // gray-400

// Mencegah teks dari database dibaca sebagai HTML (keamanan XSS)
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

// Jarak dua titik di permukaan bumi (rumus Haversine), hasil dalam kilometer
function distanceKm(from, to) {
    const toRad = (degree) => (degree * Math.PI) / 180;
    const earthRadiusKm = 6371;
    const dLat = toRad(to.lat - from.lat);
    const dLng = toRad(to.lng - from.lng);
    const a = Math.sin(dLat / 2) ** 2
        + Math.cos(toRad(from.lat)) * Math.cos(toRad(to.lat)) * Math.sin(dLng / 2) ** 2;

    return earthRadiusKm * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function formatDistance(km) {
    return km < 1 ? `${Math.round(km * 1000)} m` : `${km.toFixed(1).replace('.', ',')} km`;
}

function formatWeight(kg) {
    return Number(kg).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function routeUrl(dropPoint, userPosition) {
    if (userPosition) {
        return `https://www.openstreetmap.org/directions?engine=fossgis_osrm_foot&route=`
            + `${userPosition.lat},${userPosition.lng};${dropPoint.latitude},${dropPoint.longitude}`;
    }

    return `https://www.openstreetmap.org/?mlat=${dropPoint.latitude}&mlon=${dropPoint.longitude}`
        + `#map=18/${dropPoint.latitude}/${dropPoint.longitude}`;
}

/* ---------- 1. Peta pemilih lokasi (form & detail drop point) ---------- */

function initPicker(element) {
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const readonly = element.dataset.readonly === '1';

    const hasPoint = element.dataset.lat !== '' && element.dataset.lng !== '';
    const center = hasPoint
        ? [Number(element.dataset.lat), Number(element.dataset.lng)]
        : [Number(element.dataset.centerLat), Number(element.dataset.centerLng)];

    const map = createMap(element, center, hasPoint ? 17 : 15);
    let marker = null;

    function setPoint(latlng, moveMap = false) {
        if (!marker) {
            marker = L.marker(latlng, { icon: ICON_ACTIVE, draggable: !readonly }).addTo(map);
            marker.on('dragend', () => setPoint(marker.getLatLng()));
        }

        marker.setLatLng(latlng);

        if (latInput && lngInput) {
            latInput.value = latlng.lat.toFixed(7);
            lngInput.value = latlng.lng.toFixed(7);
        }

        if (moveMap) {
            map.flyTo(latlng, 17);
        }
    }

    if (hasPoint) {
        setPoint(L.latLng(center));
    }

    if (readonly) {
        return;
    }

    // Klik peta untuk memilih lokasi
    map.on('click', (event) => setPoint(event.latlng));

    // Mengetik koordinat secara manual juga memindahkan marker
    [latInput, lngInput].forEach((input) => input?.addEventListener('change', () => {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);

        if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
            setPoint(L.latLng(lat, lng), true);
        }
    }));

    document.getElementById('picker-my-location')?.addEventListener('click', () => {
        if (!navigator.geolocation) {
            alert('Browser tidak mendukung fitur lokasi.');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => setPoint(L.latLng(position.coords.latitude, position.coords.longitude), true),
            () => alert('Lokasi tidak dapat diambil. Pastikan izin lokasi diberikan.'),
            { enableHighAccuracy: true },
        );
    });

    document.getElementById('picker-clear')?.addEventListener('click', () => {
        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }
        latInput.value = '';
        lngInput.value = '';
    });
}

/* ---------- 2. Peta semua drop point + data real time ---------- */

function initDropPointMap(element) {
    const map = createMap(element, [Number(element.dataset.centerLat), Number(element.dataset.centerLng)], 15);
    const listElement = document.getElementById('drop-point-list');
    const updatedAtElement = document.getElementById('map-updated-at');
    const errorElement = document.getElementById('map-error');

    const markers = {}; // id drop point -> marker Leaflet
    let dropPoints = [];
    let userPosition = null;
    let userMarker = null;
    let accuracyCircle = null;
    let firstLoad = true;

    function popupHtml(dropPoint) {
        const officers = dropPoint.officers.length
            ? dropPoint.officers.map(escapeHtml).join(', ')
            : '<em>Belum ada petugas</em>';
        const distance = userPosition
            ? `<br>Jarak: <strong>${formatDistance(dropPoint.distance)}</strong>`
            : '';
        const inactive = dropPoint.is_active ? '' : '<br><strong style="color:#dc2626">Nonaktif</strong>';

        return `<strong>${escapeHtml(dropPoint.name)}</strong><br>
            ${escapeHtml(dropPoint.address)}${inactive}<br>
            Petugas: ${officers}<br>
            Sedang terkumpul: ${dropPoint.collected_count} setoran (${formatWeight(dropPoint.collected_weight)} kg)
            ${distance}<br>
            <a href="${routeUrl(dropPoint, userPosition)}" target="_blank" rel="noopener">Lihat rute ↗</a>`;
    }

    function hasCoordinates(dropPoint) {
        return dropPoint.latitude !== null && dropPoint.longitude !== null;
    }

    // Hitung jarak dari posisi user lalu urutkan dari yang terdekat
    function sortByDistance() {
        dropPoints.forEach((dropPoint) => {
            dropPoint.distance = userPosition && hasCoordinates(dropPoint)
                ? distanceKm(userPosition, { lat: dropPoint.latitude, lng: dropPoint.longitude })
                : null;
        });

        if (userPosition) {
            dropPoints.sort((a, b) => (a.distance ?? Infinity) - (b.distance ?? Infinity));
        }
    }

    function renderMarkers() {
        const ids = dropPoints.map((dropPoint) => dropPoint.id);

        // Hapus marker drop point yang sudah tidak ada / tidak aktif
        Object.keys(markers).forEach((id) => {
            if (!ids.includes(Number(id))) {
                map.removeLayer(markers[id]);
                delete markers[id];
            }
        });

        dropPoints.filter(hasCoordinates).forEach((dropPoint) => {
            const latlng = [dropPoint.latitude, dropPoint.longitude];
            const icon = dropPoint.is_active ? ICON_ACTIVE : ICON_INACTIVE;

            if (markers[dropPoint.id]) {
                markers[dropPoint.id].setLatLng(latlng).setIcon(icon).setPopupContent(popupHtml(dropPoint));
            } else {
                markers[dropPoint.id] = L.marker(latlng, { icon }).bindPopup(popupHtml(dropPoint)).addTo(map);
            }
        });
    }

    function renderList() {
        if (!listElement) {
            return;
        }

        if (dropPoints.length === 0) {
            listElement.innerHTML = '<p class="py-6 text-center text-sm text-gray-500">Belum ada drop point aktif.</p>';
            return;
        }

        listElement.innerHTML = dropPoints.map((dropPoint, index) => {
            const nearest = userPosition && index === 0 && dropPoint.distance !== null;
            const officers = dropPoint.officers.length ? dropPoint.officers.map(escapeHtml).join(', ') : 'Belum ada petugas';
            const distance = dropPoint.distance !== null
                ? `<span class="font-semibold text-emerald-700">${formatDistance(dropPoint.distance)}</span>`
                : '';
            const actions = hasCoordinates(dropPoint)
                ? `<button type="button" data-focus="${dropPoint.id}" class="text-emerald-700 hover:underline">Lihat di peta</button>
                   <a href="${routeUrl(dropPoint, userPosition)}" target="_blank" rel="noopener" class="text-emerald-700 hover:underline">Rute ↗</a>`
                : '<span class="text-gray-400">Lokasi belum diatur</span>';

            return `<div class="rounded-lg border ${nearest ? 'border-emerald-300 bg-emerald-50' : 'border-gray-200 bg-white'} p-4">
                <div class="flex items-start justify-between gap-2">
                    <p class="font-semibold text-gray-900">${escapeHtml(dropPoint.name)}</p>
                    ${distance}
                </div>
                ${nearest ? '<p class="mt-1 text-xs font-semibold text-emerald-700">Terdekat dari lokasi Anda</p>' : ''}
                <p class="mt-1 text-sm text-gray-600">${escapeHtml(dropPoint.address)}</p>
                <p class="mt-2 text-xs text-gray-500">Petugas: ${officers}</p>
                <p class="text-xs text-gray-500">Sedang terkumpul: ${dropPoint.collected_count} setoran (${formatWeight(dropPoint.collected_weight)} kg)</p>
                <div class="mt-3 flex gap-4 text-sm">${actions}</div>
            </div>`;
        }).join('');
    }

    function render() {
        sortByDistance();
        renderMarkers();
        renderList();
    }

    // Ambil data terbaru dari server (Fetch API + async/await)
    async function loadData() {
        try {
            const response = await fetch(element.dataset.url, {
                headers: {
                    Accept: 'application/json',
                    'X-Background-Request': '1', // tidak dihitung sebagai aktivitas pengguna
                },
            });

            if (response.status === 401) {
                window.location.reload(); // sesi berakhir: server mengarahkan ke login
                return;
            }

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            dropPoints = data.drop_points;
            render();

            if (firstLoad) {
                firstLoad = false;
                const points = dropPoints.filter(hasCoordinates).map((dp) => [dp.latitude, dp.longitude]);
                if (points.length) {
                    map.fitBounds(points, { padding: [40, 40], maxZoom: 17 });
                }
            }

            if (updatedAtElement) {
                updatedAtElement.textContent = `Diperbarui ${data.updated_at}`;
            }
            errorElement?.classList.add('hidden');
        } catch (error) {
            errorElement?.classList.remove('hidden');
        }
    }

    // Tombol "Lihat di peta" pada daftar
    listElement?.addEventListener('click', (event) => {
        const id = event.target.dataset.focus;
        if (id && markers[id]) {
            map.flyTo(markers[id].getLatLng(), 18);
            markers[id].openPopup();
            element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    // Tombol "Lokasi saya": posisi user dipantau terus (real time) dengan watchPosition
    document.getElementById('locate-me')?.addEventListener('click', (event) => {
        const button = event.currentTarget;
        const statusElement = document.getElementById('locate-status');

        if (!navigator.geolocation) {
            statusElement.textContent = 'Browser tidak mendukung fitur lokasi.';
            return;
        }

        button.disabled = true;
        statusElement.textContent = 'Mencari lokasi Anda...';
        let centered = false;

        navigator.geolocation.watchPosition(
            (position) => {
                userPosition = { lat: position.coords.latitude, lng: position.coords.longitude };
                const latlng = [userPosition.lat, userPosition.lng];

                if (!userMarker) {
                    userMarker = L.circleMarker(latlng, {
                        radius: 8, color: '#fff', weight: 3, fillColor: '#2563eb', fillOpacity: 1,
                    }).bindTooltip('Lokasi Anda').addTo(map);
                    accuracyCircle = L.circle(latlng, {
                        radius: position.coords.accuracy, color: '#2563eb', weight: 1, fillOpacity: 0.1,
                    }).addTo(map);
                } else {
                    userMarker.setLatLng(latlng);
                    accuracyCircle.setLatLng(latlng).setRadius(position.coords.accuracy);
                }

                render();
                statusElement.textContent = `Lokasi Anda terdeteksi (akurasi ±${Math.round(position.coords.accuracy)} m).`;

                // Pertama kali: tampilkan lokasi user dan drop point terdekat
                if (!centered) {
                    centered = true;
                    const nearest = dropPoints.find(hasCoordinates);
                    const bounds = nearest ? [latlng, [nearest.latitude, nearest.longitude]] : [latlng];
                    map.fitBounds(bounds, { padding: [60, 60], maxZoom: 17 });
                }
            },
            () => {
                button.disabled = false;
                statusElement.textContent = 'Lokasi tidak dapat diambil. Izinkan akses lokasi di browser lalu coba lagi.';
            },
            { enableHighAccuracy: true },
        );
    });

    loadData();
    setInterval(loadData, REFRESH_EVERY_MS);
}

/* ---------- Jalankan sesuai elemen yang ada di halaman ---------- */

document.addEventListener('DOMContentLoaded', () => {
    const picker = document.getElementById('drop-point-picker');
    const dropPointMap = document.getElementById('drop-point-map');

    if (picker) {
        initPicker(picker);
    }

    if (dropPointMap) {
        initDropPointMap(dropPointMap);
    }
});
