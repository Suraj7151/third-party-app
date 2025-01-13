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
    <form action="{{ route('submitDeposit') }}" enctype="multipart/form-data" method="POST">
        @csrf
        <!-- Hidden fields for deposit details -->
        <input type="hidden" name="transaction_id" value="{{ $transaction_id }}">
        <input type="hidden" name="deposit_amount" value="{{ $deposit_amount }}">
        <input type="hidden" name="user_id" value="{{ $user_id }}">
        <input type="hidden" name="order_id" value="{{ $orderid }}">
        <div class="card">
            <div class="card-body ">
                <!-- Loop through multiple accounts dynamically -->
                @if(!empty($accounts))
                    @foreach($accounts as $account)
                        <div class="form-check mb-3">
                            <input class="form-check-input account-radio" type="radio" name="selected_account" id="account{{ $account['id'] }}" value="{{ $account['id'] }}" data-account-id="{{ $account['id'] }}"  required>
                            <label class="form-check-label mx-3" for="account{{ $account['id'] }}">
                                <strong>Account: {{ $account['accountNo'] }}</strong> <br>
                                IFSC Code: {{ $account['ifscNo'] }} <br>
                                UPI ID: {{ $account['upiId'] }}
                            </label>

                            <!-- UTR Input (Initially Hidden) -->
                            <div id="utrInput{{ $account['id'] }}" class="mt-2 col-4" style="display: none">
                                <label for="utrNo{{ $account['id'] }}" class="form-label">Enter UTR Number</label>
                                <input type="text" class="form-control" id="utrNo{{ $account['id'] }}" name="utr_no" placeholder="Enter UTR No." required>
                            </div>

                            <!-- Image Upload Input (Initially Hidden) -->
                            <div id="imageInput{{ $account['id'] }}" class="mt-2 col-4" style="display: none">
                                <label for="depositImage{{ $account['id'] }}" class="form-label">Upload Deposit Image</label>
                                <input type="file" class="form-control" id="depositImage{{ $account['id'] }}" name="deposit_image" accept="image/jpeg, image/png">
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
    document.addEventListener('DOMContentLoaded', function () {
       // Add an event listener to all radio buttons
        document.querySelectorAll('.account-radio').forEach(function(radio) {
            radio.addEventListener('change', function() {
                // When the radio button is clicked
                const accountId = this.value; // Get the account ID of the selected radio button
                const userId = '{{ $user_id }}'; // Get the user ID
                const depositAmount = '{{ $deposit_amount }}'; // Get the deposit amount
                const orderId = '{{ $orderid }}'; // Get the order ID
                console.log('Radio button clicked:', {
                    accountId: accountId,
                    userId: userId,
                    depositAmount: depositAmount,
                    orderId: orderId,
                });

                // Hide all UTR inputs
                document.querySelectorAll('[id^="utrInput"]').forEach(function(utrInput) {
                    utrInput.style.display = 'none';  // Hide other UTR input fields
                });

                // Show the UTR input for the selected account
                const utrInputField = document.getElementById('utrInput' + accountId);
                const imageInputField = document.getElementById('imageInput' + accountId);

                if (utrInputField) {
                    utrInputField.style.display = 'block'; // Display the UTR input
                }
                if (imageInputField) {
                    imageInputField.style.display = 'block'; // Display the image upload input
                }

                // Send data via AJAX using Fetch API
                console.log('Making payment API call...');
                fetch('{{ route("processPayment") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' // Laravel's CSRF protection
                    },
                    body: JSON.stringify({
                        account_id: accountId,
                        user_id: userId,
                        deposit_amount: depositAmount,
                        order_id: orderId
                    })
                })
                .then(response => {
                    console.log('Payment API response received:', response);
                    return response.json();
                })
                .then(data => {
                    console.log('Payment API response data:', data);

                    // Check if the payment initiation was successful
                    if (data.success) {
                        console.log('Payment API called successfully');
                    } 
                    else {
                        console.error('Error calling payment API:', data.message);
                        alert('Failed to initiate payment');
                    }
                })
                .catch(error => {
                    console.error('Error in payment API call:', error);
                    alert('Error calling payment API');
                });
            });
        });

        // Add event listener for submit button
        document.getElementById('submitPayment').addEventListener('click', function() {
            const accountId = document.querySelector('input[name="account"]:checked')?.value;
            if (!accountId) {
                alert('Please select an account');
                return;
            }

            const userId = '{{ $user_id }}'; // Get the user ID
            const depositAmount = '{{ $deposit_amount }}'; // Get the deposit amount
            const orderId = '{{ $orderid }}'; // Get the order ID
            const utrNo = document.querySelector('input[name="utr_no"]').value; // Get the UTR number
            const depositImage = document.querySelector('input[name="deposit_image"]').files[0]; // Get the image file

            if (!utrNo) {
                alert('Please enter the UTR number');
                return;
            }

            // Create a FormData object to handle file uploads
            const formData = new FormData();
            formData.append('transaction_id', '{{ $transaction_id }}');
            formData.append('user_id', userId);
            formData.append('amount', depositAmount);
            formData.append('order_id', orderId);
            formData.append('utr_no', utrNo);
            if (depositImage) {
                formData.append('deposit_image', depositImage); // Include the image file
            }


            // Send data via AJAX using Fetch API for completePayment
            fetch('{{ route("submitDeposit") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // Laravel's CSRF protection
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '{{ route("showDashboard", ["user_id" => $user_id]) }}'; // Adjust route as needed
                } else {
                    alert('Failed to complete payment');
                }
            })
            .catch(error => {
                console.error('Error completing payment:', error);
                alert('Error completing payment');
            });
        });
        document.getElementById('depositImage{{ $account['id'] }}').addEventListener('change', function() {
        var file = this.files[0]; // Get the selected file
        if (file) {
            var maxSize = 2 * 1024 * 1024; // 2 MB in bytes
            if (file.size > maxSize) {
                alert('The file size must be less than 2 MB.');
                this.value = ''; // Clear the input if file is too large
            }
        }
        });
    });
</script>


</body>
</html>
