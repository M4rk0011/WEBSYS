<?php
// One shared reporting area for PHP validation AND the JavaScript map.
// Coordinate order: [latitude, longitude]. The last point joins the first.
// This is an APPROXIMATE trace of the red outline in the user's screenshot
// image(20261108-034347).png, aligned to nearby OpenStreetMap road points.
// It is a project service area, not an officially surveyed barangay boundary.
// Map reference: https://www.openstreetmap.org/relation/15777029
// Road reference: https://www.openstreetmap.org/way/85417112
// Reference data: © OpenStreetMap contributors, https://www.openstreetmap.org/copyright
// Traced 8 November 2026. The OSM administrative outline differs from the screenshot.
// To correct the area later, edit this array; both browser and server use it.
const BARANGAY_CENTER = [16.4155, 120.5982];
const BARANGAY_BOUNDARY = [
    [16.4169218, 120.5972576],
    [16.4167504, 120.5974896],
    [16.4166459, 120.5977347],
    [16.4165615, 120.5979727],
    [16.4166440, 120.5981949],
    [16.4169609, 120.5984422],
    [16.4168426, 120.5986457],
    [16.4167586, 120.5989186],
    [16.4166455, 120.5989826],
    [16.4165187, 120.5990049],
    [16.4163715, 120.5989996],
    [16.4162654, 120.5990845],
    [16.4161402, 120.5992602],
    [16.4160350, 120.5994287],
    [16.4159418, 120.5994716],
    [16.4157474, 120.5994250],
    [16.4155326, 120.5993437],
    [16.4153313, 120.5992833],
    [16.4151371, 120.5992576],
    [16.4149102, 120.5993020],
    [16.4147104, 120.5993739],
    [16.4145705, 120.5994243],
    [16.4144501, 120.5994257],
    [16.4142892, 120.5993857],
    [16.4141752, 120.5993521],
    [16.4140350, 120.5993746],
    [16.4139019, 120.5994388],
    [16.4138484, 120.5994394],
    [16.4136402, 120.5993512],
    [16.4134188, 120.5992840],
    [16.4130769, 120.5991972],
    [16.4134179, 120.5991933],
    [16.4136786, 120.5991834],
    [16.4138509, 120.5990351],
    [16.4140361, 120.5988378],
    [16.4141216, 120.5986974],
    [16.4142191, 120.5984314],
    [16.4143363, 120.5981233],
    [16.4144267, 120.5978156],
    [16.4145370, 120.5974937],
    [16.4146210, 120.5972139],
    [16.4147123, 120.5969828],
    [16.4149402, 120.5970430],
    [16.4151416, 120.5971174],
    [16.4153497, 120.5971917],
    [16.4155576, 120.5972521],
    [16.4157251, 120.5972781],
    [16.4159523, 120.5972685],
    [16.4162599, 120.5972651],
    [16.4164048, 120.5970543],
    [16.4166185, 120.5970240],
];

// Ray casting: count crossings of a horizontal line from the chosen point.
// An odd number means the point is inside. Points on the red line also count.
function insideBarangay(float $latitude, float $longitude): bool
{
    if (!is_finite($latitude) || !is_finite($longitude)) {
        return false;
    }
    $inside = false;
    $points = BARANGAY_BOUNDARY;
    $count = count($points);
    for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
        [$ay, $ax] = $points[$j];
        [$by, $bx] = $points[$i];
        $dx = $bx - $ax;
        $dy = $by - $ay;
        $length = hypot($dx, $dy);
        // Tiny floating-point tolerance; this does not add a buffer outside the area.
        $onLine = abs(($longitude - $ax) * $dy - ($latitude - $ay) * $dx)
            <= 0.0000000001 * $length;
        if ($onLine && $longitude >= min($ax, $bx) - 0.0000000001
            && $longitude <= max($ax, $bx) + 0.0000000001
            && $latitude >= min($ay, $by) - 0.0000000001
            && $latitude <= max($ay, $by) + 0.0000000001) {
            return true;
        }
        if (($ay > $latitude) !== ($by > $latitude)) {
            $crossing = $ax + ($latitude - $ay) * $dx / $dy;
            if ($longitude < $crossing) {
                $inside = !$inside;
            }
        }
    }
    return $inside;
}

// Old/imported complaints may have missing or out-of-area coordinates.
// Keep their records, but do not place them on the barangay map.
function complaintInBarangay(object $complaint): bool
{
    $lat = $complaint->location->latitude ?? null;
    $lng = $complaint->location->longitude ?? null;
    return is_numeric($lat) && is_numeric($lng)
        && insideBarangay((float) $lat, (float) $lng);
}
