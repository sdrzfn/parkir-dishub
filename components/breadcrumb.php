<?php
if (!isset($breadcrumbs)) {
    $current_file = basename($_SERVER['PHP_SELF']);
    $page_map = [
        'index.php'              => [['label' => 'Dashboard', 'url' => '']],
        'lokasi.php'             => [
            ['label' => 'Dashboard', 'url' => 'index.php'],
            ['label' => 'Lokasi',    'url' => ''],
        ],
        'tukang-parkir.php'      => [
            ['label' => 'Dashboard',  'url' => 'index.php'],
            ['label' => 'Juru Parkir','url' => ''],
        ],
        'retribusi-parkir.php'   => [
            ['label' => 'Dashboard',  'url' => 'index.php'],
            ['label' => 'Retribusi',  'url' => ''],
        ],
        'retribusi-detail.php'   => [
            ['label' => 'Dashboard',  'url' => 'index.php'],
            ['label' => 'Retribusi',  'url' => 'retribusi-parkir.php'],
            ['label' => 'Detail',     'url' => ''],
        ],
        'koordinator-wilayah.php'=> [
            ['label' => 'Dashboard',          'url' => 'index.php'],
            ['label' => 'Koordinator Wilayah','url' => ''],
        ],
        'peta.php'               => [
            ['label' => 'Dashboard',   'url' => 'index.php'],
            ['label' => 'Peta Wilayah','url' => ''],
        ],
        'kelola-pengguna.php'    => [
            ['label' => 'Dashboard',     'url' => 'index.php'],
            ['label' => 'Kelola Pengguna','url' => ''],
        ],
        'detail-korwil.php'=> [
            ['label' => 'Dashboard',          'url' => 'index.php'],
            ['label' => 'Koordinator Wilayah','url' => 'koordinator-wilayah.php'],
            ['label' => 'Detail Korwil','url' => ''],
        ],
    ];
    $breadcrumbs = $page_map[$current_file] ?? [['label' => 'Dashboard', 'url' => 'index.php']];
}

// Tentukan URL back (item sebelum terakhir)
if (!isset($breadcrumb_back)) {
    $prev = array_filter($breadcrumbs, fn($b) => !empty($b['url']));
    $breadcrumb_back = !empty($prev) ? end($prev)['url'] : 'index.php';
}

// Label halaman aktif (item terakhir)
$last_crumb       = end($breadcrumbs);
$active_label     = $breadcrumb_title ?? $last_crumb['label'];
$base_url_full    = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

$json_items = [];
foreach ($breadcrumbs as $i => $crumb) {
    $json_items[] = [
        '@type'    => 'ListItem',
        'position' => $i + 1,
        'name'     => $crumb['label'],
        'item'     => !empty($crumb['url']) ? $base_url_full . '/' . $crumb['url'] : $base_url_full . $_SERVER['REQUEST_URI'],
    ];
}
$json_ld = json_encode([
    '@context'        => 'https://schema.org',
    '@type'           => 'BreadcrumbList',
    'itemListElement' => $json_items,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>

<script type="application/ld+json"><?= $json_ld ?></script>

<nav class="breadcrumb-nav" aria-label="Breadcrumb">
    <div class="breadcrumb-inner">

        <!-- Tombol Back -->
        <a href="<?= htmlspecialchars($breadcrumb_back) ?>"
           class="breadcrumb-back"
           aria-label="Kembali ke halaman sebelumnya">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5"/><path d="M12 5l-7 7 7 7"/>
            </svg>
        </a>

        <!-- Separator -->
        <span class="breadcrumb-sep-main" aria-hidden="true"></span>

        <!-- Breadcrumb list -->
        <ol class="breadcrumb-list" aria-label="breadcrumb">
            <?php foreach ($breadcrumbs as $i => $crumb):
                $is_last = ($i === array_key_last($breadcrumbs));
            ?>
                <li class="breadcrumb-item <?= $is_last ? 'breadcrumb-item--active' : '' ?>">
                    <?php if (!$is_last && !empty($crumb['url'])): ?>
                        <a href="<?= htmlspecialchars($crumb['url']) ?>"
                           class="breadcrumb-link">
                            <?php if ($i === 0): ?>
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                     style="margin-right:4px; vertical-align:-1px;">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                    <polyline points="9 22 9 12 15 12 15 22"/>
                                </svg>
                            <?php endif; ?>
                            <?= htmlspecialchars($crumb['label']) ?>
                        </a>
                    <?php else: ?>
                        <span class="breadcrumb-current"
                              aria-current="page">
                            <?= htmlspecialchars($crumb['label']) ?>
                        </span>
                    <?php endif; ?>

                    <?php if (!$is_last): ?>
                        <svg class="breadcrumb-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                             aria-hidden="true">
                            <path d="M9 18l6-6-6-6"/>
                        </svg>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>

    </div>
</nav>

<style>
/* ── Breadcrumb Component ───────────────────────────────────── */
.breadcrumb-nav {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.breadcrumb-inner {
    display: flex;
    align-items: center;
    gap: 0;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 8px 14px;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    max-width: 100%;
    overflow: hidden;
}

/* Tombol back */
.breadcrumb-back {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 8px;
    color: #64748b;
    text-decoration: none;
    flex-shrink: 0;
    transition: background 0.15s ease, color 0.15s ease;
}

.breadcrumb-back:hover {
    background: #f1f5f9;
    color: #1e293b;
}

/* Garis pemisah antara back button dan list */
.breadcrumb-sep-main {
    width: 1px;
    height: 18px;
    background: #e2e8f0;
    margin: 0 12px;
    flex-shrink: 0;
}

/* List */
.breadcrumb-list {
    display: flex;
    align-items: center;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 4px;
    flex-wrap: wrap;
    overflow: hidden;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
    gap: 4px;
    min-width: 0;
}

/* Link breadcrumb (bukan aktif) */
.breadcrumb-link {
    font-size: 0.8rem;
    font-weight: 500;
    color: #64748b;
    text-decoration: none;
    white-space: nowrap;
    display: flex;
    align-items: center;
    padding: 2px 6px;
    border-radius: 6px;
    transition: background 0.15s ease, color 0.15s ease;
}

.breadcrumb-link:hover {
    background: #f1f5f9;
    color: #1e293b;
}

/* Item aktif (halaman sekarang) */
.breadcrumb-current {
    font-size: 0.8rem;
    font-weight: 700;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 200px;
    padding: 2px 6px;
}

/* Chevron separator */
.breadcrumb-chevron {
    color: #cbd5e1;
    flex-shrink: 0;
}

/* ── Responsive ─────────────────────────────────────────────── */
@media (max-width: 768px) {
    .breadcrumb-inner {
        padding: 6px 10px;
    }

    .breadcrumb-link {
        font-size: 0.75rem;
    }

    .breadcrumb-current {
        font-size: 0.75rem;
        max-width: 120px;
    }

    /* Sembunyikan item tengah di mobile (hanya tampilkan pertama & terakhir) */
    .breadcrumb-item:not(:first-child):not(:last-child) {
        display: none;
    }

    /* Tambahkan ellipsis sebelum item terakhir */
    .breadcrumb-item:last-child::before {
        content: '…';
        color: #94a3b8;
        font-size: 0.75rem;
        margin-right: 4px;
    }
}

@media (max-width: 480px) {
    .breadcrumb-back {
        width: 26px;
        height: 26px;
    }

    .breadcrumb-sep-main {
        margin: 0 8px;
    }
}
</style>