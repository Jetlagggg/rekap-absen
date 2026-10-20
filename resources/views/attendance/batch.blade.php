<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Absensi Mingguan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8fafc; font-family: sans-serif; }
        .card-custom { border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-radius: 12px; }
        .header-custom { background: #4f46e5; color: white; padding: 20px; border-radius: 12px 12px 0 0; }
        .input-time { text-align: center; font-weight: bold; }
    </style>
</head>
<body>

<div class="container my-5">
    <div class="card card-custom">
        <div class="header-custom d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold"><i class="bi bi-calendar-week me-2"></i>Input Rekap Mingguan</h4>
            <a href="{{ route('attendance.create') }}" class="btn btn-outline-light btn-sm">Kembali ke Dashboard</a>
        </div>
        <div class="card-body p-4">
            
            <form action="{{ route('attendance.batch_store') }}" method="POST">
                @csrf
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="fw-bold mb-2">1. Pilih Pegawai</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">-- Pilih Nama --</option>
                            @foreach($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="fw-bold mb-2">2. Tanggal Awal (Senin)</label>
                        <input type="date" id="startDate" class="form-control" onchange="generateRows()" required>
                        <div class="form-text">Pilih hari Senin, sistem akan buatkan baris s/d Minggu.</div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mb-2 gap-2">
                    <button type="button" class="btn btn-outline-success btn-sm fw-bold" onclick="isiDefault()">
                        <i class="bi bi-magic"></i> Isi Semua Tepat Waktu (07:30 - 16:00)
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm fw-bold" onclick="resetForm()">
                        <i class="bi bi-trash"></i> Reset
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="bg-light text-center">
                            <tr>
                                <th style="width: 200px;">Tanggal</th>
                                <th>Jam Masuk (07:30)</th>
                                <th>Jam Pulang (16:00)</th>
                                <th style="width: 50px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    Silakan pilih <b>Tanggal Awal</b> di atas terlebih dahulu.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary fw-bold py-3">
                        SIMPAN DATA MINGGUAN <i class="bi bi-save ms-2"></i>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    function generateRows() {
        const startDateVal = document.getElementById('startDate').value;
        if (!startDateVal) return;

        const tableBody = document.getElementById('tableBody');
        tableBody.innerHTML = ''; 

        let currentDate = new Date(startDateVal);

        for (let i = 0; i < 7; i++) {
            let dateStr = currentDate.toISOString().split('T')[0];
            
            let options = { weekday: 'long', day: 'numeric', month: 'short' };
            let labelStr = currentDate.toLocaleDateString('id-ID', options);
            
            let dayNum = currentDate.getDay();
            let bgClass = (dayNum === 0 || dayNum === 6) ? 'bg-danger bg-opacity-10' : '';

            let row = `
                <tr class="${bgClass}">
                    <td>
                        <input type="hidden" name="absen[${i}][tanggal]" value="${dateStr}">
                        <span class="fw-bold text-dark">${labelStr}</span>
                    </td>
                    <td>
                        <input type="time" name="absen[${i}][jam_masuk]" class="form-control input-time jam-masuk">
                    </td>
                    <td>
                        <input type="time" name="absen[${i}][jam_pulang]" class="form-control input-time jam-pulang">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-light text-danger" onclick="this.closest('tr').remove()">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </td>
                </tr>
            `;
            tableBody.insertAdjacentHTML('beforeend', row);

            currentDate.setDate(currentDate.getDate() + 1);
        }
    }

    function isiDefault() {
        document.querySelectorAll('.jam-masuk').forEach(el => el.value = '07:30');
        document.querySelectorAll('.jam-pulang').forEach(el => el.value = '16:00');
    }

    function resetForm() {
        document.querySelectorAll('.jam-masuk').forEach(el => el.value = '');
        document.querySelectorAll('.jam-pulang').forEach(el => el.value = '');
    }
</script>

</body>
</html>