<?php // Send the SAME PHP boundary to the browser; no duplicated coordinate list. ?>
<p class="map-area-note"><span class="boundary-key" aria-hidden="true"></span>
Lower General Luna reporting area · Approximate outline based on the supplied map.</p>
<script type="application/json" id="map-area"><?= json_encode([ 'center' => BARANGAY_CENTER, 'boundary' => BARANGAY_BOUNDARY, ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
