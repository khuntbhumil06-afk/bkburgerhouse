<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username'])) {
    header("location: login.php");
    exit();
}

if ($_SESSION["admin"] == 'YES') {
    header("location: sales.php");
    exit();
}

$username  = $_SESSION['username'];
$cartItemN = $_SESSION['cartItemN'] ?? [];
$cartItemQ = $_SESSION['cartItemQ'] ?? [];
$cartItemP = $_SESSION['cartItemP'] ?? [];
$netAmount = (float)($_SESSION['netAmount'] ?? 0);
$subtotal  = (float)($_SESSION['total'] ?? 0);
$discount  = (float)($_SESSION['discount'] ?? 0);
$tax       = (float)($_SESSION['tax'] ?? 0);

$fullname = $email = $cno = $cvv = $expiry = $address = $payment_method = "";
$err = "";
$fullname_err = $email_err = $cno_err = $cvv_err = $expiry_err = $address_err = $paymentmethod_err = "";

$allowedMethods = ['card', 'upi', 'cod'];

// Rs. format (FPDF ma ₹ chhap nathi thato, etle Rs. vaparo chhe)
function rs($value) {
    return "Rs. " . number_format((float)$value, 2, '.', ',');
}

// Error message ni HTML (khali hoy to kai nahi)
function fieldErr($msg) {
    return $msg ? '<small class="err">' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</small>' : '';
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $fullname       = trim($_POST['fullname'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $cno            = trim($_POST['cno'] ?? '');
    $cvv            = trim($_POST['cvv'] ?? '');
    $expiry         = trim($_POST['expiry'] ?? '');
    $address        = trim($_POST['address'] ?? '');
    $payment_method = $_POST['payment_method'] ?? '';

    if (empty($fullname) || !preg_match("/^[a-zA-Z ]+$/", $fullname)) {
        $fullname_err = "Enter valid full name";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_err = "Enter valid email";
    }
    if (empty($address)) {
        $address_err = "Enter address";
    }
    if (empty($payment_method) || !in_array($payment_method, $allowedMethods, true)) {
        $paymentmethod_err = "Select payment method";
    }

    // Card details fakt "Card" payment ma j required chhe
    if ($payment_method === 'card') {
        if (empty($cno) || !preg_match("/^[0-9]{16}$/", $cno)) {
            $cno_err = "Enter valid 16-digit card number";
        }
        if (empty($cvv) || !preg_match("/^[0-9]{4}$/", $cvv)) {
            $cvv_err = "Enter valid 4-digit CVV";
        }
        if (!preg_match("/^(0[1-9]|1[0-2])\/([0-9]{2})$/", $expiry)) {
            $expiry_err = "Enter valid expiry (MM/YY)";
        } else {
            [$mm, $yy] = explode('/', $expiry);
            // Expiry mahino puro thaya pachhi (1st of next month) card expired gannay
            $expiresAt = mktime(0, 0, 0, (int)$mm + 1, 1, 2000 + (int)$yy);
            if (time() >= $expiresAt) {
                $expiry_err = "This card has expired";
            }
        }
    }

    if (empty($fullname_err) && empty($email_err) && empty($cno_err) &&
        empty($cvv_err) && empty($expiry_err) && empty($address_err) && empty($paymentmethod_err)) {

        $allCN = implode(", ", $cartItemN);
        $allCQ = implode(", ", $cartItemQ);
        $_SESSION['addr'] = $address;

        $order_id     = date('dmYHis');
        $current_time = date("Y-m-d H:i:s");

        $sql = "INSERT INTO orders (fullname, username, order_id, item, quantity, email, address, price, details, status_time, payment_method)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Confirmed', ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssssssdss", $fullname, $username, $order_id, $allCN, $allCQ, $email, $address, $netAmount, $current_time, $payment_method);

            if (mysqli_stmt_execute($stmt)) {

                $totalQty = array_sum($cartItemQ);
                mysqli_query($conn, "UPDATE stats SET happy_customers = happy_customers + 1, burgers_sold = burgers_sold + " . (int)$totalQty . " WHERE id = 1");

                // ---------- PDF INVOICE ----------
                require_once __DIR__ . '/libs/fpdf.php';

                $receiptDir = __DIR__ . '/uploads/receipts';
                if (!is_dir($receiptDir)) {
                    mkdir($receiptDir, 0775, true);
                }

                $pdf = new FPDF('P', 'mm', 'A4');
                $pdf->SetMargins(15, 15, 15);
                $pdf->AddPage();

                // Logo (left)
                $logoPath = __DIR__ . '/Assets/logo.png';
                if (is_file($logoPath)) {
                    $pdf->Image($logoPath, 15, 12, 28);
                }

                // Shop name (red), city, INVOICE title
                $pdf->SetY(14);
                $pdf->SetTextColor(255, 0, 0);
                $pdf->SetFont('Arial', 'B', 20);
                $pdf->SetX(15);
                $pdf->Cell(180, 10, "B K's Burger House", 0, 1, 'C');

                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('Arial', '', 11);
                $pdf->SetX(15);
                $pdf->Cell(180, 7, "Surat, Gujarat", 0, 1, 'C');

                $pdf->Ln(4);
                $pdf->SetFont('Arial', 'B', 15);
                $pdf->SetX(15);
                $pdf->Cell(180, 10, "INVOICE", 0, 1, 'C');
                $pdf->Ln(4);

                // Order details
                $pdf->SetFont('Arial', '', 11);
                $pdf->SetX(15);
                $pdf->Cell(95, 7, "Order ID: " . $order_id, 0, 0);
                $pdf->Cell(85, 7, "Date: " . date("d-m-Y"), 0, 1);
                $pdf->SetX(15);
                $pdf->Cell(95, 7, "Customer: " . $fullname, 0, 0);
                $pdf->Cell(85, 7, "Email: " . $email, 0, 1);
                $pdf->SetX(15);
                $pdf->MultiCell(180, 7, "Address: " . $address, 0, 'L');
                $pdf->Ln(4);

                // Items table
                $w = [85, 25, 35, 35];
                $pdf->SetFillColor(230, 230, 230);
                $pdf->SetFont('Arial', 'B', 11);
                $pdf->SetX(15);
                $pdf->Cell($w[0], 9, 'Item',  1, 0, 'C', true);
                $pdf->Cell($w[1], 9, 'Qty',   1, 0, 'C', true);
                $pdf->Cell($w[2], 9, 'Price', 1, 0, 'C', true);
                $pdf->Cell($w[3], 9, 'Total', 1, 1, 'C', true);

                $pdf->SetFont('Arial', '', 11);
                foreach ($cartItemN as $i => $name) {
                    $qty       = (int)$cartItemQ[$i];
                    $lineTotal = (float)$cartItemP[$i];
                    $unitPrice = $qty > 0 ? $lineTotal / $qty : 0;

                    $pdf->SetX(15);
                    $pdf->Cell($w[0], 8, $name,          1, 0, 'L');
                    $pdf->Cell($w[1], 8, $qty,           1, 0, 'C');
                    $pdf->Cell($w[2], 8, rs($unitPrice), 1, 0, 'C');
                    $pdf->Cell($w[3], 8, rs($lineTotal), 1, 1, 'C');
                }

                // Summary
                $pdf->Ln(4);
                $sw = [135, 45];
                $pdf->SetFont('Arial', 'B', 11);

                $pdf->SetX(15);
                $pdf->Cell($sw[0], 8, 'Subtotal', 1, 0, 'L');
                $pdf->Cell($sw[1], 8, number_format($subtotal, 2, '.', ','), 1, 1, 'C');

                $pdf->SetX(15);
                $pdf->Cell($sw[0], 8, 'Discount (20%)', 1, 0, 'L');
                $pdf->Cell($sw[1], 8, '- ' . number_format($discount, 2, '.', ','), 1, 1, 'C');

                $pdf->SetX(15);
                $pdf->Cell($sw[0], 8, 'Tax (20%)', 1, 0, 'L');
                $pdf->Cell($sw[1], 8, number_format($tax, 2, '.', ','), 1, 1, 'C');

                $pdf->SetTextColor(255, 0, 0);
                $pdf->SetFont('Arial', 'B', 13);
                $pdf->SetX(15);
                $pdf->Cell($sw[0], 10, 'Grand Total', 1, 0, 'L');
                $pdf->Cell($sw[1], 10, number_format($netAmount, 2, '.', ','), 1, 1, 'C');

                // Footer
                $pdf->SetTextColor(0, 0, 0);
                $pdf->Ln(12);
                $pdf->SetFont('Arial', 'I', 11);
                $pdf->SetX(15);
                $pdf->Cell(180, 8, 'Thank you for ordering! Visit again', 0, 1, 'C');

                $receiptFile = 'uploads/receipts/Receipt_' . $order_id . '.pdf';
                $pdf->Output('F', __DIR__ . '/' . $receiptFile);
                // ---------- END PDF ----------

                $_SESSION['receipt_pdf'] = $receiptFile;
                $_SESSION['order_id']    = $order_id;
                $_SESSION['payment']     = "yes";

                unset($_SESSION['cartItemN'], $_SESSION['cartItemQ'], $_SESSION['cartItemP'], $_SESSION['netAmount']);

                header("location: success.php");
                exit();
            } else {
                $err = "Database error!";
            }

            mysqli_stmt_close($stmt);
        } else {
            $err = "SQL error!";
        }
    }
}

