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

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                font-family: 'Nunito', sans-serif;
                background-color: #f4f7fc;
                margin: 0;
                padding: 0;
            }

            .container {
                margin-top: 40px;
            }

            .dashboard-card {
                margin-bottom: 10px;
                border-radius: 15px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }

            .user-icon {
                font-size: 40px;
                color: #007bff;
            }

            .card-body {
                text-align: center;
                padding: 10px ;
            }

            .card-title {
                font-size: 1.5rem;
                font-weight: 600;
            }

            .card-text {
                font-size: 1.25rem;
                font-weight: 500;
            }

            .balance-card {
                background-color: #007bff;
                color: white;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                border-radius: 15px;
            }


            .balance-card p {
                font-size: 1.25rem;
                font-weight: 500;
            }
            
            .transaction-card{
                height: fit-content;
                /* border: 1px solid black; */
                box-shadow: 0 4px 8px rgba(50, 50, 50, 0.1); 
                border-radius: 8px;
            }
            .type{
                padding: 5px !important;
            }
            .amount{
                color: #4F7A94;
            }
            .table-responsive {
                margin-top: 20px;
            }

            .table {
                background-color: white;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                overflow: hidden;
            }

            .table th, .table td {
                padding: 8px 5px;
                text-align: center;
            }

            .table thead {
                background-color: #007bff;
                color: white;
            }

            .table-striped tbody tr:nth-of-type(odd) {
                background-color: #f9f9f9;
            }

            @media (max-width: 768px) {
                .user-icon {
                    font-size: 30px;
                }

                .card-title {
                    font-size: 1.25rem;
                }
                .col{
                    
                }
                .card-text {
                    font-size: 1rem;
                }

                .balance-card h5 {
                    font-size: 1.5rem;
                }

                .balance-card p {
                    font-size: 1.25rem;
                }
                .transaction-card {
                    /* align-items: flex-end !important; Align items to the left */
                    /* gap: 10px; Add space between stacked items */
                    padding: 10px; 
                }

                .transaction-card img {
                    margin-bottom: 5px; /* Add space below the image */
                }

                .transaction-col {
                    width: 100% !important; /* Take full width on smaller screens */
                }

                /* .transaction-card .d-flex {
                    flex-direction: row;
                } */
                .table {
                font-size: 0.9rem;
                }

                .table th, .table td {
                    padding: 0.5rem;
                }

                /* You can adjust the font size and padding for smaller screens */
                .user-info, .transaction-info {
                    font-size: 0.9rem;
                }
                
            }
        </style>
    </head>
    <body class="antialiased">
        
        <div class="container">
            <!-- User Details Section -->
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            <div class="row">
                <!-- User Icon -->
                <div class="col-md-2">
                    <div class="card dashboard-card">
                        <div class="card-body d-flex align-items-center gap-3 justify-content-center">
                            <h3 class="card-title"><i class="fas fa-user user-icon"></i></h3>
                            <p class="card-text">User: {{ $user->user_id }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row -mb-2">
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
            <h2>Transactions</h2>
            <div class="col mx-md-5">
                @foreach($transactions as $transaction)
                <div class="col-md-5 w-100 mb-2 transaction-col">
                    <div class="">
                            <div class="transaction-card p-2 d-flex justify-content-between align-items-center px-3">
                                <div class="d-flex gap-4 align-items-center">
                                    <div class="fw-bold rounded-2 type  text-center h-100">
                                        @if($transaction->transaction_type === 'Deposit')
                                            <img height="50px" width="50px" src="{{asset('image/deposit.png')}}" alt="Deposit Icon" style="filter: invert(35%) sepia(70%) saturate(500%) hue-rotate(100deg) brightness(100%) contrast(90%);">
                                        @elseif($transaction->transaction_type === 'Withdrawal')
                                            <img height="50px" width="50px" src="{{asset('image/withdrawal.png')}}" alt="Withdrawal Icon" style="filter: invert(21%) sepia(94%) saturate(7468%) hue-rotate(0deg) brightness(92%) contrast(102%);">
                                        @endif
                                    </div>
                                    <div class="d-flex flex-column gap-0">
                                        <p class="fw-semibold pb-0 m-0  ">₹ {{$transaction->amount}}</p>
                                        <p class="fw-light m-0 amount">{{\Carbon\Carbon::parse($transaction->created_at)->setTimezone('Asia/Kolkata')->format('d M Y h:i A')}}</p>
                                    </div>
                                </div>
                                <div style="color: 
                                @if($transaction->status === 'Pending') orange 
                                @elseif($transaction->status === 'Success') green 
                                @elseif($transaction->status === 'Failure') red 
                                @elseif($transaction->status === 'Initiated') blue 
                                @endif;" class='fw-bold'>
                                    {{$transaction->status}}
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
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

        {{-- <script type="module" src="{{ asset('js/app.js') }}" defer></script> --}}
    </body>
</html>
