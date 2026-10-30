<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['username'])) {
    header("location: login.php");
    exit();
}
if (($_SESSION['admin'] ?? 'NO') !== 'YES') {
    header("location: index.php");
    exit();
}

$allowedCategories = ["Veg Burger", "Non-Veg Burger", "Combo", "Drink"];

// Category -> image folder (Assets/Store/<folder>/)
$categoryFolders = [
    "Veg Burger"     => "vegburger",
    "Non-Veg Burger" => "non-vegburger",
    "Combo"          => "combo",
    "Drink"          => "drink",
];

$premiumDir        = "Assets/Store/premium";
$allowedImageTypes = [
    "image/png"  => "png",
    "image/jpeg" => "jpg",
    "image/webp" => "webp",
];
$imageExtensions = ["png", "jpg", "jpeg", "webp"];
$maxImageSize    = 2 * 1024 * 1024; // 2 MB

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function flash($type, $text) {
    $_SESSION['flash'] = ['type' => $type, 'text' => $text];
}

// Relative folder for a category, e.g. Assets/Store/vegburger
function categoryDir($category, $folders) {
    return "Assets/Store/" . ($folders[$category] ?? "drink");
}

// Relative path of an existing item image (premium first, then category folder), or null
function findImage($name, $category, $folders, $premiumDir, $extensions) {
    foreach ([$premiumDir, categoryDir($category, $folders)] as $dir) {
        foreach ($extensions as $ext) {
            $rel = $dir . "/" . $name . "." . $ext;
            if (is_file(__DIR__ . "/" . $rel)) {
                return $rel;
            }
        }
    }
    return null;
}

// Encode a relative path so it works in an <img src>
function urlPath($rel) {
    return implode("/", array_map('rawurlencode', explode("/", $rel)));
}

// Create a folder (relative to this file) if it doesn't exist
function ensureDir($rel) {
    $abs = __DIR__ . "/" . $rel;
    return is_dir($abs) || mkdir($abs, 0775, true);
}

function deleteFile($rel) {
    $abs = __DIR__ . "/" . $rel;
    if (is_file($abs)) {
        unlink($abs);
    }
}

