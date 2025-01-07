<!-- resources/views/account.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Select Account</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mx-auto mt-5">
    <p>Your Deposit Amount is {{$deposit_amount}}</p>
    <h2>Select an Account</h2>
    <form action="{{ route('proceedDeposit') }}" method="POST">
        @csrf
        <!-- Hidden fields for deposit details -->
        <input type="hidden" name="transaction_id" value="{{ $transaction_id }}">
        <input type="hidden" name="deposit_amount" value="{{ $deposit_amount }}">
        <input type="hidden" name="user_id" value="{{ $user_id }}">
        <div class="card">
            <div class="card-body ">
                <!-- sLoop through multiple accounts dynamically -->
                @if(!empty($accounts))
                    @foreach($accounts as $account)
                        <div class="form-check mb-3">
                            <input class="form-check-input account-radio" type="radio" name="selected_account" id="account{{ $account['id'] }}" value="{{ $account['id'] }}" required>
                            <label class="form-check-label mx-3" for="account{{ $account['id'] }}">
                                <strong>Account: {{ $account['accountNo'] }}</strong> <br>
                                IFSC Code: {{ $account['ifscNo'] }} <br>
                                UPI ID: {{ $account['upiId'] }}
                                Order ID : {{ isset($account['orderId']) && $account['orderId'] ? $account['orderId'] : 'Not available' }}
                            </label>

                            <!-- UTR Input (Initially Hidden) -->
                            <div id="utrInput{{ $account['id'] }}" class="mt-2 col-4" style="display: none">
                                <label for="utrNo{{ $account['id'] }}" class="form-label">Enter UTR Number</label>
                                <input type="text" class="form-control" id="utrNo{{ $account['id'] }}" name="utr_no" placeholder="Enter UTR No.">
                            </div>
                        </div>
                    @endforeach
                @else
                    <p>No accounts available to display.</p>
                @endif
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
</div>

<!-- Include Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Add an event listener to all radio buttons
    document.querySelectorAll('.account-radio').forEach(function(radio) {
        radio.addEventListener('change', function() {
            // When the radio button is clicked
            const accountId = this.value; // Get the account ID of the selected radio button

            // Hide all UTR inputs
            document.querySelectorAll('[id^="utrInput"]').forEach(function(utrInput) {
                utrInput.style.display = 'none';  // Hide other UTR input fields
            }); 

            // Show the UTR input for the selected account
            const utrInputField = document.getElementById('utrInput' + accountId);
            if (utrInputField) {
                utrInputField.style.display = 'block'; // Display the UTR input
            }

            // Optionally send data to API (you can handle the API call here)
            const accountDetails = JSON.parse(this.getAttribute('data-account-details'));  // Get the complete account details from the data attribute
            const dataToSend = {
                accountDetails: accountDetails
            };

            // Send data via AJAX using Fetch API (Optional)
            fetch('/your-api-endpoint-url', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // Laravel's CSRF protection
                },
                body: JSON.stringify(dataToSend)
            })
            .then(response => response.json())
            .then(data => {
                console.log('API Response:', data);
                // You can update the page based on the response (if needed)
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
</script>
</body>
</html>
