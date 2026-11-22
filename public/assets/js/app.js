/* Shared browser behaviors: confirmations, form controls, previews, and map interactions. */
"use strict";
for (const b of document.querySelectorAll("[data-confirm]"))
  b.addEventListener("click", (e) => {
    if (!confirm(b.dataset.confirm)) e.preventDefault();
  });
for (const n of ["latitude", "longitude"]) {
  const input = document.querySelector(`[name="${n}"]`);
  if (input) {
    input.step = "any";
    input.min = n === "latitude" ? -90 : -180;
    input.max = n === "latitude" ? 90 : 180;
  }
}
const files = document.querySelector('[name="photos[]"]');
let previews = [];
if (files)
  files.addEventListener("change", () => {
    const box = document.getElementById("photo-preview");
    previews.forEach(URL.revokeObjectURL);
    if (!box) {
      previews = [];
      return;
    }
    box.replaceChildren();
    previews = [];
    for (const file of files.files) {
      if (!["image/jpeg", "image/png", "image/webp"].includes(file.type)) continue;
      const img = document.createElement("img");
      img.alt = "Selected photo preview";
      img.src = URL.createObjectURL(file);
      previews.push(img.src);
      box.append(img);
    }
  });
const el = document.getElementById("location-map");
if (el) {
  if (typeof L === "undefined") {
    el.textContent = "Map could not load. Enter latitude and longitude manually.";
  } else {
    // PHP supplies one coordinate list for drawing AND server validation.
    const area = JSON.parse(document.getElementById("map-area").textContent);
    const center = area.center;
    const map = L.map(el, { minZoom: 16, maxZoom: 19, maxBoundsViscosity: 0.8, fadeAnimation: false, zoomControl: false });
    L.control.zoom({ position: "bottomleft" }).addTo(map);
    // A bundled, georeferenced road image remains visible when internet tiles fail.
    // This is a saved OpenStreetMap reference (8 Nov 2026), not live imagery.
    L.imageOverlay("assets/img/map-reference.png", [[16.4126, 120.5958], [16.4175, 120.6001]], {
      pane: "tilePane", zIndex: 0, interactive: false,
      attribution: '<a href="https://www.openstreetmap.org/copyright">© OpenStreetMap contributors</a>',
    }).addTo(map);
    const boundary = L.polygon(area.boundary, {
      color: "#dc2626", weight: 3, fillColor: "#ef4444", fillOpacity: 0.06,
      interactive: false,
    }).addTo(map);
    const bounds = boundary.getBounds();
    map.setMaxBounds(bounds.pad(0.5));
    const fitArea = () => {
      map.stop();
      map.closePopup();
      map.fitBounds(bounds, { padding: [24, 24], maxZoom: 17, animate: false });
    };
    fitArea();
    // Dim the area outside the red line without blocking map clicks.
    L.polygon([
      [[85, -180], [85, 180], [-85, 180], [-85, -180]], area.boundary,
    ], { stroke: false, fillColor: "#18392a", fillOpacity: 0.16, interactive: false }).addTo(map);

    // Same ray-casting rule as insideBarangay() in src/services/boundary.php.
    function insideArea(lat, lng) {
      if (!Number.isFinite(lat) || !Number.isFinite(lng)) return false;
      let inside = false;
      const points = area.boundary;
      for (let i = 0, j = points.length - 1; i < points.length; j = i++) {
        const [ay, ax] = points[j], [by, bx] = points[i];
        const dx = bx - ax, dy = by - ay, tolerance = 0.0000000001;
        const onLine = Math.abs((lng - ax) * dy - (lat - ay) * dx)
          <= tolerance * Math.hypot(dx, dy);
        if (onLine && lng >= Math.min(ax, bx) - tolerance
            && lng <= Math.max(ax, bx) + tolerance
            && lat >= Math.min(ay, by) - tolerance && lat <= Math.max(ay, by) + tolerance) return true;
        if ((ay > lat) !== (by > lat) && lng < ax + (lat - ay) * dx / dy) inside = !inside;
      }
      return inside;
    }
    const bar = document.createElement("div");
    bar.className = "map-tools";
    const select = document.createElement("select");
    select.setAttribute("aria-label", "Map imagery");
    for (const [v, t] of [
      ["street", "Online street map"],
      ["satellite", "Satellite"],
      ["offline", "Saved street map (recommended)"],
    ]) {
      const o = document.createElement("option");
      o.value = v;
      o.textContent = t;
      select.append(o);
    }
    // Start with the included street reference: no third-party requests or 403 tiles.
    // Residents can still choose Online street map or Satellite from the selector.
    select.value = "offline";
    const retry = document.createElement("button");
    retry.type = "button";
    retry.textContent = "Retry map";
    const message = document.createElement("span");
    message.setAttribute("role", "status");
    const reset = document.createElement("button");
    reset.type = "button";
    reset.textContent = "Show whole area";
    reset.addEventListener("click", fitArea);
    bar.append(select, retry, reset, message);
    el.before(bar);
    const providers = {
      street: [
        ["https://tile.openstreetmap.org/{z}/{x}/{y}.png", '<a href="https://www.openstreetmap.org/copyright">© OpenStreetMap contributors</a>'],
        [
          "https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}",
          "Tiles © Esri",
        ],
      ],
      satellite: [
        [
          "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}",
          "Tiles © Esri, Maxar, Earthstar Geographics",
        ],
      ],
    };
    let layer = null,
      timer = null,
      generation = 0;
    function load(index = 0) {
      clearTimeout(timer);
      const token = ++generation;
      if (layer) map.removeLayer(layer);
      const list = providers[select.value];
      if (!list || !list[index]) {
        layer = null;
        message.textContent =
          "Saved street map (8 Nov 2026). Pins and boundary work offline. For live tiles, choose Online street map or Satellite.";
        return;
      }
      message.textContent = "Loading online map… (If access is blocked, choose Saved street map.)";
      let failed = false;
      layer = L.tileLayer(list[index][0], {
        attribution: list[index][1],
        maxZoom: 19,
        maxNativeZoom: 18,
        referrerPolicy: "strict-origin-when-cross-origin",
        zIndex: 1,
      }).addTo(map);
      let queued = false;
      const fallback = () => {
        if (queued || token !== generation) return;
        queued = true;
        setTimeout(() => {
          if (token === generation) load(index + 1);
        }, 0);
      };
      layer.on("loading", () => {
        if (token !== generation) return;
        failed = false;
        clearTimeout(timer);
        timer = setTimeout(fallback, 10000);
      });
      layer.on("tileerror", () => {
        if (token === generation) failed = true;
      });
      layer.on("load", () => {
        if (token !== generation) return;
        clearTimeout(timer);
        if (failed) {
          fallback();
          return;
        }
        message.textContent = select.value === "satellite" ? "Satellite imagery" : "Street map";
      });
      timer = setTimeout(fallback, 10000);
    }
    select.addEventListener("change", () => load());
    retry.addEventListener("click", () => load());
    load();
    if (el.dataset.edit) {
      const lat = document.querySelector('[name="latitude"]');
      const lng = document.querySelector('[name="longitude"]');
      const hint = document.createElement("p");
      hint.className = "map-location-feedback";
      hint.setAttribute("role", "status");
      el.after(hint);
      let pin = null, lastValid = null;
      const outsideMessage = "Choose a location inside the red Lower General Luna reporting area.";
      function feedback(text, error = false) {
        hint.textContent = text;
        hint.classList.toggle("is-error", error);
      }
      function placePin(point) {
        lastValid = point;
        if (!pin) {
          pin = L.marker(point, { draggable: true, title: "Complaint location" }).addTo(map);
          pin.on("dragend", () => {
            if (!chooseLocation(pin.getLatLng())) pin.setLatLng(lastValid);
          });
        } else pin.setLatLng(point);
      }
      function validateFields() {
        const valid = lat.value.trim() !== "" && lng.value.trim() !== ""
          && insideArea(Number(lat.value), Number(lng.value));
        lat.setCustomValidity(valid ? "" : outsideMessage);
        lng.setCustomValidity(valid ? "" : outsideMessage);
        return valid;
      }
      function chooseLocation(point) {
        // Invalid clicks/drags leave the last accepted location unchanged.
        if (!insideArea(point.lat, point.lng)) {
          feedback(outsideMessage + " The selected pin has not moved.", true);
          return false;
        }
        lat.value = point.lat.toFixed(7);
        lng.value = point.lng.toFixed(7);
        // Validate the rounded coordinates that will actually be submitted.
        if (!validateFields()) {
          feedback(outsideMessage, true);
          return false;
        }
        placePin(L.latLng(Number(lat.value), Number(lng.value)));
        feedback("Location selected inside Lower General Luna.");
        return true;
      }
      map.on("click", event => chooseLocation(event.latlng));
      for (const input of [lat, lng]) input.addEventListener("input", () => {
        if (validateFields()) {
          placePin(L.latLng(Number(lat.value), Number(lng.value)));
          feedback("Location selected inside Lower General Luna.");
        } else {
          if (pin) { map.removeLayer(pin); pin = null; }
          feedback(outsideMessage, true);
        }
      });
      lat.form.addEventListener("submit", event => {
        if (!validateFields()) {
          event.preventDefault();
          feedback(outsideMessage, true);
          lat.reportValidity();
        }
      });
      if (validateFields()) {
        placePin(L.latLng(Number(lat.value), Number(lng.value)));
        feedback("Location selected inside Lower General Luna.");
      } else feedback(lat.value || lng.value ? outsideMessage : "Click inside the red line to place your pin.", Boolean(lat.value || lng.value));
    } else {
      const records = JSON.parse(document.getElementById("map-reports").textContent);
      const placeRecords = JSON.parse(document.getElementById("map-places-data").textContent);
      const complaintLayer = L.layerGroup().addTo(map);
      const placeLayer = L.layerGroup().addTo(map);
      const groups = new Map(), markers = new Map();
      // Complaints at identical coordinates share a counted pin, so none is hidden.
      for (const report of records) {
        if (!insideArea(Number(report.lat), Number(report.lng))) continue;
        const key = Number(report.lat).toFixed(7) + "," + Number(report.lng).toFixed(7);
        if (!groups.has(key)) groups.set(key, []);
        groups.get(key).push(report);
      }
      function popupCard(report) {
        // User text uses textContent, not innerHTML, to prevent inserted scripts.
        const article = document.createElement("article");
        article.className = "map-popup-report";
        const title = document.createElement("a");
        title.href = "details.php?id=" + encodeURIComponent(report.id);
        title.textContent = report.reference + " · " + report.title;
        article.append(title);
        const info = document.createElement("p");
        info.textContent = report.status + " · " + report.category;
        article.append(info);
        const address = document.createElement("p");
        address.textContent = report.address;
        article.append(address);
        const resident = document.createElement("p");
        resident.textContent = "Reported by: " + report.resident;
        article.append(resident);
        if (report.photos.length) {
          const photos = document.createElement("div");
          photos.className = "map-popup-photos";
          for (const [index, source] of report.photos.entries()) {
            const link = document.createElement("a");
            link.href = source; link.target = "_blank"; link.rel = "noopener";
            const photo = document.createElement("img");
            photo.src = source;
            photo.alt = "Evidence " + (index + 1) + " for " + report.reference;
            photo.loading = "lazy";
            link.append(photo); photos.append(link);
          }
          article.append(photos);
        } else {
          const empty = document.createElement("p");
          empty.textContent = "No photo attached.";
          article.append(empty);
        }
        return article;
      }
      for (const group of groups.values()) {
        const first = group[0];
        const popup = document.createElement("div");
        popup.className = "map-popup-reports";
        if (group.length > 1) {
          const heading = document.createElement("strong");
          heading.textContent = group.length + " complaints at this location";
          popup.append(heading);
        }
        for (const report of group) popup.append(popupCard(report));
        const icon = L.divIcon({
          className: "complaint-pin", html: String(group.length),
          iconSize: [32, 32], iconAnchor: [16, 16], popupAnchor: [0, -18],
        });
        const marker = L.marker([first.lat, first.lng], {
          icon, title: group.length + " complaint(s) at this location", alt: "Complaint location",
        }).addTo(complaintLayer).bindPopup(popup, {
          // Leave room for Leaflet's margins, close button and the map edges on phones.
          maxWidth: Math.min(300, el.clientWidth - 70), minWidth: 160,
          maxHeight: Math.min(320, el.clientHeight - 110), autoPanPadding: [12, 16],
        });
        for (const report of group) markers.set(report.id, marker);
      }
      // Community locations are entered by administrators; no fake hazard coordinates.
      const placeMarkers = new Map();
      for (const place of placeRecords) {
        if (!insideArea(Number(place.lat), Number(place.lng))) continue;
        const popup = document.createElement('article');
        popup.className = 'map-popup-report';
        const heading = document.createElement('strong');
        heading.textContent = place.name;
        popup.append(heading);
        const kind = document.createElement('p');
        kind.textContent = place.kindLabel + (place.hazardLabel ? ' · ' + place.hazardLabel : '') +
          (place.review === 'draft' ? ' · Draft (unverified)' : ' · Staff verified');
        popup.append(kind);
        if (place.description) {
          const details = document.createElement('p');
          details.textContent = place.description;
          popup.append(details);
        }
        if (place.photo) {
          const img = document.createElement('img');
          img.src = place.photo;
          img.alt = 'Photo of ' + place.name;
          img.loading = 'lazy';
          img.className = 'place-popup-photo';
          popup.append(img);
        }
        const category = ({hazard:'!', evacuation:'E', hospital:'+', barangay_hall:'B', road:'R', landmark:'L'})[place.kind] || 'P';
        const icon = L.divIcon({ className:'place-pin place-pin-' + place.kind,
          html: category, iconSize:[33,33], iconAnchor:[16,16], popupAnchor:[0,-15] });
        const marker = L.marker([place.lat, place.lng], {icon, title:place.name, alt:place.kindLabel})
          .addTo(placeLayer).bindPopup(popup, {maxWidth:Math.min(300, el.clientWidth-70)});
        placeMarkers.set(place.id, marker);
      }
      const complaintToggle = document.getElementById('layer-complaints');
      const placeToggle = document.getElementById('layer-places');
      if (complaintToggle) complaintToggle.addEventListener('change', () => {
        if (complaintToggle.checked) map.addLayer(complaintLayer); else map.removeLayer(complaintLayer);
      });
      if (placeToggle) placeToggle.addEventListener('change', () => {
        if (placeToggle.checked) map.addLayer(placeLayer); else map.removeLayer(placeLayer);
      });
      for (const button of document.querySelectorAll('.show-place-pin')) {
        const marker = placeMarkers.get(button.dataset.place);
        if (!marker) continue;
        button.hidden = false;
        button.addEventListener('click', () => {
          if (placeToggle && !placeToggle.checked) { placeToggle.checked = true; map.addLayer(placeLayer); }
          el.scrollIntoView({ behavior: 'smooth', block:'center' });
          map.setView(marker.getLatLng(), 18, { animate:false });
          marker.openPopup();
        });
      }
      for (const button of document.querySelectorAll(".show-map-pin")) {
        const marker = markers.get(button.dataset.report);
        if (!marker) continue;
        button.hidden = false;
        button.addEventListener("click", () => {
          if (complaintToggle && !complaintToggle.checked) { complaintToggle.checked = true; map.addLayer(complaintLayer); }
          el.scrollIntoView({ behavior: "smooth", block: "center" });
          map.setView(marker.getLatLng(), 18, { animate: false });
          marker.openPopup();
        });
      }
    }
  }
}

// Show the server-rendered announcement as a modal. The X submits a normal PHP form.
// With JavaScript disabled, the open dialog still displays and its X still works.
const announcementPopup = document.getElementById('announcement-popup');
if (announcementPopup && typeof announcementPopup.showModal === 'function') {
  announcementPopup.close();
  announcementPopup.showModal();
  announcementPopup.addEventListener('cancel', function (event) {
    // Escape has the same effect as X and remembers dismissal for this account.
    event.preventDefault();
    announcementPopup.querySelector('form').requestSubmit();
  });
}