// Load one product by id, or null if not found
function getProduct($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT id, name, price, category FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $pid, $pname, $pprice, $pcat);
    $row = null;
    if (mysqli_stmt_fetch($stmt)) {
        $row = ['id' => $pid, 'name' => $pname, 'price' => $pprice, 'category' => $pcat];
    }
    mysqli_stmt_close($stmt);
    return $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add' || $action === 'edit') {
            $id       = (int)($_POST['id'] ?? 0);
            $name     = trim($_POST['name'] ?? '');
            $price    = $_POST['price'] ?? '';
            $category = $_POST['category'] ?? '';
            $file     = $_FILES['image'] ?? null;
            $hasFile  = $file !== null && $file['error'] !== UPLOAD_ERR_NO_FILE;

            $newExt = null;
            $error  = null;

            if ($name === '' || mb_strlen($name) > 100) {
                $error = 'Item name is required (max 100 characters).';
            } elseif (preg_match('/[\\\\\/:*?"<>|]/', $name)) {
                $error = 'Name cannot contain these characters: \ / : * ? " < > |';
            } elseif (!is_numeric($price) || (float)$price < 0) {
                $error = 'Enter a valid price.';
            } elseif (!in_array($category, $allowedCategories, true)) {
                $error = 'Choose a valid category.';
            } elseif ($action === 'add' && !$hasFile) {
                $error = 'Please upload an image for the item.';
            } elseif ($hasFile && $file['error'] !== UPLOAD_ERR_OK) {
                $error = 'Image upload failed. Please try again.';
            } elseif ($hasFile && $file['size'] > $maxImageSize) {
                $error = 'Image must be 2 MB or smaller.';
            } elseif ($hasFile) {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime  = $finfo->file($file['tmp_name']);
                if (!isset($allowedImageTypes[$mime])) {
                    $error = 'Only PNG, JPG, or WEBP images are allowed.';
                } else {
                    $newExt = $allowedImageTypes[$mime];
                }
            }

            if ($error !== null) {
                flash('error', $error);

            } elseif ($action === 'add') {
                $priceVal  = round((float)$price, 2);
                $dir       = categoryDir($category, $categoryFolders);
                $targetRel = $dir . "/" . $name . "." . $newExt;
                ensureDir($dir);

                $stmt = mysqli_prepare($conn, "INSERT INTO products (name, price, category) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "sds", $name, $priceVal, $category);
                mysqli_stmt_execute($stmt);
                $newId = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt);

                if (move_uploaded_file($file['tmp_name'], __DIR__ . "/" . $targetRel)) {
                    flash('success', "\"$name\" was added to the menu with its image.");
                } else {
                    // Image could not be saved, so remove the row to keep things consistent
                    $del = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
                    mysqli_stmt_bind_param($del, "i", $newId);
                    mysqli_stmt_execute($del);
                    mysqli_stmt_close($del);
                    flash('error', 'Could not save the image file. Check folder permissions.');
                }

            } else { // edit
                $old = getProduct($conn, $id);

                if (!$old) {
                    flash('error', 'Item not found.');
                } else {
                    $priceVal = round((float)$price, 2);
                    $stmt = mysqli_prepare($conn, "UPDATE products SET name = ?, price = ?, category = ? WHERE id = ?");
                    mysqli_stmt_bind_param($stmt, "sdsi", $name, $priceVal, $category, $id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);

                    $oldRel = findImage($old['name'], $old['category'], $categoryFolders, $premiumDir, $imageExtensions);
                    $dir    = categoryDir($category, $categoryFolders);

                    if ($hasFile) {
                        // New picture uploaded: save it and remove the old one
                        $newRel = $dir . "/" . $name . "." . $newExt;
                        ensureDir($dir);

                        if (move_uploaded_file($file['tmp_name'], __DIR__ . "/" . $newRel)) {
                            if ($oldRel !== null && strcasecmp($oldRel, $newRel) !== 0) {
                                deleteFile($oldRel);
                            }
                            flash('success', 'Item updated successfully.');
                        } else {
                            flash('error', 'Item updated, but the new image could not be saved. Check folder permissions.');
                        }

                    } elseif ($oldRel !== null && ($old['name'] !== $name || $old['category'] !== $category)) {
                        // No new picture, but name or category changed: move the existing image to match
                        $ext    = pathinfo($oldRel, PATHINFO_EXTENSION);
                        $newRel = $dir . "/" . $name . "." . $ext;
                        ensureDir($dir);

                        $sameFile = strcasecmp($newRel, $oldRel) === 0;
                        if ($sameFile || !is_file(__DIR__ . "/" . $newRel)) {
                            if (rename(__DIR__ . "/" . $oldRel, __DIR__ . "/" . $newRel)) {
                                flash('success', 'Item updated successfully.');
                            } else {
                                flash('error', 'Item updated, but the image could not be moved. Check folder permissions.');
                            }
                        } else {
                            flash('error', 'Item updated, but an image with the new name already exists, so the old picture was kept.');
                        }

                    } else {
                        flash('success', 'Item updated successfully.');
                    }
                }
            }

        } elseif ($action === 'delete') {
            $id  = (int)($_POST['id'] ?? 0);
            $old = getProduct($conn, $id);

            if (!$old) {
                flash('error', 'Item not found.');
            } else {
                $rel = findImage($old['name'], $old['category'], $categoryFolders, $premiumDir, $imageExtensions);

                $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                if ($rel !== null) {
                    deleteFile($rel);
                }
                flash('success', 'Item deleted successfully.');
            }
        }

    } catch (mysqli_sql_exception $ex) {
        if ($ex->getCode() == 1062) {
            flash('error', 'An item with this name already exists.');
        } else {
            flash('error', 'Database error: ' . $ex->getMessage());
        }
    }

    header("Location: manage_menu.php");
    exit();
}

$flash  = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$editId = (int)($_GET['edit'] ?? 0);

$products = [];
$res = mysqli_query($conn, "SELECT id, name, price, category FROM products
                            ORDER BY FIELD(category, 'Veg Burger', 'Non-Veg Burger', 'Combo', 'Drink'), name");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $row['id'] = (int)$row['id'];
        $rel = findImage($row['name'], $row['category'], $categoryFolders, $premiumDir, $imageExtensions);
        $row['image'] = $rel !== null ? urlPath($rel) : "Assets/logo.png";
        $products[] = $row;
    }
}

