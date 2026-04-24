<div class="filter-container"
    style="font-family: 'Inter', sans-serif; max-width: 900px; background: #ffffff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">

    <div style="display: flex; gap: 10px; margin-bottom: 15px;">
        <div style="flex: 1; position: relative;">
            <input type="text" placeholder="Cari Kode Lokasi atau Nama Jalan..."
                style="width: 100%; padding: 12px 15px; border-radius: 8px; border: 1px solid #cbd5e1; background: #f8fafc; font-size: 0.95rem; outline: none; transition: border-color 0.2s;">
        </div>
        <button
            style="padding: 0 25px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
            Cari Data
        </button>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">

        <div>
            <label
                style="display: block; font-size: 0.8rem; font-weight: 600; color: #64748b; margin-bottom: 5px;">Kecamatan</label>
            <select
                style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; font-size: 0.85rem; color: #1e293b;">
                <option value="">Semua Kecamatan</option>
                <option value="sidoarjo">Sidoarjo Kota</option>
                <option value="waru">Waru</option>
                <option value="taman">Taman</option>
                <option value="krian">Krian</option>
                <option value="gedangan">Gedangan</option>
            </select>
        </div>

        <div>
            <label
                style="display: block; font-size: 0.8rem; font-weight: 600; color: #64748b; margin-bottom: 5px;">Kategori
                Layanan</label>
            <select
                style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; font-size: 0.85rem; color: #1e293b;">
                <option value="">Semua Layanan</option>
                <option value="parkir">Titik Parkir</option>
                <option value="pju">Penerangan Jalan (PJU)</option>
                <option value="atcs">Titik CCTV / ATCS</option>
                <option value="terminal">Terminal / Halte</option>
            </select>
        </div>

        <div>
            <label
                style="display: block; font-size: 0.8rem; font-weight: 600; color: #64748b; margin-bottom: 5px;">Status</label>
            <div style="display: flex; gap: 10px; align-items: center; height: 40px;">
                <label
                    style="display: flex; align-items: center; gap: 5px; font-size: 0.85rem; color: #1e293b; cursor: pointer;">
                    <input type="checkbox" checked> Aktif
                </label>
                <label
                    style="display: flex; align-items: center; gap: 5px; font-size: 0.85rem; color: #1e293b; cursor: pointer;">
                    <input type="checkbox"> Non-Aktif
                </label>
            </div>
        </div>

    </div>

    <div style="margin-top: 15px; text-align: right;">
        <button
            style="background: none; border: none; color: #ef4444; font-size: 0.85rem; font-weight: 500; cursor: pointer; text-decoration: underline;">
            Reset Filter
        </button>
    </div>
</div>