<?php
require_once '../includes/config.php';
require_once '../includes/resident_auth.php';

$page_title = 'Pay Bill';
$bill_id = (int)$_GET['id'];
$resident_id = $_SESSION['user_id'];

// Get bill details
$stmt = $conn->prepare("SELECT * FROM bills WHERE id = ? AND resident_id = ?");
$stmt->bind_param('ii', $bill_id, $resident_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Bill not found";
    redirect('/resident/bills.php');
}

$bill = $result->fetch_assoc();
$stmt->close();

// Calculate late fee if applicable
$late_fee = 0;
if ($bill['status'] == 'pending' && strtotime($bill['due_date']) < time()) {
    $days_late = floor((time() - strtotime($bill['due_date'])) / (60 * 60 * 24));
    $late_fee = $bill['amount'] * 0.02 * ceil($days_late / 30); // 2% per month
    $late_fee = min($late_fee, $bill['amount'] * 0.1); // Max 10%
}

$total_amount = $bill['amount'] + $late_fee;

// Process payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = sanitize($_POST['payment_method']);
    $transaction_id = 'TXN' . time() . rand(100, 999);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update bill
        $update_bill = $conn->prepare("UPDATE bills SET status = 'paid', payment_date = NOW(), late_fee = ? WHERE id = ?");
        $update_bill->bind_param('di', $late_fee, $bill_id);
        $update_bill->execute();
        $update_bill->close();
        
        // Insert payment record
        $insert_payment = $conn->prepare("INSERT INTO payments (bill_id, resident_id, amount, payment_method, transaction_id, payment_status) VALUES (?, ?, ?, ?, ?, 'success')");
        $insert_payment->bind_param('iidss', $bill_id, $resident_id, $total_amount, $payment_method, $transaction_id);
        $insert_payment->execute();
        $insert_payment->close();
        
        $conn->commit();
        
        $_SESSION['success'] = "Payment successful! Transaction ID: " . $transaction_id;
        redirect('/resident/bills.php');
        
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Payment failed: " . $e->getMessage();
    }
}

include '../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Pay Bill</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <!-- Bill Summary -->
                    <div class="bg-light p-4 rounded mb-4">
                        <h5>Bill Summary</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td>Bill Number:</td>
                                <td><strong><?php echo $bill['bill_number']; ?></strong></td>
                            </tr>
                            <tr>
                                <td>Bill Type:</td>
                                <td><?php echo ucfirst($bill['bill_type']); ?></td>
                            </tr>
                            <tr>
                                <td>Period:</td>
                                <td><?php echo date('F Y', mktime(0,0,0,$bill['month'],1,$bill['year'])); ?></td>
                            </tr>
                            <tr>
                                <td>Due Date:</td>
                                <td><?php echo date('d M Y', strtotime($bill['due_date'])); ?></td>
                            </tr>
                            <tr>
                                <td>Original Amount:</td>
                                <td>₹<?php echo number_format($bill['amount'], 2); ?></td>
                            </tr>
                            <?php if($late_fee > 0): ?>
                            <tr>
                                <td>Late Fee (2% per month):</td>
                                <td class="text-danger">+ ₹<?php echo number_format($late_fee, 2); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr class="border-top">
                                <td><strong>Total Amount:</strong></td>
                                <td><strong class="text-primary">₹<?php echo number_format($total_amount, 2); ?></strong></td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Payment Form -->
                    <form method="POST" id="paymentForm">
                        <div class="mb-4">
                            <label class="form-label">Select Payment Method</label>
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" value="card" id="card" required>
                                        <label class="form-check-label" for="card">
                                            <i class="fas fa-credit-card"></i> Credit/Debit Card
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" value="bank_transfer" id="bank">
                                        <label class="form-check-label" for="bank">
                                            <i class="fas fa-university"></i> Bank Transfer
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" value="cash" id="cash">
                                        <label class="form-check-label" for="cash">
                                            <i class="fas fa-money-bill"></i> Cash
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Card Details (shown only if card selected) -->
                        <div id="cardDetails" style="display: none;">
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Card Number</label>
                                    <input type="text" class="form-control" placeholder="1234 5678 9012 3456" id="card_number">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">CVV</label>
                                    <input type="text" class="form-control" placeholder="123" id="cvv">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Expiry Month</label>
                                    <select class="form-select" id="exp_month">
                                        <?php for($m=1; $m<=12; $m++): ?>
                                            <option value="<?php echo str_pad($m,2,'0',STR_PAD_LEFT); ?>"><?php echo str_pad($m,2,'0',STR_PAD_LEFT); ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Expiry Year</label>
                                    <select class="form-select" id="exp_year">
                                        <?php for($y=date('Y'); $y<=date('Y')+10; $y++): ?>
                                            <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Bank Transfer Details -->
                        <div id="bankDetails" style="display: none;" class="alert alert-info">
                            <h6>Bank Transfer Details:</h6>
                            <p class="mb-1">Bank: XYZ Bank</p>
                            <p class="mb-1">Account Name: Society Management</p>
                            <p class="mb-1">Account Number: 1234567890</p>
                            <p class="mb-1">IFSC Code: XYZB123456</p>
                            <hr>
                            <small>After transfer, please upload the receipt below</small>
                            <input type="file" class="form-control mt-2" id="receipt" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                        
                        <!-- Cash Payment Instructions -->
                        <div id="cashDetails" style="display: none;" class="alert alert-warning">
                            <i class="fas fa-info-circle"></i> Please pay the cash at the society office during working hours (9 AM - 6 PM).
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-lock"></i> Pay ₹<?php echo number_format($total_amount, 2); ?>
                            </button>
                            <a href="bills.php" class="btn btn-secondary btn-lg">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show/hide payment details based on selection
document.querySelectorAll('input[name="payment_method"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.getElementById('cardDetails').style.display = 'none';
        document.getElementById('bankDetails').style.display = 'none';
        document.getElementById('cashDetails').style.display = 'none';
        
        if (this.value === 'card') {
            document.getElementById('cardDetails').style.display = 'block';
        } else if (this.value === 'bank_transfer') {
            document.getElementById('bankDetails').style.display = 'block';
        } else if (this.value === 'cash') {
            document.getElementById('cashDetails').style.display = 'block';
        }
    });
});

// Format card number
document.getElementById('card_number').addEventListener('input', function(e) {
    var value = this.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    var matches = value.match(/\d{4,16}/g);
    var match = matches && matches[0] || '';
    var parts = [];
    
    for (i = 0; i < match.length; i += 4) {
        parts.push(match.substring(i, i + 4));
    }
    
    if (parts.length) {
        this.value = parts.join(' ');
    } else {
        this.value = value;
    }
});
</script>

<?php include '../includes/footer.php'; ?>