// Design mate: category pramane emoji ane ginti
$catEmoji = ["Veg Burger" => "🥬", "Non-Veg Burger" => "🍗", "Combo" => "🍱", "Drink" => "🥤"];
$catCount = array_fill_keys($allowedCategories, 0);
foreach ($products as $p) {
    if (isset($catCount[$p['category']])) {
        $catCount[$p['category']]++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            --paper: #fff3d6;
            --paper-ink: #2b2118;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            color: var(--ink);
            min-height: 100vh;
            background:
                radial-gradient(circle at 8% -5%, rgba(230,57,70,.28), transparent 38%),
                radial-gradient(circle at 100% 60%, rgba(255,183,3,.12), transparent 40%),
                var(--bg);
            background-attachment: fixed;
            overflow-x: hidden;
        }

        /* ---------- HEADER ---------- */
        .nav-container {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: #0b0b0b;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 12px 45px;
            min-height: 64px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
            text-decoration: none;
            font-size: 20px;
            font-weight: 700;
            z-index: 1001;
        }

        .brand img {
            width: 38px;
            height: 38px;
            object-fit: contain;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 28px;
        }

        .nav-right > a {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            font-size: 17px;
            transition: color .2s;
        }

        .nav-right > a:hover {
            color: #ff4d4d;
        }

        .nav-right > a.active {
            color: var(--gold);
        }

        .profile {
            position: relative;
            margin-left: 4px;
        }

        .profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
            display: block;
            background: var(--card2);
        }

        .dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 52px;
            min-width: 150px;
            z-index: 1001;
            background: var(--red);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,.5);
        }

        .dropdown a {
            display: block;
            padding: 11px 16px;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .dropdown a:hover {
            background: var(--red2);
        }

        /* Hamburger */
        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            z-index: 1001;
            padding: 6px;
        }

        .hamburger span {
            display: block;
            width: 26px;
            height: 3px;
            background: #fff;
            border-radius: 2px;
            transition: .3s;
        }

        .hamburger.active span:nth-child(1) {
            transform: rotate(45deg) translate(6px, 6px);
        }
        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }
        .hamburger.active span:nth-child(3) {
            transform: rotate(-45deg) translate(6px, -6px);
        }

        /* ---------- PAGE ---------- */
        .page {
            max-width: 1150px;
            margin: 0 auto;
            padding: 0 20px 90px;
        }

        .hero {
            padding: 44px 0 26px;
        }

        .hi {
            color: var(--gold);
            font-weight: 800;
            font-size: 13px;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .hero h1 {
            font-family: 'Bebas Neue', sans-serif;
            font-weight: 400;
            font-size: clamp(54px, 10vw, 96px);
            line-height: 1;
            letter-spacing: 2px;
            color: var(--ink);
            text-align: left;
            margin: 0;
        }

        .hero h1 span {
            color: transparent;
            -webkit-text-stroke: 2px var(--red);
        }

        /* ---------- FLASH ---------- */
        .flash {
            margin: 0 0 22px;
            padding: 14px 20px;
            border-radius: 16px;
            font-weight: 800;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .flash.success {
            color: #052e16;
            background: linear-gradient(90deg, #4ade80, #bbf7d0);
            box-shadow: 0 10px 30px rgba(74,222,128,.22);
        }

        .flash.error {
            color: #fff;
            background: linear-gradient(90deg, var(--red2), var(--red));
            box-shadow: 0 10px 30px rgba(230,57,70,.25);
        }

        /* ---------- ADD ITEM (paper slip) ---------- */
        .slip {
            position: relative;
            background: var(--paper);
            color: var(--paper-ink);
            border-radius: 26px;
            padding: 30px 32px 28px;
            box-shadow: 0 20px 50px rgba(0,0,0,.55);
            margin-bottom: 40px;
        }

        .slip::before,
        .slip::after {
            content: "";
            position: absolute;
            top: 70px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--bg);
        }

        .slip::before { left: -14px; }
        .slip::after  { right: -14px; }

        .slip .tag {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 3px;
            color: var(--red);
        }

        .slip h2 {
            font-family: 'Bebas Neue', sans-serif;
            font-weight: 400;
            font-size: 44px;
            letter-spacing: 1.5px;
            line-height: 1;
            margin: 4px 0 8px;
        }

        .slip .hint {
            font-size: 13px;
            color: #7a6a4f;
            margin-bottom: 14px;
        }

        .slip .cut {
            border-top: 2px dashed #c9b88f;
            margin: 14px -32px 20px;
        }

        .add-grid {
            display: grid;
            grid-template-columns: 1.4fr .7fr 1fr;
            gap: 18px;
            margin-bottom: 18px;
        }

        .field label {
            display: block;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #7a6a4f;
            margin-bottom: 4px;
        }

        .slip input[type="text"],
        .slip input[type="number"],
        .slip select {
            width: 100%;
            background: transparent;
            border: none;
            border-bottom: 2px solid #c9b88f;
            border-radius: 0;
            padding: 8px 2px;
            font-family: inherit;
            font-size: 15px;
            color: var(--paper-ink);
            outline: none;
            transition: .2s;
        }

        .slip input:focus,
        .slip select:focus {
            border-bottom-color: var(--red);
        }

        .upload {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .preview {
            width: 84px;
            height: 84px;
            border-radius: 18px;
            background: #fff;
            border: 2px dashed #c9b88f;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }

        .slip input[type="file"] {
            font-family: inherit;
            font-size: 13px;
            color: var(--paper-ink);
            max-width: 100%;
        }

        input[type="file"]::file-selector-button {
            font-family: inherit;
            font-weight: 800;
            font-size: 13px;
            cursor: pointer;
            border: none;
            border-radius: 999px;
            padding: 9px 18px;
            margin-right: 12px;
            background: var(--paper-ink);
            color: var(--gold);
            transition: .2s;
        }

        input[type="file"]::file-selector-button:hover {
            background: var(--red);
            color: #fff;
        }

        .add-btn {
            width: 100%;
            border: none;
            cursor: pointer;
            background: var(--red);
            color: #fff;
            font-family: inherit;
            font-weight: 800;
            font-size: 16px;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 15px;
            border-radius: 14px;
            box-shadow: 0 5px 0 var(--red2);
            transition: .15s;
        }

        .add-btn:hover { background: #f1485a; }
        .add-btn:active { transform: translateY(4px); box-shadow: 0 1px 0 var(--red2); }

        /* ---------- CURRENT MENU ---------- */
        .sec-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .sec-head h2 {
            font-family: 'Bebas Neue', sans-serif;
            font-weight: 400;
            font-size: 46px;
            letter-spacing: 2px;
        }

        .sec-head h2 span {
            color: var(--gold);
        }

        .filters {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filters button {
            font-family: inherit;
            font-weight: 800;
            font-size: 13px;
            cursor: pointer;
            color: var(--muted);
            background: var(--card);
            border: 2px solid var(--line);
            border-radius: 999px;
            padding: 8px 16px;
            transition: .2s;
        }

        .filters button b {
            color: var(--gold);
            margin-left: 4px;
        }

        .filters button:hover,
        .filters button.on {
            color: #000;
            background: var(--gold);
            border-color: var(--gold);
        }

        .filters button:hover b,
        .filters button.on b {
            color: #000;
        }

        .table-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 14px 40px rgba(0,0,0,.45);
        }

        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 720px;
        }

        th {
            background: linear-gradient(90deg, var(--red), var(--red2));
            color: #fff;
            white-space: nowrap;
            font-size: 12px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 14px;
            text-align: left;
        }

        td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--line);
            font-size: 14px;
            font-weight: 600;
            vertical-align: middle;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr {
            transition: .15s;
        }

        tbody tr.item:hover {
            background: var(--card2);
        }

        .thumb {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 16px;
            background: var(--card2);
            display: block;
            box-shadow: 0 6px 16px rgba(0,0,0,.45);
        }

        .name {
            font-family: 'Bebas Neue', sans-serif;
            font-weight: 400;
            font-size: 24px;
            letter-spacing: 1px;
        }

        .cat-tag {
            display: inline-block;
            padding: 5px 13px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            background: var(--card2);
            border: 1px solid var(--line);
            white-space: nowrap;
        }

        .price {
            color: var(--gold);
            font-weight: 800;
            font-size: 16px;
            white-space: nowrap;
        }

        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .actions form {
            margin: 0;
        }

        .btn,
        .actions button {
            font-family: inherit;
            font-weight: 800;
            font-size: 13px;
            border: none;
            border-radius: 999px;
            padding: 9px 18px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: .2s;
        }

        .btn-edit {
            background: rgba(96,165,250,.16);
            color: #93c5fd;
        }

        .btn-edit:hover {
            background: #3b82f6;
            color: #fff;
        }

        .actions button {
            background: rgba(230,57,70,.16);
            color: #fca5a5;
        }

        .actions button:hover {
            background: var(--red);
            color: #fff;
        }

        .empty-row td {
            text-align: center;
            color: var(--muted);
            padding: 36px;
            font-size: 16px;
        }

        /* ---------- EDIT ROW ---------- */
        .edit-cell {
            background: var(--card2);
            border-left: 4px solid var(--gold);
        }

        .edit-form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
            margin: 0;
        }

        .edit-form input[type="text"],
        .edit-form input[type="number"],
        .edit-form select {
            padding: 11px 14px;
            border-radius: 12px;
            border: 2px solid transparent;
            background: var(--card);
            color: var(--ink);
            font-family: inherit;
            font-size: 14px;
            outline: none;
            transition: .2s;
        }

        .edit-form input:focus,
        .edit-form select:focus {
            border-color: var(--gold);
        }

        .edit-form input[type="file"] {
            font-family: inherit;
            font-size: 13px;
            color: var(--muted);
            max-width: 280px;
        }

        .edit-form .note {
            color: var(--muted);
            font-size: 12px;
        }

        .btn-save {
            background: #4ade80;
            color: #052e16;
        }

        .btn-save:hover {
            background: #86efac;
        }

        .btn-cancel {
            background: var(--card);
            color: var(--muted);
            border: 2px solid var(--line);
        }

        .btn-cancel:hover {
            background: var(--line);
            color: #fff;
        }

        .edit-form button.btn-save {
            border: none;
            border-radius: 999px;
            padding: 11px 22px;
            font-family: inherit;
            font-weight: 800;
            cursor: pointer;
        }

        /* ==================== RESPONSIVE ==================== */

        /* Tablets */
        @media (max-width: 900px) {
            .nav-container { padding: 12px 28px; }
            .page { padding: 0 18px 70px; }
            .add-grid {
                grid-template-columns: 1fr 1fr;
            }
            .add-grid .field:first-child {
                grid-column: 1 / -1;
            }
        }

        /* Mobile landscape / large phones */
        @media (max-width: 768px) {
            .nav-container {
                padding: 10px 18px;
            }

            .hamburger { display: flex; }

            .nav-right {
                position: fixed;
                top: 0;
                right: -100%;
                width: 280px;
                height: 100vh;
                background: #111;
                flex-direction: column;
                align-items: flex-start;
                padding: 90px 30px 40px;
                gap: 8px;
                transition: right .35s ease;
                box-shadow: -10px 0 30px rgba(0,0,0,.5);
                z-index: 999;
            }

            .nav-right.open { right: 0; }

            .nav-right > a {
                font-size: 18px;
                padding: 12px 0;
                width: 100%;
                border-bottom: 1px solid #222;
            }

            .profile {
                margin: 20px 0 0;
                width: 100%;
            }

            .profile img {
                width: 48px;
                height: 48px;
            }

            .dropdown {
                position: static;
                display: none;
                margin-top: 10px;
                width: 100%;
                border-radius: 10px;
            }

            .dropdown.show { display: block; }
            .dropdown a { padding: 12px 16px; }

            .hero {
                padding: 32px 0 22px;
            }

            .hero h1 {
                font-size: clamp(48px, 14vw, 72px);
            }

            .slip {
                padding: 26px 22px 24px;
                border-radius: 20px;
            }

            .slip::before,
            .slip::after {
                display: none;
            }

            .slip h2 {
                font-size: 36px;
            }

            .slip .cut {
                margin: 14px -22px 18px;
            }

            .add-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }

            .upload {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .preview {
                width: 76px;
                height: 76px;
                font-size: 28px;
            }

            .sec-head {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .sec-head h2 {
                font-size: 36px;
            }

            .filters {
                width: 100%;
                overflow-x: auto;
                flex-wrap: nowrap;
                padding-bottom: 6px;
                -webkit-overflow-scrolling: touch;
            }

            .filters button {
                flex-shrink: 0;
                font-size: 12px;
                padding: 7px 14px;
            }

            table {
                min-width: 640px;
            }

            .thumb {
                width: 56px;
                height: 56px;
                border-radius: 12px;
            }

            .name {
                font-size: 20px;
            }

            .edit-form {
                flex-direction: column;
                align-items: stretch;
            }

            .edit-form input[type="text"],
            .edit-form input[type="number"],
            .edit-form select,
            .edit-form input[type="file"] {
                width: 100%;
                max-width: 100%;
            }
        }

        /* Small phones */
        @media (max-width: 480px) {
            .brand { font-size: 16px; gap: 8px; }
            .brand img { width: 32px; height: 32px; }

            .page { padding: 0 14px 50px; }

            .hero {
                padding: 26px 0 18px;
            }

            .hi {
                font-size: 11px;
                letter-spacing: 2.5px;
            }

            .hero h1 {
                font-size: clamp(42px, 16vw, 58px);
                letter-spacing: 1px;
            }

            .flash {
                padding: 12px 16px;
                font-size: 13px;
                border-radius: 12px;
            }

            .slip {
                padding: 22px 16px 20px;
                border-radius: 16px;
                margin-bottom: 30px;
            }

            .slip h2 {
                font-size: 30px;
            }

            .slip .hint {
                font-size: 12px;
            }

            .slip .cut {
                margin: 12px -16px 16px;
            }

            .field label {
                font-size: 10px;
            }

            .slip input[type="text"],
            .slip input[type="number"],
            .slip select {
                font-size: 14px;
            }

            .preview {
                width: 68px;
                height: 68px;
                font-size: 24px;
                border-radius: 14px;
            }

            .add-btn {
                padding: 13px;
                font-size: 14px;
            }

            .sec-head h2 {
                font-size: 30px;
            }

            .filters button {
                font-size: 11px;
                padding: 6px 12px;
            }

            .table-card {
                border-radius: 16px;
            }

            th {
                padding: 12px 10px;
                font-size: 11px;
            }

            td {
                padding: 10px;
                font-size: 13px;
            }

            .thumb {
                width: 48px;
                height: 48px;
                border-radius: 10px;
            }

            .name {
                font-size: 18px;
            }

            .cat-tag {
                font-size: 11px;
                padding: 4px 10px;
            }

            .price {
                font-size: 14px;
            }

            .btn,
            .actions button {
                font-size: 12px;
                padding: 8px 14px;
            }
        }

        /* Extra small */
        @media (max-width: 360px) {
            .slip h2 { font-size: 26px; }
            .sec-head h2 { font-size: 26px; }
            .name { font-size: 16px; }
        }
    </style>
</head>
<body>

    <header class="nav-container">
        <a href="index.php" class="brand">
            <img src="Assets/logo.png" alt="logo">
            <span>B K's Burger House</span>
        </a>

        <div class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <nav class="nav-right" id="navMenu">
            <a href="index.php">Home</a>
            <a href="sales.php">Sales</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="manage_menu.php" class="active">Manage Menu</a>

            <div class="profile">
                <img src="uploads/bk.png" id="profileBtn" alt="Profile" onerror="this.onerror=null;this.src='Assets/logo.png'">
                <div class="dropdown" id="dropMenu">
                    <a href="account.php">Account</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </nav>
    </header>

    <div class="page">

        <div class="hero">
            <p class="hi">Admin panel</p>
            <h1>Manage <span>menu</span></h1>
        </div>

        <?php if ($flash): ?>
            <div class="flash <?= e($flash['type']) ?>">
                <?= $flash['type'] === 'success' ? '✅' : '⚠️' ?> <?= e($flash['text']) ?>
            </div>
        <?php endif; ?>

        <!-- ---------- ADD NEW ITEM ---------- -->
        <div class="slip">
            <span class="tag">NEW ITEM</span>
            <h2>Add new item</h2>
            <p class="hint">Upload a picture (PNG, JPG or WEBP, max 2 MB). It is saved to the matching category folder automatically.</p>
            <div class="cut"></div>

            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">

                <div class="add-grid">
                    <div class="field">
                        <label for="a_name">Item name</label>
                        <input type="text" id="a_name" name="name" maxlength="100" required>
                    </div>
                    <div class="field">
                        <label for="a_price">Price (₹)</label>
                        <input type="number" id="a_price" name="price" min="0" step="0.01" required>
                    </div>
                    <div class="field">
                        <label for="a_cat">Category</label>
                        <select id="a_cat" name="category" required>
                            <?php foreach ($allowedCategories as $cat): ?>
                                <option value="<?= e($cat) ?>"><?= e($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="upload">
                    <div class="preview" id="previewBox">
                        <span id="previewEmoji">📷</span>
                        <img id="previewImg" alt="Preview">
                    </div>
                    <input type="file" name="image" id="a_image" accept="image/png,image/jpeg,image/webp" required>
                </div>

                <button type="submit" class="add-btn">+ Add Item</button>
            </form>
        </div>

        <!-- ---------- CURRENT MENU ---------- -->
        <div class="sec-head">
            <h2>Current <span>menu</span></h2>
            <div class="filters" id="filters">
                <button type="button" class="on" data-cat="all">All <b><?= count($products) ?></b></button>
                <?php foreach ($allowedCategories as $cat): ?>
                    <button type="button" data-cat="<?= e($cat) ?>"><?= $catEmoji[$cat] ?> <?= e($cat) ?> <b><?= (int)$catCount[$cat] ?></b></button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="table-card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php if (!$products): ?>
                        <tr class="empty-row"><td colspan="5">🍽️ No items yet.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($products as $p): ?>
                        <?php if ($p['id'] === $editId): ?>
                            <tr>
                                <td colspan="5" class="edit-cell">
                                    <form method="post" enctype="multipart/form-data" class="edit-form">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">

                                        <input type="text" name="name" value="<?= e($p['name']) ?>" maxlength="100" required>
                                        <input type="number" name="price" value="<?= e($p['price']) ?>" min="0" step="0.01" required>
                                        <select name="category" required>
                                            <?php foreach ($allowedCategories as $cat): ?>
                                                <option value="<?= e($cat) ?>" <?= $cat === $p['category'] ? 'selected' : '' ?>><?= e($cat) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="file" name="image" accept="image/png,image/jpeg,image/webp">
                                        <span class="note">Leave the picture empty to keep the current one.</span>

                                        <button type="submit" class="btn btn-save">Save</button>
                                        <a href="manage_menu.php" class="btn btn-cancel">Cancel</a>
                                    </form>
                                </td>
                            </tr>
                        <?php else: ?>
                            <tr class="item" data-cat="<?= e($p['category']) ?>">
                                <td>
                                    <img class="thumb" src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
                                </td>
                                <td class="name"><?= e($p['name']) ?></td>
                                <td><span class="cat-tag"><?= e($catEmoji[$p['category']] ?? '') ?> <?= e($p['category']) ?></span></td>
                                <td class="price">₹<?= e($p['price']) ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="manage_menu.php?edit=<?= (int)$p['id'] ?>" class="btn btn-edit">Edit</a>
                                        <form method="post" onsubmit="return confirm('Are you sure you want to delete this item from the menu?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                            <button type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        /* Hamburger menu */
        const hamburger = document.getElementById("hamburger");
        const navMenu = document.getElementById("navMenu");

        hamburger.addEventListener("click", function () {
            hamburger.classList.toggle("active");
            navMenu.classList.toggle("open");
        });

        navMenu.querySelectorAll("a").forEach(function (link) {
            link.addEventListener("click", function () {
                hamburger.classList.remove("active");
                navMenu.classList.remove("open");
            });
        });

        document.addEventListener("DOMContentLoaded", function () {
            const btn = document.getElementById("profileBtn");
            const menu = document.getElementById("dropMenu");

            btn.addEventListener("click", function (e) {
                e.stopPropagation();
                if (window.innerWidth <= 768) {
                    menu.classList.toggle("show");
                } else {
                    menu.style.display = menu.style.display === "block" ? "none" : "block";
                }
            });

            document.addEventListener("click", function () {
                menu.style.display = "none";
                menu.classList.remove("show");
            });

            /* Category filter chips */
            const chips = document.querySelectorAll("#filters button");
            chips.forEach(function (chip) {
                chip.addEventListener("click", function () {
                    chips.forEach(function (c) { c.classList.remove("on"); });
                    chip.classList.add("on");
                    const cat = chip.dataset.cat;
                    document.querySelectorAll("tr.item").forEach(function (row) {
                        row.style.display = (cat === "all" || row.dataset.cat === cat) ? "" : "none";
                    });
                });
            });

            /* Image preview (Add New Item) */
            const input = document.getElementById("a_image");
            const img = document.getElementById("previewImg");
            const emoji = document.getElementById("previewEmoji");
            input.addEventListener("change", function () {
                const f = input.files && input.files[0];
                if (!f) {
                    img.style.display = "none";
                    emoji.style.display = "";
                    return;
                }
                img.src = URL.createObjectURL(f);
                img.style.display = "block";
                emoji.style.display = "none";
            });
        });
    </script>
</body>
</html>