@extends('layouts.app')

@section('title', '決済詳細')

@section('content')
<div style="margin-bottom: 2rem;">
    <a href="{{ route('payments.index') }}" class="btn btn-primary">← 一覧に戻る</a>
</div>

<div class="card">
    <h2 style="margin-bottom: 2rem;">決済詳細 #{{ $payment->id }}</h2>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        <div>
            <h3 style="margin-bottom: 1rem; color: #34495e;">決済情報</h3>
            <table style="box-shadow: none;">
                <tr>
                    <th style="background-color: #f8f9fa; color: #333; width: 40%;">ステータス</th>
                    <td>
                        @switch($payment->status)
                            @case('pending')
                                <span style="color: #f39c12; font-weight: bold;">保留中</span>
                                @break
                            @case('processing')
                                <span style="color: #3498db; font-weight: bold;">処理中</span>
                                @break
                            @case('completed')
                                <span style="color: #27ae60; font-weight: bold;">完了</span>
                                @break
                            @case('failed')
                                <span style="color: #e74c3c; font-weight: bold;">失敗</span>
                                @break
                            @case('refunded')
                                <span style="color: #95a5a6; font-weight: bold;">返金済み</span>
                                @break
                            @case('cancelled')
                                <span style="color: #7f8c8d; font-weight: bold;">キャンセル</span>
                                @break
                        @endswitch
                    </td>
                </tr>
                <tr>
                    <th style="background-color: #f8f9fa; color: #333;">金額</th>
                    <td style="font-size: 1.2rem; font-weight: bold;">¥{{ number_format($payment->amount) }}</td>
                </tr>
                <tr>
                    <th style="background-color: #f8f9fa; color: #333;">決済方法</th>
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
                        @endswitch
                    </td>
                </tr>
                <tr>
                    <th style="background-color: #f8f9fa; color: #333;">決済ゲートウェイ</th>
                    <td>{{ $payment->payment_gateway ?? '-' }}</td>
                </tr>
                <tr>
                    <th style="background-color: #f8f9fa; color: #333;">トランザクションID</th>
                    <td>{{ $payment->transaction_id ?? '-' }}</td>
                </tr>
                <tr>
                    <th style="background-color: #f8f9fa; color: #333;">決済日時</th>
                    <td>{{ $payment->paid_at ? $payment->paid_at->format('Y/m/d H:i') : '-' }}</td>
                </tr>
            </table>
        </div>

        <div>
            <h3 style="margin-bottom: 1rem; color: #34495e;">予約情報</h3>
            <table style="box-shadow: none;">
                <tr>
                    <th style="background-color: #f8f9fa; color: #333; width: 40%;">予約ID</th>
                    <td>
                        <a href="{{ route('reservations.show', $payment->reservation) }}" style="color: #3498db;">
                            #{{ $payment->reservation_id }}
                        </a>
                    </td>
                </tr>
                <tr>
                    <th style="background-color: #f8f9fa; color: #333;">顧客名</th>
                    <td>{{ $payment->reservation->customer->name }}</td>
                </tr>
                <tr>
                    <th style="background-color: #f8f9fa; color: #333;">部屋</th>
                    <td>{{ $payment->reservation->room->room_number }}</td>
                </tr>
                <tr>
                    <th style="background-color: #f8f9fa; color: #333;">チェックイン</th>
                    <td>{{ $payment->reservation->check_in_date->format('Y/m/d') }}</td>
                </tr>
                <tr>
                    <th style="background-color: #f8f9fa; color: #333;">チェックアウト</th>
                    <td>{{ $payment->reservation->check_out_date->format('Y/m/d') }}</td>
                </tr>
            </table>
        </div>
    </div>

    @if($payment->status === 'refunded')
        <div style="background-color: #f8f9fa; padding: 1.5rem; border-radius: 4px; margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1rem; color: #34495e;">返金情報</h3>
            <table style="box-shadow: none;">
                <tr>
                    <th style="background-color: #fff; color: #333; width: 20%;">返金額</th>
                    <td style="background-color: #fff; font-weight: bold;">¥{{ number_format($payment->refund_amount) }}</td>
                </tr>
                <tr>
                    <th style="background-color: #fff; color: #333;">返金日時</th>
                    <td style="background-color: #fff;">{{ $payment->refunded_at->format('Y/m/d H:i') }}</td>
                </tr>
                <tr>
                    <th style="background-color: #fff; color: #333;">返金理由</th>
                    <td style="background-color: #fff;">{{ $payment->refund_reason ?? '-' }}</td>
                </tr>
            </table>
        </div>
    @endif

    @if($payment->status === 'failed')
        <div style="background-color: #fee; padding: 1.5rem; border-radius: 4px; border: 1px solid #e74c3c; margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1rem; color: #e74c3c;">エラー情報</h3>
            <p><strong>失敗理由:</strong> {{ $payment->failure_reason }}</p>
        </div>
    @endif

    @if($payment->notes)
        <div style="margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1rem; color: #34495e;">備考</h3>
            <p style="background-color: #f8f9fa; padding: 1rem; border-radius: 4px;">{{ $payment->notes }}</p>
        </div>
    @endif

    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
        @if($payment->status === 'pending' || $payment->status === 'processing')
            <form method="POST" action="{{ route('payments.process', $payment) }}">
                @csrf
                <button type="submit" class="btn btn-success">決済を処理</button>
            </form>
            <form method="POST" action="{{ route('payments.cancel', $payment) }}">
                @csrf
                <button type="submit" class="btn btn-danger" onclick="return confirm('本当にキャンセルしますか？')">キャンセル</button>
            </form>
        @endif

        @if($payment->status === 'completed')
            <button type="button" class="btn btn-danger" onclick="document.getElementById('refundForm').style.display='block'">返金</button>
        @endif
    </div>

    @if($payment->status === 'completed')
        <div id="refundForm" style="display: none; margin-top: 2rem; background-color: #f8f9fa; padding: 1.5rem; border-radius: 4px;">
            <h3 style="margin-bottom: 1rem;">返金処理</h3>
            <form method="POST" action="{{ route('payments.refund', $payment) }}">
                @csrf
                <div class="form-group">
                    <label>返金額（空欄の場合は全額返金）</label>
                    <input type="number" name="amount" step="0.01" max="{{ $payment->amount }}" placeholder="¥{{ number_format($payment->amount) }}">
                </div>
                <div class="form-group">
                    <label>返金理由</label>
                    <textarea name="reason" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-danger" onclick="return confirm('返金処理を実行しますか？')">返金を実行</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('refundForm').style.display='none'">キャンセル</button>
            </form>
        </div>
    @endif
</div>
@endsection
