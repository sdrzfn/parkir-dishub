<?php
function getPerformanceStatus($realisasi, $target) {
    if ($target <= 0) {
        return [
            'persen' => 0,
            'warna'  => '#94a3b8',
            'bg_css' => 'bg-slate-500',
            'text'   => 'Tidak ada target',
            'status' => 'neutral'
        ];
    }

    $persen = ($realisasi / $target) * 100;

    if ($persen < 50) {
        return [
            'persen' => round($persen, 1),
            'warna'  => '#ef4444',
            'bg_css' => 'bg-red-500',
            'text'   => 'Kritis / Tunggakan',
            'status' => 'danger'
        ];
    } elseif ($persen < 80) {
        return [
            'persen' => round($persen, 1),
            'warna'  => '#f59e0b',
            'bg_css' => 'bg-amber-500',
            'text'   => 'Hampir Tercapai',
            'status' => 'warning'
        ];
    } else {
        return [
            'persen' => round($persen, 1),
            'warna'  => '#10b981',
            'bg_css' => 'bg-green-500',
            'text'   => 'Target Tercapai',
            'status' => 'success'
        ];
    }
}

/**
 * Redirect kembali ke halaman asal berdasarkan role.
 * 
 * @param string $page     Nama file tujuan
 * @param array  $params   Query string tambahan
 */
function redirectBack(string $page, array $params = []): void
{
    $role_folders = [
        'super-admin'   => 'super-admin',
        'kepala-dinas'  => 'kepala-dinas',
        'bendahara'     => 'bendahara',
    ];

    $role   = $_SESSION['role'] ?? 'admin';
    $folder = $role_folders[$role] ?? null;

    if ($folder) {
        $base = "../{$folder}/";
    } else {
        $base = "../";
    }

    $query = !empty($params) ? '?' . http_build_query($params) : '';

    header("Location: {$base}{$page}{$query}");
    exit;
}
?>