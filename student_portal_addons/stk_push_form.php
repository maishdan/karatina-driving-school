<!DOCTYPE html>
<html>
<head>
    <title>MPesa STK Push</title>
</head>
<body>
    <h2>Pay with MPesa</h2>
    <form method="POST" action="stk_push.php">
        <label>Phone Number (format 2547XXXXXXXX):</label><br>
        <input type="text" name="phone" required><br><br>
        <label>Amount (KES):</label><br>
        <input type="number" name="amount" required><br><br>
        <button type="submit">Pay Now</button>
    </form>
</body>
</html>
