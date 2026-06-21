<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/mail_config.php';

function format_money_vn($number) {
    return number_format((int)$number, 0, ',', '.') . ' đ';
}

function safe_mail_text($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function sendOrderSuccessMail($conn, $order_id) {
    $order_id = (int)$order_id;

    if ($order_id <= 0) {
        return false;
    }

    $sql_order = "SELECT * FROM orders WHERE id = ? LIMIT 1";
    $stmt_order = mysqli_prepare($conn, $sql_order);

    if (!$stmt_order) {
        return false;
    }

    mysqli_stmt_bind_param($stmt_order, "i", $order_id);
    mysqli_stmt_execute($stmt_order);

    $order_result = mysqli_stmt_get_result($stmt_order);
    $order = mysqli_fetch_assoc($order_result);

    if (!$order) {
        return false;
    }

    if (empty($order['email']) || !filter_var($order['email'], FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $sql_items = "SELECT * FROM order_items WHERE order_id = ?";
    $stmt_items = mysqli_prepare($conn, $sql_items);

    if (!$stmt_items) {
        return false;
    }

    mysqli_stmt_bind_param($stmt_items, "i", $order_id);
    mysqli_stmt_execute($stmt_items);

    $items_result = mysqli_stmt_get_result($stmt_items);

    $items_html = '';

    while ($item = mysqli_fetch_assoc($items_result)) {
        $product_name = safe_mail_text($item['product_name'] ?? '');
        $quantity = (int)($item['quantity'] ?? 0);
        $subtotal = (int)($item['subtotal'] ?? 0);

        $items_html .= '
            <tr>
                <td style="padding:12px;border-bottom:1px solid #e5e7eb;">
                    ' . $product_name . '
                </td>

                <td style="padding:12px;border-bottom:1px solid #e5e7eb;text-align:center;">
                    ' . $quantity . '
                </td>

                <td style="padding:12px;border-bottom:1px solid #e5e7eb;text-align:right;color:#ef4444;font-weight:700;">
                    ' . format_money_vn($subtotal) . '
                </td>
            </tr>
        ';
    }

    $customer_name = safe_mail_text($order['customer_name'] ?? '');
    $phone = safe_mail_text($order['phone'] ?? '');
    $email = safe_mail_text($order['email'] ?? '');
    $address = safe_mail_text($order['address'] ?? '');
    $note = safe_mail_text($order['note'] ?? '');
    $payment_method = safe_mail_text($order['payment_method'] ?? '');
    $total_amount = (int)($order['total_amount'] ?? 0);

    $subject = 'Xác nhận đơn hàng #' . $order_id . ' - ABA Mobile';

    $body = '
        <div style="font-family:Arial,sans-serif;background:#f8fafc;padding:24px;color:#0f172a;">
            <div style="max-width:680px;margin:0 auto;background:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #e5e7eb;">
                <div style="background:linear-gradient(90deg,#007bff,#00a8ff);padding:22px;color:#ffffff;">
                    <h2 style="margin:0;font-size:24px;">ABA Mobile</h2>
                    <p style="margin:6px 0 0;">Đặt hàng thành công</p>
                </div>

                <div style="padding:24px;">
                    <p>Xin chào <strong>' . $customer_name . '</strong>,</p>

                    <p>Cảm ơn bạn đã đặt hàng tại <strong>ABA Mobile</strong>. Đơn hàng của bạn đã được ghi nhận thành công.</p>

                    <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:14px;padding:16px;margin:18px 0;">
                        <p style="margin:6px 0;"><strong>Mã đơn hàng:</strong> #' . $order_id . '</p>
                        <p style="margin:6px 0;"><strong>Họ tên:</strong> ' . $customer_name . '</p>
                        <p style="margin:6px 0;"><strong>Số điện thoại:</strong> ' . $phone . '</p>
                        <p style="margin:6px 0;"><strong>Email:</strong> ' . $email . '</p>
                        <p style="margin:6px 0;"><strong>Địa chỉ:</strong> ' . $address . '</p>
                        <p style="margin:6px 0;"><strong>Thanh toán:</strong> ' . $payment_method . '</p>
                        <p style="margin:6px 0;"><strong>Ghi chú:</strong> ' . ($note !== '' ? $note : 'Không có') . '</p>
                    </div>

                    <h3 style="margin-top:24px;">Chi tiết đơn hàng</h3>

                    <table style="width:100%;border-collapse:collapse;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
                        <thead>
                            <tr style="background:#f8fafc;">
                                <th style="padding:12px;text-align:left;">Sản phẩm</th>
                                <th style="padding:12px;text-align:center;">SL</th>
                                <th style="padding:12px;text-align:right;">Thành tiền</th>
                            </tr>
                        </thead>

                        <tbody>
                            ' . $items_html . '
                        </tbody>
                    </table>

                    <h2 style="text-align:right;color:#ef4444;margin-top:20px;">
                        Tổng tiền: ' . format_money_vn($total_amount) . '
                    </h2>

                    <p>ABA Mobile sẽ liên hệ với bạn để xác nhận đơn hàng trong thời gian sớm nhất.</p>

                    <p style="margin-top:24px;">
                        Trân trọng,<br>
                        <strong>ABA Mobile</strong>
                    </p>
                </div>
            </div>
        </div>
    ';

    try {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = MAIL_PORT;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);
        $mail->addAddress($order['email'], $order['customer_name']);

        if (defined('SHOP_RECEIVE_EMAIL') && SHOP_RECEIVE_EMAIL !== '') {
            $mail->addBCC(SHOP_RECEIVE_EMAIL);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}
?>