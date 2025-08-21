<?php
/**
 * تحويل أي كمية للوحدة الكبرى
 *
 * @param float  $count   الكمية
 * @param string $unit    الوحدة الحالية
 * @param string $unitL   الوحدة الكبرى
 * @param string $unitM   الوحدة المتوسطة
 * @param string $unitS   الوحدة الصغرى
 * @param float  $fL2M    معامل تحويل من L إلى M
 * @param float  $fM2S    معامل تحويل من M إلى S
 * @return float الكمية بعد التحويل للوحدة الكبرى
 */
function convertToUnitL($count, $unit, $unitL, $unitM, $unitS, $fL2M = 1, $fM2S = 1) {
    if ($unit === $unitL) {
        return $count;
    } elseif ($unit === $unitM) {
        return $count / $fL2M;
    } elseif ($unit === $unitS) {
        return $count / ($fM2S * $fL2M);
    } else {
        throw new Exception("الوحدة غير معروفة: $unit");
    }
}
