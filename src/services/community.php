<?php
// The official hazard/evacuation map has not been supplied; staff add verified points.
const PLACE_KINDS = [
    'hazard' => 'Hazard area',
    'evacuation' => 'Evacuation center',
    'barangay_hall' => 'Barangay hall',
    'hospital' => 'Hospital or clinic',
    'road' => 'Road / access point',
    'landmark' => 'Community landmark',
];
const HAZARD_TYPES = [
    '' => 'Not specified', 'landslide' => 'Landslide', 'flood' => 'Flood',
    'fire' => 'Fire', 'falling_tree' => 'Falling tree', 'other' => 'Other hazard',
];
function directory(string $type): BarangayDirectory
{
    return new BarangayDirectory(db(), $type);
}
function canManageDirectory(): bool
{
    return isAdmin();
}
function canSeePlace(array $place): bool
{
    return isAdmin() || $place['review_status'] === 'verified';
}
function visiblePlaces(): array
{
    return array_values(array_filter(directory('places')->all(), 'canSeePlace'));
}
function validDateOrBlank(string $value): bool
{
    if ($value === '') return true;
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}
function inputText(string $name, int $max = 200, bool $mustHave = false): string
{
    $v = post($name);
    if (mbLen($v) > $max || ($mustHave && $v === '')) {
        throw new RuntimeException('Please enter a valid ' . str_replace('_', ' ', $name) . '.');
    }
    return $v;
}
function mbLen(string $str): int
{
    return function_exists('mb_strlen') ? mb_strlen($str) : strlen($str);
}
function photoPlaceUrl(string $id): string
{
    return 'place-photo.php?' . http_build_query(['id' => $id]);
}
