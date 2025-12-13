@extends('layout')

@section('title', 'Payment History - TARUMT Car Park')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-history me-2"></i>Payment History</h2>
    <a href="{{ route('credits.index') }}" class="btn btn-success">
        <i class="fas fa-plus me-1"></i>Top Up Credits
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        {{-- Credit Transactions --}}
        <div class="card shadow mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Credit Transactions</h5>
            </div>
            <div class="card-body p-0">
                @if($transactions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                            <tr>
                                <td>
                                    <small>{{ $transaction->created_at->format('d M Y') }}</small><br>
                                    <small class="text-muted">{{ $transaction->created_at->format('h:i A') }}</small>
                                </td>
                                <td>{{ $transaction->description }}</td>
                                <td class="text-end">
                                    <span class="badge bg-{{ $transaction->type === 'credit' ? 'success' : 'danger' }}">
                                        {{ $transaction->type === 'credit' ? '+' : '-' }}RM {{ number_format($transaction->amount, 2) }}
                                    </span>
                                </td>
                                <td class="text-end text-muted">RM {{ number_format($transaction->balance_after, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $transactions->links() }}
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No transactions yet</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Payment Records --}}
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Records</h5>
            </div>
            <div class="card-body p-0">
                @if($payments->count() > 0)
                <ul class="list-group list-group-flush">
                    @foreach($payments as $payment)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>RM {{ number_format($payment->amount, 2) }}</strong>
                                <br>
                                <small class="text-muted">{{ $payment->created_at->format('d M Y, h:i A') }}</small>
                            </div>
                            <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'failed' ? 'danger' : 'warning') }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </div>
                        @if($payment->status === 'completed')
                        <small class="text-success">+{{ number_format($payment->credits_purchased, 2) }} credits</small>
                        @endif
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-credit-card fa-2x text-muted mb-2"></i>
                    <p class="text-muted small mb-0">No payments yet</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Current Balance --}}
        <div class="card mt-3">
            <div class="card-body text-center">
                <small class="text-muted">Current Balance</small>
                <h3 class="text-success mb-0">RM {{ number_format(auth()->user()->credit_balance, 2) }}</h3>
            </div>
        </div>
    </div>
</div>
@endsection
