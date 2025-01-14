    <!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">

            <title>User Dashboard</title>

            <!-- Fonts -->
            <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

            @vite(['resources/css/app.css', 'resources/js/app.js'])
        </head>
        <body class="antialiased">
            
            <div class="mainContainer">
                <!-- User Details Section -->
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="userDetails">
                    <div class="row d-flex justify-content-between align-items-center user-details">
                        <!-- User Icon -->
                        <div class="col-sm-2 col-md-2 user-card">
                            <div class="card dashboard-card ">
                                <div class="card-body d-flex align-items-center gap-3 justify-content-center">
                                    <h3 class="card-title"><i class="fas fa-user user-icon"></i></h3>
                                    <p class="card-text">{{ $authenticatedUser->name }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 mb-sm-3 btn-parent">
                            <!-- Logout Button -->
                            <div class="d-flex align-items-center justify-content-md-end ">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-2 userBalance">
                        <!-- Deposits -->
                        <div class="col-md-4">
                            <div class="card dashboard-card">
                                <div class="card-body">
                                    <h5 class="card-title">Deposits</h5>
                                    <p class="card-text">₹{{ number_format($user->deposit, 2) }}</p>
                                </div>
                            </div>
                        </div>
    
                        <!-- Withdrawals -->
                        <div class="col-md-4">
                            <div class="card dashboard-card">
                                <div class="card-body">
                                    <h5 class="card-title">Withdrawals</h5>
                                    <p class="card-text">₹{{ number_format($user->withdrawal, 2) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card balance-card">
                                <div class="card-body">
                                    <h5 class="card-title">Available Balance</h5>
                                    <p class="card-text">₹{{ number_format($user->total_balance, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Transaction Table Section -->
                {{-- <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Transaction Date</th>
                                <th>Transaction Type</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->id }}</td>
                                <td>₹ {{ $transaction->amount }}</td>
                                <td>{{ $transaction->status }}</td>
                                <td>{{ $transaction->created_at }}</td>
                                <td>{{ $transaction->transaction_type }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div> --}}
                <div class="buttons mt-3 mb-5">
                    <div class="column d-flex flex-row justify-content-center gap-3">
                        <div 
                            class="btn btn-primary" 
                            id="deposit-btn"  
                            data-bs-toggle="modal" 
                            data-bs-target="#depositModal">Deposit</div>
                        <div 
                            class="btn btn-secondary" 
                            id="withdrawal-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#withdrawalModal" 
                        >Withdrawal</div>
                    </div>
                </div>
                
                <div class="col mx-md-5">
                    <h2 class="fw-bold h2 mb-2">Transactions</h2>
                    @foreach($transactions as $transaction)
                    <div class="col-md-5 w-100 mb-2 transaction-col">
                        <div class="transaction-card p-2 d-flex justify-content-between align-items-center px-3">
                            <div class="d-flex gap-4 align-items-center">
                                <div class="fw-bold rounded-2 type  text-center h-100">
                                     @if($transaction->transaction_type === 'Deposit')
                                        <img height="50px" width="50px" src="{{asset('image/arrowDown-removebg-preview.png')}}" alt="Deposit Icon" style="filter: invert(35%) sepia(70%) saturate(500%) hue-rotate(100deg) brightness(100%) contrast(90%);">
                                    @elseif($transaction->transaction_type === 'Withdrawal')
                                        <img height="50px" width="50px" src="{{asset('image/arrowUp-removebg-preview.png')}}" alt="Withdrawal Icon" style="filter: invert(21%) sepia(94%) saturate(7468%) hue-rotate(0deg) brightness(92%) contrast(102%);">
                                    @endif
                                </div>
                                <div class="d-flex flex-column gap-0">
                                    <p class="fw-semibold pb-0 m-0 text-lg">₹ {{$transaction->amount}}</p>
                                    <p class="fw-light m-0 amount text-xs">{{\Carbon\Carbon::parse($transaction->created_at)->setTimezone('Asia/Kolkata')->format('d M Y h:i A')}}</p>
                                </div>
                            </div>
                            <div class="d-flex justify-content-center flex-column align-items-center">
                                <div style="color: 
                                    @if($transaction->status === 'Pending') orange 
                                    @elseif($transaction->status === 'Success') green 
                                    @elseif($transaction->status === 'Failure') red 
                                    @elseif($transaction->status === 'Initiated') blue 
                                    @endif;" class='fw-bold'>
                                    <h1 class="h4">{{$transaction->status}}</h1>
                                </div>
                                <div class="d-flex gap-4">
                                    <button class="help-btn" data-order-id="{{ $transaction->order_id }}">Raise Ticket</button>
                                    <button>Status</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-center ">
                    {{ $transactions->links('pagination::bootstrap-5') }} <!-- Pagination controls -->
                </div>

                
            </div>

            {{-- MODALS --}}

            <!-- Deposit Modal -->
            <div class="modal fade" id="depositModal" tabindex="-1" aria-labelledby="depositModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="depositModalLabel">Deposit</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="depositForm" method="GET" action="{{ route('account') }}">
                                @csrf

                                <!-- Hidden input to pass the user_id -->
                                <input type="hidden" name="user_id" value="{{ $user->user_id }}">
                                <div class="mb-3">
                                    <label for="depositAmount" class="form-label">Amount</label>

                                    <input type="number" class="form-control" id="depositAmount" placeholder="Enter deposit amount" name="deposit_amount" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Proceed</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Withdrawal Modal -->
            <div class="modal fade" id="withdrawalModal" tabindex="-1" aria-labelledby="withdrawalModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="withdrawalModalLabel">Withdrawal</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="{{ route('proceedWithdrawal') }}">
                                @csrf

                                <!-- Hidden input to pass the user_id -->
                                <input type="hidden" name="user_id" value="{{ $user->user_id }}">

                                <div class="mb-3">
                                    <label for="withdrawalAmount" class="form-label">Amount</label>
                                    <input type="number" class="form-control" id="withdrawalAmount" placeholder="Enter withdrawal amount" name="withdrawal_amount">
                                </div>
                                <button type="submit" class="btn btn-primary">Proceed</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Ticket Create Window Starts --}}
            <div class="ticket-window" id="ticketWindow">
                <div class="ticket-window-header">
                    Support Ticket
                </div>
                <div class="ticket-window-body">
                    <form method="POST" action="{{ route('raiseTicket') }}" id="ticketForm" enctype="multipart/form-data">
                        @csrf
                        <label for="order_id" class="mb-2 fw-bold">Order ID :</label>
                        <input type="text" id="order_id" name="order_id" readonly>
                        
                        <label for="issue_type" class="mb-2 mt-2 fw-bold">Select an Issue:</label>
                        <select id="issue_type" name="issue_type" class="p-1 rounded-lg">
                            <option value="">Select an Issue</option>
                            <option value="Payment Failed">Payment Failed</option>
                            <option value="Payment Not Reflected">Payment Not Reflected</option>
                            <option value="Transaction Pending">Transaction Pending</option>
                            <option value="Duplicate Payment">Duplicate Payment</option>
                            <option value="Other">Other</option>
                        </select>

                        <label for="query" class="mt-3 mb-2 fw-bold">Describe your issue:</label>

                        <textarea id="query" name="query" placeholder="Describe your issue..." cols="5" rows="3"></textarea>
            
                        <label for="screenshot" class="mt-2">Attach Screenshot (optional):</label>

                        <input type="file" id="ticket_image" name="ticket_image" class="ticket-input mt-2" accept="image/jpeg, image/png">
                        <span id="fileName"></span>
                        <input type="hidden" name="user_id" value={{$user->user_id}}>

                        <div class="ticket-window-footer d-flex justify-content-center gap-3">
                            <button type="submit" class="btn btn-primary" id="submitTicket">Submit</button>
                            <button type="button" class="btn btn-secondary" id="closeTicket">Close</button>
                        </div>
                    </form>
                </div>
               
            </div>
            {{-- Ticket Create Window Ends --}}

            {{-- Ticket Status Window Starts --}}
            <div class="ticket-status-window" id="ticketStatusWindow">
                <div class="ticket-status-header" id="ticketStatusHeader">
                    Ticket Status
                </div>

                <div class="ticket-status-body">
                    
                </div>
            </div>
            {{-- Ticket Status Window Ends --}}

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

            {{-- <script type="module" src="{{ asset('js/app.js') }}" defer></script> --}}
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // Get elements
                    const helpButtons = document.querySelectorAll('.help-btn');
                    const ticketWindow = document.getElementById('ticketWindow');
                    const orderIdField = document.getElementById('order_id');
                    const closeBtn = document.getElementById('closeTicket');
                    const submitBtn = document.getElementById('submitTicket');
                    
                    // Handle help button clicks
                    helpButtons.forEach(button => {
                        button.addEventListener('click', function () {
                            const orderId = this.getAttribute('data-order-id');
                            orderIdField.value = orderId; // Set the order_id field
                            ticketWindow.style.display = 'block'; // Show the ticket window
                        });
                    });
            
                    // Handle closing the ticket window
                    closeBtn.addEventListener('click', function () {
                        ticketWindow.style.display = 'none'; // Hide the ticket window
                    });
            
                    // // Handle ticket submission (you can replace this with an actual AJAX request)
                    // submitBtn.addEventListener('click', function () {
                    //     const query = document.getElementById('query').value;
                    //     if (query.trim()) {
                    //         // Submit the form via AJAX or redirect to the support route
                    //         console.log('Order ID:', orderIdField.value);
                    //         console.log('Query:', query);
                    //         // Close the window after submitting
                    //         ticketWindow.style.display = 'none';
                    //     } else {
                    //         alert('Please describe your issue.');
                    //     }
                    // });
                });
            </script>
        </body>
    </html>