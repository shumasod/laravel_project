import Layout from '@/Components/Layout';
import { Link } from '@inertiajs/react';

export default function Dashboard({ report, accommodationId }) {
    const StatCard = ({ title, value, subtitle, color = 'blue', icon }) => (
        <div className={`bg-gradient-to-br from-${color}-50 to-${color}-100 rounded-lg shadow-md p-6 border-l-4 border-${color}-500`}>
            <div className="flex items-center justify-between">
                <div>
                    <p className="text-sm font-medium text-gray-600 mb-1">{title}</p>
                    <p className={`text-3xl font-bold text-${color}-700`}>{value}</p>
                    {subtitle && <p className="text-sm text-gray-600 mt-1">{subtitle}</p>}
                </div>
                {icon && (
                    <div className={`text-${color}-400`}>
                        {icon}
                    </div>
                )}
            </div>
        </div>
    );

    return (
        <Layout>
            <div className="space-y-8">
                <div>
                    <h1 className="text-3xl font-bold text-gray-900 mb-2">ダッシュボード</h1>
                    <p className="text-gray-600">ビジネス概要と主要指標</p>
                </div>

                {/* 予約統計 */}
                <div>
                    <h2 className="text-xl font-semibold text-gray-900 mb-4">予約統計</h2>
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div className="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                            <p className="text-sm font-medium text-gray-600 mb-1">予約総数</p>
                            <p className="text-4xl font-bold text-blue-700">{report.reservations.total_reservations}</p>
                        </div>

                        <div className="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow-md p-6 border-l-4 border-green-500">
                            <p className="text-sm font-medium text-gray-600 mb-1">完了</p>
                            <p className="text-4xl font-bold text-green-700">{report.reservations.completed}</p>
                        </div>

                        <div className="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                            <p className="text-sm font-medium text-gray-600 mb-1">確定済み</p>
                            <p className="text-4xl font-bold text-yellow-700">{report.reservations.confirmed}</p>
                        </div>

                        <div className="bg-gradient-to-br from-red-50 to-red-100 rounded-lg shadow-md p-6 border-l-4 border-red-500">
                            <p className="text-sm font-medium text-gray-600 mb-1">キャンセル率</p>
                            <p className="text-4xl font-bold text-red-700">{report.reservations.cancellation_rate}%</p>
                        </div>
                    </div>
                </div>

                {/* 売上とレビュー */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div className="lg:col-span-2 bg-white rounded-lg shadow-lg p-6">
                        <h2 className="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                            <svg className="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            売上概要
                        </h2>
                        <div className="space-y-4">
                            <div className="flex items-center justify-between py-3 border-b">
                                <span className="text-gray-600">総売上</span>
                                <span className="text-3xl font-bold text-green-600">
                                    ¥{Number(report.revenue.total_revenue).toLocaleString()}
                                </span>
                            </div>
                            <div className="flex items-center justify-between py-3 border-b">
                                <span className="text-gray-600">取引件数</span>
                                <span className="text-xl font-semibold">{report.revenue.total_transactions}件</span>
                            </div>
                            <div className="flex items-center justify-between py-3">
                                <span className="text-gray-600">平均単価</span>
                                <span className="text-xl font-semibold">
                                    ¥{Number(report.revenue.average_transaction_value).toLocaleString()}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div className="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg shadow-lg p-6 border-2 border-yellow-200">
                        <h2 className="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                            <svg className="w-6 h-6 mr-2 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            レビュー
                        </h2>
                        <div className="text-center py-4">
                            <div className="text-7xl font-bold text-yellow-600 mb-2">
                                {report.reviews.average_rating}
                            </div>
                            <p className="text-xl text-gray-700 font-medium">平均評価</p>
                            <p className="text-gray-600 mt-2">{report.reviews.total_reviews}件のレビュー</p>
                        </div>
                    </div>
                </div>

                {/* 占有率 */}
                {report.occupancy && (
                    <div className="bg-white rounded-lg shadow-lg p-6">
                        <h2 className="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                            <svg className="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            占有率
                        </h2>
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div className="text-center p-4 bg-purple-50 rounded-lg">
                                <div className="text-4xl font-bold text-purple-600">{report.occupancy.occupancy_rate}%</div>
                                <div className="text-gray-600 mt-2">占有率</div>
                            </div>
                            <div className="text-center p-4 bg-gray-50 rounded-lg">
                                <div className="text-3xl font-bold">{report.occupancy.total_rooms}</div>
                                <div className="text-gray-600 mt-2">総部屋数</div>
                            </div>
                            <div className="text-center p-4 bg-gray-50 rounded-lg">
                                <div className="text-3xl font-bold">{report.occupancy.occupied_room_nights}</div>
                                <div className="text-gray-600 mt-2">稼働室数</div>
                            </div>
                            <div className="text-center p-4 bg-gray-50 rounded-lg">
                                <div className="text-3xl font-bold">{report.occupancy.total_room_nights}</div>
                                <div className="text-gray-600 mt-2">総室数</div>
                            </div>
                        </div>
                    </div>
                )}

                {/* 顧客統計 */}
                <div className="bg-white rounded-lg shadow-lg p-6">
                    <h2 className="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                        <svg className="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        顧客統計
                    </h2>
                    <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div className="space-y-2">
                            <div className="text-sm text-gray-600">総顧客数</div>
                            <div className="text-2xl font-bold">{report.customers.total_customers}人</div>
                        </div>
                        <div className="space-y-2">
                            <div className="text-sm text-gray-600">リピーター数</div>
                            <div className="text-2xl font-bold">{report.customers.repeat_customers}人</div>
                        </div>
                        <div className="space-y-2">
                            <div className="text-sm text-gray-600">リピーター率</div>
                            <div className="text-2xl font-bold text-blue-600">{report.customers.repeat_rate}%</div>
                        </div>
                        <div className="space-y-2">
                            <div className="text-sm text-gray-600">平均宿泊日数</div>
                            <div className="text-2xl font-bold">{report.customers.average_stay_duration}泊</div>
                        </div>
                        <div className="space-y-2">
                            <div className="text-sm text-gray-600">平均ゲスト数</div>
                            <div className="text-2xl font-bold">{report.customers.average_guest_count}人</div>
                        </div>
                    </div>
                </div>

                {/* クイックリンク */}
                <div className="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg shadow p-6">
                    <h2 className="text-lg font-semibold text-gray-900 mb-4">詳細レポート</h2>
                    <div className="grid grid-cols-2 md:grid-cols-5 gap-3">
                        <Link
                            href="/reports/reservations"
                            className="bg-white hover:bg-blue-50 text-center py-3 px-4 rounded-lg shadow-sm hover:shadow-md transition border border-gray-200"
                        >
                            <div className="font-medium text-gray-900">予約レポート</div>
                        </Link>
                        <Link
                            href="/reports/revenue"
                            className="bg-white hover:bg-blue-50 text-center py-3 px-4 rounded-lg shadow-sm hover:shadow-md transition border border-gray-200"
                        >
                            <div className="font-medium text-gray-900">売上レポート</div>
                        </Link>
                        {accommodationId && (
                            <Link
                                href={`/reports/occupancy?accommodation_id=${accommodationId}`}
                                className="bg-white hover:bg-blue-50 text-center py-3 px-4 rounded-lg shadow-sm hover:shadow-md transition border border-gray-200"
                            >
                                <div className="font-medium text-gray-900">占有率レポート</div>
                            </Link>
                        )}
                        <Link
                            href="/reports/reviews"
                            className="bg-white hover:bg-blue-50 text-center py-3 px-4 rounded-lg shadow-sm hover:shadow-md transition border border-gray-200"
                        >
                            <div className="font-medium text-gray-900">レビューレポート</div>
                        </Link>
                        <Link
                            href="/reports/customers"
                            className="bg-white hover:bg-blue-50 text-center py-3 px-4 rounded-lg shadow-sm hover:shadow-md transition border border-gray-200"
                        >
                            <div className="font-medium text-gray-900">顧客レポート</div>
                        </Link>
                    </div>
                </div>
            </div>
        </Layout>
    );
}
