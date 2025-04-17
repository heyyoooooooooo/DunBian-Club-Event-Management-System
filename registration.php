<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Registration</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom Google Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

        /* Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .registration-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            transition: all 0.3s ease;
        }

        /* Left Side - Registration Form */
        .registration-form {
            background: linear-gradient(to bottom right, #6a11cb 0%, #2575fc 100%);
            padding: 40px;
            color: white;
        }

        .registration-form h2 {
            text-align: center;
            color: white;
            margin-bottom: 30px;
            font-weight: 700;
            position: relative;
        }

        .registration-form h2::after {
            content: '';
            position: absolute;
            width: 100px;
            height: 4px;
            background: white;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            opacity: 0.8;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid rgba(255,255,255,0.2);
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            color: white;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: white;
            box-shadow: 0 0 10px rgba(255,255,255,0.3);
        }

        .radio-group {
            display: flex;
            justify-content: space-between;
        }

        .radio-option {
            display: flex;
            align-items: center;
            background: rgba(255,255,255,0.1);
            padding: 10px 15px;
            border-radius: 10px;
            width: 30%;
            transition: all 0.3s ease;
        }

        .radio-option input {
            margin-right: 10px;
        }

        .radio-option:hover {
            background: rgba(255,255,255,0.2);
        }

        .register-btn {
            width: 100%;
            padding: 15px;
            background: white;
            color: #2575fc;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .register-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        /* Right Side - Summary */
        .registration-summary {
            background: #f4f7f6;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .summary-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #f1f1f1;
        }

        .summary-item:last-child {
            border-bottom: none;
            font-weight: bold;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .registration-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <!-- Left Side: Registration Form -->
        <div class="registration-form">
            <h2>Event Registration</h2>
            
            <form id="registrationForm">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" class="form-control" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" class="form-control" placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" class="form-control" placeholder="Enter your phone number" required>
                </div>

                <div class="form-group">
                    <label>Select Ticket Type</label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="ticket" value="standard">
                            Standard
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="ticket" value="premium">
                            Premium
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="ticket" value="vip">
                            VIP
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Coupon Code (Optional)</label>
                    <input type="text" class="form-control" placeholder="Enter coupon code">
                </div>

                <button type="submit" class="register-btn">
                    Register Now
                </button>
            </form>
        </div>

        <!-- Right Side: Registration Summary -->
        <div class="registration-summary">
            <div class="summary-card">
                <h3 style="text-align: center; margin-bottom: 20px; color: #2575fc;">
                    Registration Summary
                </h3>
                
                <div id="summaryContent">
                    <div class="summary-item">
                        <span>Ticket Type</span>
                        <span>-</span>
                    </div>
                    <div class="summary-item">
                        <span>Base Price</span>
                        <span>-</span>
                    </div>
                    <div class="summary-item">
                        <span>Discount</span>
                        <span>-</span>
                    </div>
                    <div class="summary-item">
                        <strong>Total Price</strong>
                        <strong>-</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // JavaScript for form functionality would go here
        // (You can use the previous script with minor adjustments to match this new structure)
    </script>
</body>
</html>