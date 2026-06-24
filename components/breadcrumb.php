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
            ['label' => 'Dashboard',        'url' => 'index.php'],
            ['label' => 'Petugas Parkir',   'url' => ''],
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
            ['label' => 'Dashboard',           'url' => 'index.php'],
            ['label' => 'Koordinator Wilayah', 'url' => ''],
        ],
        'peta.php'               => [
            ['label' => 'Dashboard',    'url' => 'index.php'],
            ['label' => 'Peta Wilayah', 'url' => ''],
        ],
        'kelola-pengguna.php'    => [
            ['label' => 'Dashboard',      'url' => 'index.php'],
            ['label' => 'Kelola Pengguna','url' => ''],
        ],
        'detail-korwil.php'      => [
            ['label' => 'Dashboard',           'url' => 'index.php'],
            ['label' => 'Koordinator Wilayah', 'url' => 'koordinator-wilayah.php'],
            ['label' => 'Detail Korwil',       'url' => ''],
        ],
        'profile.php'            => [
            ['label' => 'Dashboard', 'url' => 'index.php'],
            ['label' => 'Profile',   'url' => ''],
        ],
    ];
    $breadcrumbs = $page_map[$current_file] ?? [['label' => 'Dashboard', 'url' => 'index.php']];
}

// Tentukan URL back (item sebelum terakhir)
if (!isset($breadcrumb_back)) {
    $prev = array_filter($breadcrumbs, fn($b) => !empty($b['url']));
    $breadcrumb_back = !empty($prev) ? end($prev)['url'] : 'index.php';
}

// Label halaman aktif (item terakhir) — allow override via $breadcrumb_title
$last_crumb   = end($breadcrumbs);
$active_label = $breadcrumb_title ?? $last_crumb['label'];

// JSON-LD Structured data
$base_url_full = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
$json_items = [];
foreach ($breadcrumbs as $i => $crumb) {
    $json_items[] = [
        '@type'    => 'ListItem',
        'position' => $i + 1,
        'name'     => ($i === array_key_last($breadcrumbs)) ? $active_label : $crumb['label'],
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
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
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
                $label   = $is_last ? $active_label : $crumb['label'];
            ?>
                <li class="breadcrumb-item <?= $is_last ? 'breadcrumb-item--active' : '' ?>">
                    <?php if (!$is_last && !empty($crumb['url'])): ?>
                        <a href="<?= htmlspecialchars($crumb['url']) ?>"
                           class="breadcrumb-link">
                            <?php if ($i === 0): ?>
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                    <polyline points="9 22 9 12 15 12 15 22"/>
                                </svg>
                            <?php endif; ?>
                            <?= htmlspecialchars($label) ?>
                        </a>
                    <?php else: ?>
                        <span class="breadcrumb-current" aria-current="page">
                            <?= htmlspecialchars($label) ?>
                        </span>
                    <?php endif; ?>

                    <?php if (!$is_last): ?>
                        <svg class="breadcrumb-chevron" width="11" height="11" viewBox="0 0 24 24" fill="none"
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