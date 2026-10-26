<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];

// ---------- DUKAN (RESTAURANT) NU REAL LOCATION ----------
// Google Maps ma dukan par right-click karo -> pehla be number (lat, lng) copy karo
$RESTAURANT = [
    'name' => "B K's Burger House",
    'lat'  => 19.0760,     // <-- aa badlo
    'lng'  => 72.8777,     // <-- aa badlo
];

// Order ni taiyari ma lagto time (minute). Pachhi rider nikle em manine map ma rider chale chhe
$PREP_MINUTES = 15;

$COL = [
    'id'     => 'id',
    'user'   => 'username',
    'date'   => 'order_time',
    'total'  => 'total_price',
    'status' => 'status',
    'items'  => 'items',
    // Map mate (optional) columns. Na hoy to map sirf address parthi chale
    'addr'   => 'address',        // customer nu delivery address
    'dlat'   => 'delivery_lat',   // customer location latitude  (optional)
    'dlng'   => 'delivery_lng',   // customer location longitude (optional)
    'rlat'   => 'rider_lat',      // rider no live latitude      (optional)
    'rlng'   => 'rider_lng',      // rider no live longitude     (optional)
];

$stmt = $conn->prepare("SELECT * FROM orders WHERE {$COL['user']} = ? ORDER BY {$COL['id']} DESC");
$stmt->bind_param("s", $username);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$steps = ['Pending', 'Preparing', 'Out for delivery', 'Delivered'];

function stepIndex($s) {
    $s = strtolower(trim((string) $s));
    if ($s === 'delivered') return 3;
    if (strpos($s, 'out') !== false) return 2;
    if ($s === 'preparing') return 1;
    return 0;
}

function itemLines($text) {
    $lines = preg_split('/\r\n|\r|\n/', (string) $text);
    return array_values(array_filter(array_map('trim', $lines), 'strlen'));
}

function itemEmoji($line) {
    $l = strtolower($line);
    foreach ([
        ['fries', '🍟'], ['wedge', '🍟'],
        ['coke', '🥤'], ['sprite', '🥤'], ['fanta', '🥤'], ['coffee', '☕'], ['water', '💧'], ['drink', '🥤'],
        ['combo', '🍱'], ['chicken', '🍗'], ['burger', '🍔'],
    ] as $p) {
        if (strpos($l, $p[0]) !== false) return $p[1];
    }
    return '🍽️';
}

