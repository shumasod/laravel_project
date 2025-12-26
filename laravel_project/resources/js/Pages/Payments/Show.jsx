import Layout from '@/Components/Layout';
import { Link, useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function Show({ payment }) {
    const [showRefundForm, setShowRefundForm] = useState(false);
    const { data, setData, post, processing } = useForm({
        amount: '',
        reason: '',
    });

    const handleRefund = (e) => {
        e.preventDefault();
        post(`/payments/${payment.id}/refund`, {
            onSuccess: () => setShowRefundForm(false),
        });
    };

    const getStatusColor = (status) => {
        const colors = {
            pending: 'text-yellow-600 bg-yellow-50',
            processing: 'text-blue-600 bg-blue-50',
            completed: 'text-green-600 bg-green-50',
            failed: 'text-red-600 bg-red-50',
            refunded: 'text-gray-600 bg-gray-50',
            cancelled: 'text-gray-500 bg-gray-50',
        };
        return colors[status] || 'text-gray-600 bg-gray-50';
    };

    const getStatusLabel = (status) => {
        const labels = {
            pending: '保留中',
            processing: '処理中',
            completed: '完了',
            failed: '失敗',
            refunded: '返金済み',
            cancelled: 'キャンセル',
        };
        return labels[status] || status;
    };

    return (
        <Layout>
            <div className="space-y-6">
                <div>
                    <Link href="/payments" className="text-blue-600 hover:text-blue-800 font-medium">
                        ← 一覧に戻る
                    </Link>
                </div>

                <div className="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div className="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                        <h1 className="text-2xl font-bold text-white">決済詳細 #{payment.id}</h1>
                    </div>

                    <div className="p-6 space-y-6">
                        <div className="grid grid-cols-2 gap-6">
                            <div className="space-y-4">
                                <h2 className="text-lg font-semibold text-gray-900 border-b pb-2">決済情報</h2>
                                <div className="space-y-3">
                                    <div className="flex items-center justify-between py-2 border-b">
                                        <span className="text-gray-600">ステータス</span>
                                        <span className={`px-4 py-1 rounded-full font-semibold ${getStatusColor(payment.status)}`}>
                                            {getStatusLabel(payment.status)}
                                        </span>
                                    </div>
                                    <div className="flex items-center justify-between py-2 border-b">
                                        <span className="text-gray-600">金額</span>
                                        <span className="text-2xl font-bold text-green-600">
                                            ¥{Number(payment.amount).toLocaleString()}
                                        </span>
                                    </div>
                                    <div className="flex items-center justify-between py-2 border-b">
                                        <span className="text-gray-600">決済方法</span>
                                        <span className="font-medium">{payment.payment_method}</span>
                                    </div>
                                    <div className="flex items-center justify-between py-2 border-b">
                                        <span className="text-gray-600">決済ゲートウェイ</span>
                                        <span className="font-medium">{payment.payment_gateway || '-'}</span>
                                    </div>
                                    <div className="flex items-center justify-between py-2 border-b">
                                        <span className="text-gray-600">トランザクションID</span>
                                        <span className="font-mono text-sm">{payment.transaction_id || '-'}</span>
                                    </div>
                                    <div className="flex items-center justify-between py-2">
                                        <span className="text-gray-600">決済日時</span>
                                        <span>{payment.paid_at || '-'}</span>
                                    </div>
                                </div>
                            </div>

                            <div className="space-y-4">
                                <h2 className="text-lg font-semibold text-gray-900 border-b pb-2">予約情報</h2>
                                <div className="space-y-3">
                                    <div className="flex items-center justify-between py-2 border-b">
                                        <span className="text-gray-600">予約ID</span>
                                        <Link
                                            href={`/reservations/${payment.reservation.id}`}
                                            className="text-blue-600 hover:text-blue-800 font-medium"
                                        >
                                            #{payment.reservation.id}
                                        </Link>
                                    </div>
                                    <div className="flex items-center justify-between py-2 border-b">
                                        <span className="text-gray-600">顧客名</span>
                                        <span className="font-medium">{payment.reservation.customer.name}</span>
                                    </div>
                                    <div className="flex items-center justify-between py-2 border-b">
                                        <span className="text-gray-600">部屋</span>
                                        <span>{payment.reservation.room.room_number}</span>
                                    </div>
                                    <div className="flex items-center justify-between py-2 border-b">
                                        <span className="text-gray-600">チェックイン</span>
                                        <span>{payment.reservation.check_in_date}</span>
                                    </div>
                                    <div className="flex items-center justify-between py-2">
                                        <span className="text-gray-600">チェックアウト</span>
                                        <span>{payment.reservation.check_out_date}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {payment.status === 'refunded' && (
                            <div className="bg-gray-50 rounded-lg p-4 border-l-4 border-gray-400">
                                <h3 className="font-semibold text-gray-900 mb-3">返金情報</h3>
                                <div className="space-y-2 text-sm">
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">返金額</span>
                                        <span className="font-bold">¥{Number(payment.refund_amount).toLocaleString()}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">返金日時</span>
                                        <span>{payment.refunded_at}</span>
                                    </div>
                                    {payment.refund_reason && (
                                        <div>
                                            <span className="text-gray-600">返金理由</span>
                                            <p className="mt-1">{payment.refund_reason}</p>
                                        </div>
                                    )}
                                </div>
                            </div>
                        )}

                        <div className="flex gap-3 pt-4">
                            {(payment.status === 'pending' || payment.status === 'processing') && (
                                <>
                                    <Link
                                        href={`/payments/${payment.id}/process`}
                                        method="post"
                                        as="button"
                                        className="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition"
                                    >
                                        決済を処理
                                    </Link>
                                    <Link
                                        href={`/payments/${payment.id}/cancel`}
                                        method="post"
                                        as="button"
                                        className="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition"
                                    >
                                        キャンセル
                                    </Link>
                                </>
                            )}
                            {payment.status === 'completed' && (
                                <button
                                    onClick={() => setShowRefundForm(!showRefundForm)}
                                    className="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2 rounded-lg font-medium transition"
                                >
                                    返金
                                </button>
                            )}
                        </div>

                        {showRefundForm && payment.status === 'completed' && (
                            <div className="bg-yellow-50 rounded-lg p-6 border border-yellow-200">
                                <h3 className="font-semibold text-gray-900 mb-4">返金処理</h3>
                                <form onSubmit={handleRefund} className="space-y-4">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            返金額（空欄の場合は全額返金）
                                        </label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            max={payment.amount}
                                            value={data.amount}
                                            onChange={(e) => setData('amount', e.target.value)}
                                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                            placeholder={`¥${Number(payment.amount).toLocaleString()}`}
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">
                                            返金理由
                                        </label>
                                        <textarea
                                            value={data.reason}
                                            onChange={(e) => setData('reason', e.target.value)}
                                            rows="3"
                                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                                        />
                                    </div>
                                    <div className="flex gap-3">
                                        <button
                                            type="submit"
                                            disabled={processing}
                                            className="bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white px-6 py-2 rounded-lg font-medium transition"
                                        >
                                            {processing ? '処理中...' : '返金を実行'}
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => setShowRefundForm(false)}
                                            className="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium transition"
                                        >
                                            キャンセル
                                        </button>
                                    </div>
                                </form>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </Layout>
    );
}
