@extends('layouts.app')

@section('title', '決済一覧')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>決済一覧</h2>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>予約ID</th>
                <th>顧客名</th>
                <th>金額</th>
                <th>決済方法</th>
                <th>ステータス</th>
                <th>決済日時</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td>{{ $payment->id }}</td>
                    <td>
                        <a href="{{ route('reservations.show', $payment->reservation_id) }}" style="color: #3498db;">
                            #{{ $payment->reservation_id }}
                        </a>
                    </td>
                    <td>{{ $payment->reservation->customer->name }}</td>
                    <td>¥{{ number_format($payment->amount) }}</td>
                    <td>
                        @switch($payment->payment_method)
                            @case('credit_card')
                                クレジットカード
                                @break
                            @case('bank_transfer')
                                銀行振込
                                @break
                            @case('cash')
                                現金
                                @break
                            @case('digital_wallet')
                                デジタルウォレット
                                @break
                            @default
                                {{ $payment->payment_method }}
                        @endswitch
                    </td>
                    <td>
                        @switch($payment->status)
                            @case('pending')
                                <span style="color: #f39c12;">保留中</span>
                                @break
                            @case('processing')
                                <span style="color: #3498db;">処理中</span>
                                @break
                            @case('completed')
                                <span style="color: #27ae60;">完了</span>
                                @break
                            @case('failed')
                                <span style="color: #e74c3c;">失敗</span>
                                @break
                            @case('refunded')
                                <span style="color: #95a5a6;">返金済み</span>
                                @break
                            @case('cancelled')
                                <span style="color: #7f8c8d;">キャンセル</span>
                                @break
                        @endswitch
                    </td>
                    <td>{{ $payment->paid_at ? $payment->paid_at->format('Y/m/d H:i') : '-' }}</td>
                    <td>
                        <a href="{{ route('payments.show', $payment) }}" class="btn btn-primary" style="font-size: 0.9rem; padding: 0.3rem 0.8rem;">詳細</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 2rem;">決済データがありません</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($payments->hasPages())
        <div style="margin-top: 2rem;">
            {{ $payments->links() }}
        </div>
    @endif
</div>
@endsection