function h($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

$total_orders = count($orders);
$total_spent  = 0;
$delivered    = 0;
foreach ($orders as $o) {
    $total_spent += (float) ($o[$COL['total']] ?? 0);
    if (stepIndex($o[$COL['status']] ?? '') === 3) {
        $delivered++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your Orders</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
  :root {
    --bg: #0f0d0c;
    --card: #1a1613;
    --card2: #241d18;
    --ink: #fff7ec;
    --muted: #a39384;
    --red: #e63946;
    --red2: #9d0208;
    --gold: #ffb703;
    --line: #2f2620;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
    color: var(--ink);
    min-height: 100vh;
    background:
      radial-gradient(circle at 8% -5%, rgba(230,57,70,.28), transparent 38%),
      radial-gradient(circle at 100% 100%, rgba(255,183,3,.14), transparent 40%),
      var(--bg);
    background-attachment: fixed;
  }
  .page { max-width: 1040px; margin: 0 auto; padding: 22px 20px 90px; }

  /* ---------- TOP BAR + HERO ---------- */
  .bar { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
  .brand { font-family: 'Bebas Neue', sans-serif; font-size: 28px; letter-spacing: 1.5px; }
  .brand span { color: var(--gold); }
  .pill-link {
    color: var(--gold); text-decoration: none; font-weight: 700; font-size: 14px;
    border: 2px solid var(--gold); padding: 9px 20px; border-radius: 999px; transition: .2s;
  }
  .pill-link:hover { background: var(--gold); color: #000; }

  .hero { margin: 38px 0 28px; }
  .hi { color: var(--gold); font-weight: 800; font-size: 13px; letter-spacing: 3px; text-transform: uppercase; }
  .hero h1 {
    font-family: 'Bebas Neue', sans-serif; font-weight: 400;
    font-size: clamp(54px, 11vw, 104px); line-height: .9; letter-spacing: 2px; margin-top: 6px;
  }
  .hero h1 span { color: transparent; -webkit-text-stroke: 2px var(--red); }

  /* ---------- STATS ---------- */
  .metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px; margin-bottom: 34px; }
  .metric {
    display: flex; align-items: center; gap: 16px;
    background: var(--card); border: 1px solid var(--line); border-radius: 20px; padding: 16px 20px;
  }
  .metric .ico {
    width: 54px; height: 54px; border-radius: 50%; background: var(--card2);
    display: flex; align-items: center; justify-content: center; font-size: 24px;
    box-shadow: inset 0 0 0 2px rgba(255,183,3,.35);
  }
  .metric span { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700; }
  .metric strong { display: block; font-family: 'Bebas Neue', sans-serif; font-weight: 400; font-size: 40px; letter-spacing: 1px; line-height: 1.05; }

  /* ---------- ORDER CARD (ticket) ---------- */
  .list { display: grid; gap: 24px; }
  .card {
    display: grid; grid-template-columns: 240px 1fr;
    background: var(--card); border: 1px solid var(--line); border-radius: 24px; overflow: hidden;
    box-shadow: 0 14px 40px rgba(0,0,0,.45);
  }
  .stub {
    position: relative; display: flex; flex-direction: column; gap: 4px;
    padding: 24px 22px;
    background: linear-gradient(160deg, var(--red), var(--red2));
  }
  .stub::after {
    content: ""; position: absolute; top: 0; bottom: 0; right: -1px; width: 14px;
    background: radial-gradient(circle at 14px 10px, var(--card) 7px, transparent 7.5px) 0 0 / 14px 20px repeat-y;
  }
  .label { font-size: 11px; text-transform: uppercase; letter-spacing: 2px; font-weight: 700; color: rgba(255,255,255,.75); }
  .oid { font-family: 'Bebas Neue', sans-serif; font-size: 58px; line-height: 1; letter-spacing: 1px; }
  .date { font-size: 12px; color: rgba(255,255,255,.8); }
  .badge {
    align-self: flex-start; margin-top: 10px;
    font-size: 12px; font-weight: 800; color: var(--red2); background: #fff;
    padding: 6px 14px; border-radius: 999px;
  }
  .s3 .badge { color: #14532d; background: #bbf7d0; }
  .stub-total { margin-top: auto; padding-top: 22px; }
  .stub-total strong { display: block; font-family: 'Bebas Neue', sans-serif; font-weight: 400; font-size: 40px; letter-spacing: 1px; }

  .body { padding: 24px 28px 26px; min-width: 0; }
  .items { list-style: none; display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 8px; }
  .items li {
    display: flex; align-items: center; gap: 10px;
    background: var(--card2); border-radius: 12px; padding: 9px 12px;
    font-size: 13px; font-weight: 600; line-height: 1.3;
  }
  .items li i { font-style: normal; font-size: 20px; }

  /* ---------- ROAD TRACKER ---------- */
  .road { position: relative; margin-top: 38px; }
  .stops { position: relative; display: grid; grid-template-columns: repeat(4, 1fr); }
  .stops::before, .stops::after {
    content: ""; position: absolute; top: 19px; left: 12.5%; height: 5px; border-radius: 5px;
  }
  .stops::before {
    width: 75%;
    background: repeating-linear-gradient(90deg, #4a3d34 0 12px, transparent 12px 20px);
  }
  .stops::after {
    width: calc(75% * var(--p)); background: var(--gold); box-shadow: 0 0 12px rgba(255,183,3,.6);
    transition: width .5s;
  }
  .rider-ico {
    position: absolute; top: -34px; left: calc(12.5% + 75% * var(--p)); z-index: 3;
    font-size: 28px; transform: translateX(-50%) scaleX(-1);
    animation: bob 1.2s ease-in-out infinite; filter: drop-shadow(0 4px 4px rgba(0,0,0,.5));
  }
  @keyframes bob {
    0%, 100% { transform: translateX(-50%) scaleX(-1) translateY(0); }
    50%      { transform: translateX(-50%) scaleX(-1) translateY(-4px); }
  }
  .stop { position: relative; z-index: 1; text-align: center; }
  .sico {
    width: 44px; height: 44px; margin: 0 auto 8px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; font-size: 20px;
    background: var(--card2); border: 3px solid #3a2f28; filter: grayscale(1) opacity(.55);
  }
  .stop.done .sico { background: #3a2a0e; border-color: var(--gold); filter: none; }
  .stop.now .sico { animation: glow 1.8s infinite; }
  @keyframes glow {
    0%   { box-shadow: 0 0 0 0 rgba(255,183,3,.55); }
    70%  { box-shadow: 0 0 0 12px rgba(255,183,3,0); }
    100% { box-shadow: 0 0 0 0 rgba(255,183,3,0); }
  }
  .sname { font-size: 12px; color: var(--muted); font-weight: 600; }
  .stop.done .sname { color: var(--ink); font-weight: 800; }

  /* ---------- MAP TRACKING ---------- */
  .trackbar { margin-top: 24px; }
  .maptoggle {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--gold); color: #000; border: none; cursor: pointer;
    font-family: inherit; font-weight: 800; font-size: 14px;
    padding: 12px 22px; border-radius: 999px; transition: .2s;
    box-shadow: 0 4px 0 #b37f00;
  }
  .maptoggle:hover { background: #fff; }
  .maptoggle:active { transform: translateY(3px); box-shadow: 0 1px 0 #b37f00; }
  .mapwrap { margin-top: 16px; border-radius: 16px; overflow: hidden; border: 1px solid var(--line); }
  .mapwrap[hidden] { display: none; }
  .mapinfo {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    background: #2a1d17; color: #ffd27a; font-size: 14px; font-weight: 700; padding: 12px 16px;
  }
  .mapinfo .live { background: #16a34a; color: #fff; font-size: 11px; padding: 3px 9px; border-radius: 999px; }
  .mapbox { height: 340px; z-index: 0; }
  .pin {
    width: 36px; height: 36px; border-radius: 50%; background: #fff;
    border: 3px solid var(--red); display: flex; align-items: center; justify-content: center;
    font-size: 18px; box-shadow: 0 3px 8px rgba(0,0,0,.35);
  }
  .pin.rider { border-color: #16a34a; animation: pulse 1.8s infinite; }
  @keyframes pulse {
    0%   { box-shadow: 0 0 0 0 rgba(22,163,74,.55); }
    70%  { box-shadow: 0 0 0 14px rgba(22,163,74,0); }
    100% { box-shadow: 0 0 0 0 rgba(22,163,74,0); }
  }

  /* ---------- EMPTY ---------- */
  .empty {
    text-align: center; background: var(--card); border: 2px dashed #3a2f28;
    border-radius: 24px; padding: 64px 20px;
  }
  .empty .big { font-size: 64px; }
  .empty h2 { font-family: 'Bebas Neue', sans-serif; font-weight: 400; font-size: 44px; letter-spacing: 1.5px; margin-top: 6px; }
  .empty p { color: var(--muted); margin: 6px 0 24px; }
  .cta {
    display: inline-block; background: var(--red); color: #fff; text-decoration: none;
    font-weight: 800; padding: 13px 28px; border-radius: 999px; box-shadow: 0 4px 0 var(--red2);
  }

  @media (max-width: 760px) {
    .card { grid-template-columns: 1fr; }
    .stub { display: grid; grid-template-columns: 1fr auto; align-items: end; padding: 18px 20px; }
    .stub::after { display: none; }
    .stub .label, .stub .date, .stub .badge { grid-column: 1; }
    .stub-total { grid-column: 2; grid-row: 1 / span 4; margin: 0; padding: 0; text-align: right; }
    .oid { font-size: 46px; }
    .body { padding: 20px 18px 22px; }
    .items { grid-template-columns: 1fr; }
    .sname { font-size: 10px; }
    .sico { width: 38px; height: 38px; font-size: 17px; }
    .stops::before, .stops::after { top: 16px; }
    .mapbox { height: 300px; }
  }
</style>
</head>
<body>
<main class="page">
  <div class="bar">
    <div class="brand">🍔 B K’S <span>BURGER HOUSE</span></div>
    <a class="pill-link" href="menu.php">← Back to menu</a>
  </div>

  <section class="hero">
    <p class="hi">Hi, <?= h($username) ?> 👋</p>
    <h1>Your <span>orders</span></h1>
  </section>

  <?php if ($total_orders === 0): ?>
    <section class="empty">
      <div class="big">🛵</div>
      <h2>No orders yet</h2>
      <p>Once you place an order, you can track it here on the map.</p>
      <a class="cta" href="menu.php">Browse the menu</a>
    </section>
  <?php else: ?>
    <section class="metrics">
      <div class="metric"><div class="ico">🧾</div><div><span>Total orders</span><strong><?= $total_orders ?></strong></div></div>
      <div class="metric"><div class="ico">✅</div><div><span>Delivered</span><strong><?= $delivered ?></strong></div></div>
      <div class="metric"><div class="ico">💰</div><div><span>Total spent</span><strong>₹<?= number_format($total_spent, 2) ?></strong></div></div>
    </section>

    <section class="list">
      <?php foreach ($orders as $o):
          $current = stepIndex($o[$COL['status']] ?? '');
          $lines   = itemLines($o[$COL['items']] ?? '');
          $total   = (float) ($o[$COL['total']] ?? 0);
          $date    = !empty($o[$COL['date']]) ? date('d M Y, h:i A', strtotime($o[$COL['date']])) : '—';
          $ts      = !empty($o[$COL['date']]) ? (int) strtotime($o[$COL['date']]) : 0;
          $icons   = ['🧾', '🍳', '📦', '🏠'];
      ?>
        <article class="card s<?= (int) $current ?>">
          <div class="stub">
            <span class="label">Order</span>
            <span class="oid">#<?= h($o[$COL['id']]) ?></span>
            <span class="date"><?= h($date) ?></span>
            <span class="badge"><?= h($steps[$current]) ?></span>
            <div class="stub-total">
              <span class="label">Total</span>
              <strong>₹<?= number_format($total, 2) ?></strong>
            </div>
          </div>

          <div class="body">
            <ul class="items">
              <?php foreach ($lines as $line): ?>
                <li><i><?= itemEmoji($line) ?></i><span><?= h($line) ?></span></li>
              <?php endforeach; ?>
            </ul>

            <div class="road">
              <div class="stops" style="--p: <?= $current / 3 ?>">
                <?php if ($current < 3): ?><span class="rider-ico">🛵</span><?php endif; ?>
                <?php foreach ($steps as $i => $name): ?>
                  <div class="stop<?= $i <= $current ? ' done' : '' ?><?= $i === $current ? ' now' : '' ?>">
                    <div class="sico"><?= $icons[$i] ?></div>
                    <div class="sname"><?= h($name) ?></div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="trackbar">
              <button type="button" class="maptoggle" onclick="toggleMap(this)">
                📍 <?= $current === 3 ? 'View delivery route' : 'Track on map' ?>
              </button>
            </div>

            <div class="mapwrap" hidden
                 data-step="<?= (int) $current ?>"
                 data-ts="<?= (int) $ts ?>"
                 data-addr="<?= h($o[$COL['addr']] ?? '') ?>"
                 data-dlat="<?= h($o[$COL['dlat']] ?? '') ?>"
                 data-dlng="<?= h($o[$COL['dlng']] ?? '') ?>"
                 data-rlat="<?= h($o[$COL['rlat']] ?? '') ?>"
                 data-rlng="<?= h($o[$COL['rlng']] ?? '') ?>">
              <div class="mapinfo">Map load thai rahyo chhe...</div>
              <div class="mapbox"></div>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const SHOP = {
    name: <?= json_encode($RESTAURANT['name']) ?>,
    pos: [<?= (float) $RESTAURANT['lat'] ?>, <?= (float) $RESTAURANT['lng'] ?>]
};
const PREP_MIN = <?= (int) $PREP_MINUTES ?>;
const maps = new Map();

/* ---------- helpers ---------- */
function distKm(a, b) {
    const R = 6371, rad = Math.PI / 180;
    const dLat = (b[0] - a[0]) * rad, dLng = (b[1] - a[1]) * rad;
    const x = Math.sin(dLat / 2) ** 2 +
              Math.cos(a[0] * rad) * Math.cos(b[0] * rad) * Math.sin(dLng / 2) ** 2;
    return 2 * R * Math.asin(Math.sqrt(x));
}

// Route par frac (0..1) jetli jagya e point + te sudhi no raasto
function splitAt(path, frac) {
    if (frac <= 0) return { point: path[0], head: [path[0]] };
    if (frac >= 1) return { point: path[path.length - 1], head: path.slice() };
    const seg = [];
    let total = 0;
    for (let i = 1; i < path.length; i++) {
        const d = distKm(path[i - 1], path[i]);
        seg.push(d);
        total += d;
    }
    const target = total * frac;
    let acc = 0;
    for (let i = 0; i < seg.length; i++) {
        if (acc + seg[i] >= target) {
            const t = seg[i] ? (target - acc) / seg[i] : 0;
            const a = path[i], b = path[i + 1];
            const p = [a[0] + (b[0] - a[0]) * t, a[1] + (b[1] - a[1]) * t];
            return { point: p, head: path.slice(0, i + 1).concat([p]) };
        }
        acc += seg[i];
    }
    return { point: path[path.length - 1], head: path.slice() };
}

async function geocode(q) {
    try {
        const r = await fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(q));
        const j = await r.json();
        if (j.length) return [parseFloat(j[0].lat), parseFloat(j[0].lon)];
    } catch (e) {}
    return null;
}

// Real road route (OpenStreetMap + OSRM)
async function getRoute(a, b) {
    try {
        const url = 'https://router.project-osrm.org/route/v1/driving/' +
                    a[1] + ',' + a[0] + ';' + b[1] + ',' + b[0] + '?overview=full&geometries=geojson';
        const r = await fetch(url);
        const j = await r.json();
        if (j.routes && j.routes[0]) {
            const rt = j.routes[0];
            return {
                path: rt.geometry.coordinates.map(c => [c[1], c[0]]),
                km: rt.distance / 1000,
                min: rt.duration / 60
            };
        }
    } catch (e) {}
    const km = distKm(a, b);
    return { path: [a, b], km: km, min: km * 3 };   // fallback: seedhi line
}

function icon(emoji, cls) {
    return L.divIcon({
        html: '<div class="pin ' + (cls || '') + '">' + emoji + '</div>',
        className: '', iconSize: [36, 36], iconAnchor: [18, 18]
    });
}

/* ---------- map ---------- */
function toggleMap(btn) {
    const wrap = btn.closest('.card').querySelector('.mapwrap');
    wrap.hidden = !wrap.hidden;
    if (wrap.hidden) return;
    if (maps.has(wrap)) {
        maps.get(wrap).map.invalidateSize();
    } else {
        initMap(wrap);
    }
}

async function initMap(wrap) {
    const ds = wrap.dataset;
    const info = wrap.querySelector('.mapinfo');
    const box = wrap.querySelector('.mapbox');

    const map = L.map(box).setView(SHOP.pos, 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    L.marker(SHOP.pos, { icon: icon('🍔') }).addTo(map).bindPopup(SHOP.name);

    // Customer nu location: pehla lat/lng, nahi to address parthi shodho
    let dest = null;
    if (ds.dlat && ds.dlng) {
        dest = [parseFloat(ds.dlat), parseFloat(ds.dlng)];
    } else if (ds.addr) {
        dest = await geocode(ds.addr);
    }

    if (!dest) {
        info.textContent = 'Delivery address na maliyu, etle faqt dukan batavi chhe.';
        maps.set(wrap, { map: map });
        return;
    }

    L.marker(dest, { icon: icon('🏠') }).addTo(map).bindPopup('Delivery location');

    const route = await getRoute(SHOP.pos, dest);
    L.polyline(route.path, { color: '#fca5a5', weight: 6, opacity: 0.9 }).addTo(map);
    map.fitBounds(L.polyline(route.path).getBounds(), { padding: [40, 40] });

    const state = {
        map: map, route: route, dest: dest, info: info,
        rider: L.marker(SHOP.pos, { icon: icon('🛵', 'rider'), zIndexOffset: 1000 }).addTo(map),
        done: L.polyline([], { color: '#b91c1c', weight: 6 }).addTo(map)
    };
    maps.set(wrap, state);
    updateRider(wrap);
}

function updateRider(wrap) {
    const s = maps.get(wrap);
    if (!s || !s.route) return;
    const ds = wrap.dataset;
    const step = parseInt(ds.step, 10);
    const ts = parseInt(ds.ts, 10);
    const r = s.route;

    let pos, head = [], frac = 0, live = false, text;

    if (ds.rlat && ds.rlng && step < 3) {
        // Rider no real live GPS database ma hoy to te
        pos = [parseFloat(ds.rlat), parseFloat(ds.rlng)];
        live = true;
        const left = distKm(pos, s.dest);
        text = 'Rider tamari pase aavi rahyo chhe • lagbhag ' + left.toFixed(1) + ' km dur';
    } else if (step <= 1) {
        pos = SHOP.pos;
        text = step === 0
            ? 'Order mali gayo, restaurant confirm kare chhe'
            : 'Tamaro order dukan par taiyar thai rahyo chhe 🍳';
    } else if (step === 3) {
        pos = s.dest;
        frac = 1;
        head = r.path;
        text = 'Order delivery thai gayo ✅ • ' + r.km.toFixed(1) + ' km no raasto';
    } else {
        const elapsed = ts ? (Date.now() / 1000 - ts) / 60 : PREP_MIN;
        frac = Math.min(0.95, Math.max(0.05, (elapsed - PREP_MIN) / Math.max(r.min, 1)));
        const sp = splitAt(r.path, frac);
        pos = sp.point;
        head = sp.head;
        const leftKm = r.km * (1 - frac);
        const leftMin = Math.max(1, Math.round(r.min * (1 - frac)));
        text = 'Rider raste chhe 🛵 • ' + leftKm.toFixed(1) + ' km baki • lagbhag ' + leftMin + ' min ma pahonchse';
    }

    s.rider.setLatLng(pos);
    s.rider.setOpacity(step === 3 ? 0 : 1);
    s.done.setLatLngs(head);

    s.info.textContent = '';
    if (live) {
        const tag = document.createElement('span');
        tag.className = 'live';
        tag.textContent = 'LIVE';
        s.info.appendChild(tag);
    }
    s.info.appendChild(document.createTextNode(text));
}

// Out for delivery order ma rider ni jagya darek 30 second e aagal vadhe
setInterval(function () {
    maps.forEach(function (s, wrap) {
        if (s.route && parseInt(wrap.dataset.step, 10) === 2) updateRider(wrap);
    });
}, 30000);
</script>
</body>
</html>