$img = "uploads/default.png";
$imgStmt = mysqli_prepare($conn, "SELECT image FROM loginform WHERE username=?");
if ($imgStmt) {
    mysqli_stmt_bind_param($imgStmt, "s", $username);
    mysqli_stmt_execute($imgStmt);
    mysqli_stmt_bind_result($imgStmt, $user_img);
    mysqli_stmt_fetch($imgStmt);
    mysqli_stmt_close($imgStmt);

    if (!empty($user_img)) {
        $img = "uploads/" . $user_img;
    }
}

$cartEmpty = empty($cartItemN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - B K's Burger House</title>
    <link rel="icon" href="Assets/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/6f42fc440c.js" crossorigin="anonymous"></script>
    <style>
        :root {
            --card: #161616;
            --card-2: #1e1e1e;
            --line: rgba(255,255,255,0.08);
            --red: #ff4d4d;
            --orange: #ff9800;
            --gold: #ffd54a;
            --text: #f5f5f5;
            --muted: #9a9a9a;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', 'Segoe UI', Arial, sans-serif;
        }

        html, body { overflow-x: hidden; }

        body {
            color: var(--text);
            min-height: 100vh;
            background: linear-gradient(rgba(0,0,0,0.85), rgba(0,0,0,0.85)),
                        url('Assets/images.png') no-repeat center center fixed;
            background-size: cover;
        }

        img { max-width: 100%; }

        /* ---------- TOP BAR ---------- */
        .topbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            width: 100%;
            padding: 10px 30px;
            background: #111;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            text-decoration: none;
            font-size: 20px;
            font-weight: 700;
            white-space: nowrap;
        }
        .brand img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }
        .nav-toggle {
            display: none;
            background: none;
            border: 0;
            color: #fff;
            font-size: 30px;
            line-height: 1;
            cursor: pointer;
            padding: 0 4px;
            margin-left: auto;
        }

        .right-ul {
            display: flex;
            align-items: center;
            gap: 25px;
            list-style: none;
        }
        .right-ul a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s;
        }
        .right-ul a:hover,
        .right-ul a.active {
            color: #ff4d4d;
        }

        .profile { position: relative; }
        .profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            object-fit: cover;
            display: block;
        }
        .dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 50px;
            background: #222;
            min-width: 150px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.4);
            z-index: 1001;
        }
        .dropdown a {
            display: block;
            padding: 10px 15px;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
        }
        .dropdown a:hover { background: #ff4d4d; }

        /* ---------- PAGE ---------- */
        .page {
            max-width: 1100px;
            margin: 0 auto;
            padding: 28px 20px 60px;
        }

        .steps {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }
        .step {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--muted);
            font-size: 14px;
        }
        .step span {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--card-2);
            border: 1px solid var(--line);
            font-weight: 700;
            font-size: 13px;
        }
        .step.done { color: #ddd; }
        .step.done span { background: #2ecc71; border-color: #2ecc71; color: #000; }
        .step.active { color: #fff; font-weight: 600; }
        .step.active span { background: var(--red); border-color: var(--red); color: #fff; }
        .step-line { width: 50px; height: 2px; background: var(--line); }

        .page-title {
            text-align: center;
            font-size: 30px;
            font-weight: 700;
            margin-bottom: 24px;
        }
        .page-title em { color: var(--red); font-style: normal; }

        /* ---------- LAYOUT ---------- */
        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 24px;
            align-items: start;
        }
        .checkout-grid > * { min-width: 0; }
        .form-col {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.4);
        }
        .card-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 17px;
            font-weight: 600;
            margin-bottom: 16px;
        }
        .card-title i { color: var(--red); }

        .alert {
            background: rgba(255,77,77,0.12);
            border: 1px solid rgba(255,77,77,0.4);
            color: #ff8a8a;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 14px;
        }

        /* ---------- FIELDS ---------- */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .grid-2 > * { min-width: 0; }
        .field {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 14px;
        }
        .field:last-child { margin-bottom: 0; }
        .field label {
            font-size: 13px;
            color: var(--muted);
            font-weight: 600;
        }
        .field input,
        .field textarea {
            width: 100%;
            background: #0f0f0f;
            border: 1px solid #2c2c2c;
            color: #fff;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .field input::placeholder,
        .field textarea::placeholder { color: #666; }
        .field input:focus,
        .field textarea:focus {
            border-color: var(--red);
            box-shadow: 0 0 0 3px rgba(255,77,77,0.15);
        }
        .field textarea { min-height: 80px; resize: vertical; }
        .err { color: #ff6b6b; font-size: 12px; }

        /* ---------- PAYMENT METHOD ---------- */
        .methods {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }
        .method {
            position: relative;
            display: block;
            cursor: pointer;
        }
        .method input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .method-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 100%;
            padding: 16px 10px;
            background: #0f0f0f;
            border: 1px solid #2c2c2c;
            border-radius: 12px;
            font-size: 14px;
            text-align: center;
            transition: all 0.2s;
        }
        .method-box i { font-size: 22px; color: var(--muted); }
        .method:hover .method-box { border-color: #555; }
        .method input:checked + .method-box {
            border-color: var(--red);
            background: rgba(255,77,77,0.08);
            box-shadow: 0 0 0 3px rgba(255,77,77,0.15);
        }
        .method input:checked + .method-box i { color: var(--red); }

        .cards-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            font-size: 13px;
            color: var(--muted);
        }
        .cards-row i { font-size: 28px; }
        .cards-row .fa-cc-mastercard { color: #eb001b; }
        .cards-row .fa-cc-visa { color: #1a73e8; }

        /* ---------- CREDIT CARD PREVIEW ---------- */
        .cc-preview {
            position: relative;
            overflow: hidden;
            width: 100%;
            max-width: 380px;
            height: 200px;
            margin: 0 auto 22px;
            padding: 20px 22px;
            border-radius: 18px;
            border: 1px solid rgba(255,255,255,0.1);
            background: linear-gradient(135deg, #2b2b2b, #0d0d0d);
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .cc-preview::before {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            right: -90px;
            top: -110px;
            background: radial-gradient(circle, rgba(255,77,77,0.35), transparent 70%);
        }
        .cc-top {
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cc-chip {
            width: 42px;
            height: 30px;
            border-radius: 6px;
            background: linear-gradient(135deg, #ffd54a, #b8860b);
        }
        .cc-brand {
            font-size: 34px;
            color: #fff;
        }
        .cc-number {
            position: relative;
            font-family: monospace;
            font-size: 22px;
            letter-spacing: 3px;
            color: #fff;
            white-space: nowrap;
        }
        .cc-bottom {
            position: relative;
            display: flex;
            justify-content: space-between;
            gap: 16px;
        }
        .cc-bottom > div:first-child { min-width: 0; }
        .cc-bottom small {
            display: block;
            font-size: 10px;
            letter-spacing: 1px;
            color: var(--muted);
        }
        .cc-bottom > div:last-child { text-align: right; }
        .cc-name,
        .cc-exp {
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #fff;
        }
        .cc-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* ---------- SUMMARY ---------- */
        .summary {
            position: sticky;
            top: 100px;
        }
        .sum-items {
            list-style: none;
            max-height: 280px;
            overflow-y: auto;
            margin-bottom: 12px;
        }
        .sum-items li {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px dashed var(--line);
        }
        .sum-items li > div:first-child { min-width: 0; }
        .sum-items li:last-child { border-bottom: none; }
        .sum-name { font-weight: 600; font-size: 15px; word-break: break-word; }
        .sum-qty { color: var(--muted); font-size: 13px; margin-top: 3px; }
        .sum-price { font-weight: 700; color: var(--gold); white-space: nowrap; }
        .empty-cart { color: var(--muted); text-align: center; padding: 18px 0; }

        .sum-row {
            display: flex;
            justify-content: space-between;
            padding: 7px 0;
            color: #cfcfcf;
            font-size: 14px;
        }
        .sum-row.discount span:last-child { color: #2ecc71; }
        .sum-total {
            margin-top: 10px;
            padding-top: 14px;
            border-top: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .sum-total span:first-child { font-size: 16px; font-weight: 600; }
        .sum-total span:last-child { font-size: 24px; font-weight: 800; color: var(--red); }

        .btn-pay {
            width: 100%;
            margin-top: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            background: linear-gradient(135deg, var(--red), var(--orange));
            box-shadow: 0 8px 20px rgba(255,77,77,0.3);
            transition: transform 0.15s, box-shadow 0.2s;
        }
        .btn-pay:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px rgba(255,77,77,0.4);
        }
        .btn-pay:disabled {
            background: #333;
            box-shadow: none;
            cursor: not-allowed;
        }

        .btn-ghost {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
            padding: 12px;
            border-radius: 12px;
            background: var(--card-2);
            border: 1px solid var(--line);
            color: #ddd;
            text-decoration: none;
            font-weight: 600;
        }
        .btn-ghost:hover { background: #262626; }

        .secure-note {
            margin-top: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: var(--muted);
            font-size: 12px;
        }
        .secure-note i { color: #2ecc71; }

        .hidden { display: none !important; }

        /* ---------- RESPONSIVE ---------- */

        /* iPhone / touch: "fixed" background kaam nathi karto */
        @media (max-width: 900px), (hover: none) {
            body {
                background: linear-gradient(rgba(0,0,0,0.85), rgba(0,0,0,0.85)),
                            url('Assets/images.png') no-repeat center center scroll;
                background-size: cover;
            }
        }

        /* Tablet */
        @media (max-width: 900px) {
            .checkout-grid { grid-template-columns: 1fr; }
            .summary { position: static; }
            .page { padding: 22px 16px 50px; }
        }

        /* Mobile: hamburger menu */
        @media (max-width: 800px) {
            .topbar { flex-wrap: wrap; padding: 10px 15px; gap: 10px; }
            .brand { font-size: 18px; }
            .nav-toggle { display: block; }
            .right-ul {
                display: none;
                order: 3;
                width: 100%;
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
                padding: 10px 0 6px;
                border-top: 1px solid #333;
            }
            .right-ul.open { display: flex; }
            .right-ul > li { width: 100%; }
            .right-ul > li > a { display: block; padding: 10px 0; font-size: 16px; }
            .profile { margin-top: 6px; }
            .dropdown { position: static; margin-top: 10px; width: 100%; box-shadow: none; }
        }

        @media (max-width: 700px) {
            .grid-2 { grid-template-columns: 1fr; gap: 0; }
            .grid-2 .field { margin-bottom: 14px; }
            .methods { gap: 8px; }
            .method-box { padding: 14px 6px; font-size: 13px; gap: 6px; }
            .method-box i { font-size: 20px; }
            .page-title { font-size: 24px; margin-bottom: 18px; }
            .step { font-size: 13px; }
            .step-line { width: 22px; }
            .card { padding: 18px 16px; border-radius: 14px; }
            .cc-number { font-size: 18px; letter-spacing: 2px; }

            /* 16px thi nana font par iPhone auto-zoom kare chhe */
            .field input,
            .field textarea { font-size: 16px; }
        }

        @media (max-width: 400px) {
            .brand { font-size: 16px; }
            .brand img { width: 32px; height: 32px; }
            .methods { grid-template-columns: 1fr; }
            .method-box { flex-direction: row; justify-content: flex-start; padding: 12px 16px; gap: 12px; }
            .cc-preview { height: 180px; padding: 16px; }
            .cc-number { font-size: 16px; letter-spacing: 1px; }
            .cc-brand { font-size: 28px; }
            .sum-total span:last-child { font-size: 22px; }
        }
    </style>
</head>
<body>

    <header class="topbar">
        <a href="index.php" class="brand">
            <img src="Assets/logo.png" alt="logo">
            <span>B K's Burger House</span>
        </a>
        <button type="button" class="nav-toggle" id="navToggle" aria-label="Menu">☰</button>

        <ul class="right-ul" id="navMenu">
            <li><a href="index.php">Home</a></li>
            <li><a href="menu.php">Menu</a></li>
            <li><a href="cart.php" class="active">Cart</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li class="profile">
                <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" id="profileBtn" alt="Profile">
                <div class="dropdown" id="dropMenu">
                    <a href="account.php">Account</a>
                    <a href="wishlist.php">Wishlist</a>
                    <a href="logout.php">Logout</a>
                </div>
            </li>
        </ul>
    </header>

    <main class="page">

        <div class="steps">
            <div class="step done"><span><i class="fa-solid fa-check"></i></span> Cart</div>
            <div class="step-line"></div>
            <div class="step active"><span>2</span> Payment</div>
            <div class="step-line"></div>
            <div class="step"><span>3</span> Success</div>
        </div>

        <h1 class="page-title">Secure <em>Checkout</em></h1>

        <form method="post" action="" class="checkout-grid" id="checkoutForm">

            <!-- LEFT: form sections -->
            <div class="form-col">

                <?php if ($err): ?>
                    <div class="alert"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <section class="card">
                    <h2 class="card-title"><i class="fa-solid fa-user"></i> Contact & Delivery</h2>
                    <div class="grid-2">
                        <div class="field">
                            <label for="fullname">Full Name</label>
                            <input type="text" id="fullname" name="fullname" placeholder="Tony Stark"
                                   value="<?= htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8') ?>">
                            <?= fieldErr($fullname_err) ?>
                        </div>

                        <div class="field">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" placeholder="email@gmail.com"
                                   value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
                            <?= fieldErr($email_err) ?>
                        </div>
                    </div>

                    <div class="field" style="margin-top:16px;">
                        <label for="address">Delivery Address</label>
                        <textarea id="address" name="address" placeholder="Enter your delivery address"><?= htmlspecialchars($address, ENT_QUOTES, 'UTF-8') ?></textarea>
                        <?= fieldErr($address_err) ?>
                    </div>
                </section>

                <section class="card">
                    <h2 class="card-title"><i class="fa-solid fa-wallet"></i> Payment Method</h2>

                    <div class="methods">
                        <label class="method">
                            <input type="radio" name="payment_method" value="card" <?= $payment_method === 'card' ? 'checked' : '' ?>>
                            <span class="method-box">
                                <i class="fa-solid fa-credit-card"></i>
                                Card
                            </span>
                        </label>

                        <label class="method">
                            <input type="radio" name="payment_method" value="upi" <?= $payment_method === 'upi' ? 'checked' : '' ?>>
                            <span class="method-box">
                                <i class="fa-solid fa-mobile-screen"></i>
                                UPI
                            </span>
                        </label>

                        <label class="method">
                            <input type="radio" name="payment_method" value="cod" <?= $payment_method === 'cod' ? 'checked' : '' ?>>
                            <span class="method-box">
                                <i class="fa-solid fa-money-bill-wave"></i>
                                Cash on Delivery
                            </span>
                        </label>
                    </div>
                    <div style="margin-top:10px;"><?= fieldErr($paymentmethod_err) ?></div>
                </section>

                <!-- Card details: fakt "Card" select hoy tyare j dekhay -->
                <section class="card <?= $payment_method === 'card' ? '' : 'hidden' ?>" id="cardSection">
                    <h2 class="card-title"><i class="fa-solid fa-credit-card"></i> Card Details</h2>

                    <div class="cards-row">
                        <i class="fa-brands fa-cc-mastercard"></i>
                        <i class="fa-brands fa-cc-visa"></i>
                        <span>Accepted cards</span>
                    </div>

                    <div class="cc-preview">
                        <div class="cc-top">
                            <span class="cc-chip"></span>
                            <i class="fa-brands fa-cc-visa cc-brand" id="ccBrand"></i>
                        </div>
                        <div class="cc-number" id="ccNumView">•••• •••• •••• ••••</div>
                        <div class="cc-bottom">
                            <div>
                                <small>CARD HOLDER</small>
                                <div class="cc-name" id="ccNameView">YOUR NAME</div>
                            </div>
                            <div>
                                <small>EXPIRES</small>
                                <div class="cc-exp" id="ccExpView">MM/YY</div>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label for="cno">Card Number</label>
                        <input type="text" id="cno" name="cno" maxlength="16" inputmode="numeric"
                               placeholder="16-digit number" value="<?= htmlspecialchars($cno, ENT_QUOTES, 'UTF-8') ?>">
                        <?= fieldErr($cno_err) ?>
                    </div>

                    <div class="grid-2">
                        <div class="field">
                            <label for="expiry">Expiry (MM/YY)</label>
                            <input type="text" id="expiry" name="expiry" maxlength="5" inputmode="numeric"
                                   placeholder="MM/YY" value="<?= htmlspecialchars($expiry, ENT_QUOTES, 'UTF-8') ?>">
                            <?= fieldErr($expiry_err) ?>
                        </div>

                        <div class="field">
                            <label for="cvv">CVV</label>
                            <input type="password" id="cvv" name="cvv" maxlength="4" inputmode="numeric" placeholder="***">
                            <?= fieldErr($cvv_err) ?>
                        </div>
                    </div>
                </section>
            </div>

            <!-- RIGHT: order summary + actions -->
            <aside class="card summary">
                <h2 class="card-title"><i class="fa-solid fa-receipt"></i> Order Summary</h2>

                <?php if ($cartEmpty): ?>
                    <div class="empty-cart">Your cart is empty.</div>
                <?php else: ?>
                    <ul class="sum-items">
                        <?php for ($i = 0; $i < count($cartItemN); $i++): ?>
                            <li>
                                <div>
                                    <div class="sum-name"><?= htmlspecialchars($cartItemN[$i], ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="sum-qty">Qty: <?= (int)$cartItemQ[$i] ?></div>
                                </div>
                                <div class="sum-price">₹<?= number_format((float)$cartItemP[$i], 2) ?></div>
                            </li>
                        <?php endfor; ?>
                    </ul>
                <?php endif; ?>

                <div class="sum-row">
                    <span>Subtotal</span>
                    <span>₹<?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="sum-row discount">
                    <span>Discount (20%)</span>
                    <span>- ₹<?= number_format($discount, 2) ?></span>
                </div>
                <div class="sum-row">
                    <span>Tax (20%)</span>
                    <span>₹<?= number_format($tax, 2) ?></span>
                </div>

                <div class="sum-total">
                    <span>Total</span>
                    <span>₹<?= number_format($netAmount, 2) ?></span>
                </div>

                <button type="submit" class="btn-pay" <?= $cartEmpty ? 'disabled' : '' ?>>
                    <i class="fa-solid fa-lock"></i> Pay ₹<?= number_format($netAmount, 2) ?>
                </button>

                <a href="cart.php" class="btn-ghost">
                    <i class="fa-solid fa-arrow-left"></i> Back to Cart
                </a>

                <div class="secure-note">
                    <i class="fa-solid fa-shield-halved"></i>
                    Your payment details are kept safe
                </div>
            </aside>
        </form>

    </main>

    <script>
        // Profile dropdown + mobile hamburger
        document.addEventListener("DOMContentLoaded", function () {
            const btn = document.getElementById("profileBtn");
            const menu = document.getElementById("dropMenu");

            btn.addEventListener("click", function (e) {
                e.stopPropagation();
                menu.style.display = menu.style.display === "block" ? "none" : "block";
            });
            document.addEventListener("click", function () {
                menu.style.display = "none";
            });

            const navToggle = document.getElementById("navToggle");
            const navMenu = document.getElementById("navMenu");
            navToggle.addEventListener("click", function () {
                navMenu.classList.toggle("open");
            });
        });

        // Card section: fakt "Card" select hoy tyare j batave chhe
        const cardSection = document.getElementById("cardSection");
        const methodRadios = document.querySelectorAll('input[name="payment_method"]');

        function toggleCardSection() {
            const selected = document.querySelector('input[name="payment_method"]:checked');
            cardSection.classList.toggle("hidden", !(selected && selected.value === "card"));
        }

        methodRadios.forEach(r => r.addEventListener("change", toggleCardSection));
        toggleCardSection();

        // ---------- CARD PREVIEW ----------
        const cnoInput  = document.getElementById("cno");
        const expInput  = document.getElementById("expiry");
        const nameInput = document.getElementById("fullname");
        const cnoView   = document.getElementById("ccNumView");
        const expView   = document.getElementById("ccExpView");
        const nameView  = document.getElementById("ccNameView");
        const brandIcon = document.getElementById("ccBrand");

        function updateCardNumber() {
            const digits = cnoInput.value.replace(/\D/g, "").slice(0, 16);
            cnoInput.value = digits;

            // 16 digit ni jagyaye dots, 4-4-4-4 groups ma
            const shown = digits.padEnd(16, "•");
            cnoView.textContent = shown.match(/.{1,4}/g).join(" ");

            // Pehla digit thi brand nakki: 4 = Visa, 5 = Mastercard
            if (digits[0] === "4") {
                brandIcon.className = "fa-brands fa-cc-visa cc-brand";
            } else if (digits[0] === "5") {
                brandIcon.className = "fa-brands fa-cc-mastercard cc-brand";
            } else {
                brandIcon.className = "fa-solid fa-credit-card cc-brand";
            }
        }

        function updateExpiry() {
            const digits = expInput.value.replace(/\D/g, "").slice(0, 4);
            const formatted = digits.length > 2 ? digits.slice(0, 2) + "/" + digits.slice(2) : digits;
            expInput.value = formatted;
            expView.textContent = formatted || "MM/YY";
        }

        function updateName() {
            nameView.textContent = nameInput.value.trim().toUpperCase() || "YOUR NAME";
        }

        cnoInput.addEventListener("input", updateCardNumber);
        expInput.addEventListener("input", updateExpiry);
        nameInput.addEventListener("input", updateName);

        updateCardNumber();
        updateExpiry();
        updateName();
    </script>
</body>
</html>