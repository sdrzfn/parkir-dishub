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
?>